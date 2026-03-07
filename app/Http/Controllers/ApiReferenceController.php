<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateApiTokenRequest;
use App\Http\Requests\RevokeApiTokenRequest;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Laravel\Sanctum\PersonalAccessToken;

class ApiReferenceController extends Controller
{
    public function show(): View
    {
        $currentUser = $this->currentUser();

        $tokens = $currentUser->tokens()
            ->latest('id')
            ->get(['id', 'name', 'last_used_at', 'created_at']);

        return view('storefront.api', [
            'tokens' => $tokens,
            'currentUser' => $currentUser,
            'plainTextToken' => session('plain_text_token'),
        ]);
    }

    public function createToken(CreateApiTokenRequest $request): RedirectResponse
    {
        $currentUser = $this->currentUser();

        $plainTextToken = $currentUser
            ->createToken($request->string('token_name')->toString())
            ->plainTextToken;

        return redirect()
            ->route('api.reference')
            ->with('plain_text_token', $plainTextToken);
    }

    public function revokeToken(RevokeApiTokenRequest $request): RedirectResponse
    {
        $currentUser = $this->currentUser();
        $tokenId = $request->integer('token_id');

        $token = PersonalAccessToken::query()
            ->where('tokenable_type', User::class)
            ->where('tokenable_id', $currentUser->id)
            ->where('id', $tokenId)
            ->first();

        if ($token) {
            $token->delete();
        }

        return redirect()->route('api.reference');
    }

    private function currentUser(): User
    {
        $currentUser = auth()->user();
        abort_unless($currentUser instanceof User, 403);

        Customer::query()->updateOrCreate(
            ['email' => $currentUser->email],
            [
                'user_id' => $currentUser->id,
                'name' => $currentUser->name,
            ]
        );

        return $currentUser;
    }
}
