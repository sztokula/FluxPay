<?php

namespace App\Http\Controllers\Api;

use App\Actions\CreateCustomerAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Models\EventLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerController extends Controller
{
    public function __construct(private CreateCustomerAction $createCustomerAction)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Customer::class);

        $query = Customer::query()->where('user_id', $request->user()->id);

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->string('from')->toString());
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->string('to')->toString());
        }

        return CustomerResource::collection($query->latest()->paginate(15));
    }

    public function store(StoreCustomerRequest $request): CustomerResource
    {
        $this->authorize('create', Customer::class);

        $payload = $request->validated();
        $payload['user_id'] = $request->user()->id;

        $customer = $this->createCustomerAction->execute($payload);

        EventLog::query()->create([
            'user_id' => $request->user()->id,
            'event_name' => 'customer.created',
            'aggregate_type' => 'customer',
            'aggregate_id' => $customer->id,
            'payload' => $customer->toArray(),
            'happened_at' => now(),
        ]);

        return new CustomerResource($customer);
    }

    public function show(Customer $customer): CustomerResource
    {
        $this->authorize('view', $customer);

        return new CustomerResource($customer);
    }

    public function update(StoreCustomerRequest $request, Customer $customer): CustomerResource
    {
        $this->authorize('update', $customer);

        $customer->update($request->validated());

        EventLog::query()->create([
            'user_id' => $request->user()->id,
            'event_name' => 'customer.updated',
            'aggregate_type' => 'customer',
            'aggregate_id' => $customer->id,
            'payload' => $customer->toArray(),
            'happened_at' => now(),
        ]);

        return new CustomerResource($customer->fresh());
    }

    public function destroy(Customer $customer): \Illuminate\Http\Response
    {
        $this->authorize('delete', $customer);

        $snapshot = $customer->toArray();

        $customer->delete();

        EventLog::query()->create([
            'user_id' => request()->user()?->id,
            'event_name' => 'customer.deleted',
            'aggregate_type' => 'customer',
            'aggregate_id' => $customer->id,
            'payload' => $snapshot,
            'happened_at' => now(),
        ]);

        return response()->noContent();
    }
}
