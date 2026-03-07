<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LedgerEntryResource;
use App\Models\LedgerEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LedgerEntryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', LedgerEntry::class);

        $query = LedgerEntry::query()
            ->whereHas('customer', fn ($customerQuery) => $customerQuery->where('user_id', $request->user()->id));

        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }

        if ($request->filled('direction')) {
            $query->where('direction', $request->string('direction')->toString());
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->string('from')->toString());
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->string('to')->toString());
        }

        return LedgerEntryResource::collection($query->latest()->paginate(15));
    }

    public function show(LedgerEntry $ledgerEntry): LedgerEntryResource
    {
        $this->authorize('view', $ledgerEntry);

        return new LedgerEntryResource($ledgerEntry);
    }
}
