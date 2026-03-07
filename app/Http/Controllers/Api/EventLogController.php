<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventLogResource;
use App\Models\EventLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EventLogController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', EventLog::class);

        $query = EventLog::query()->where('user_id', $request->user()->id);

        if ($request->filled('event_name')) {
            $query->where('event_name', $request->string('event_name')->toString());
        }

        if ($request->filled('aggregate_type')) {
            $query->where('aggregate_type', $request->string('aggregate_type')->toString());
        }

        if ($request->filled('from')) {
            $query->whereDate('happened_at', '>=', $request->string('from')->toString());
        }

        if ($request->filled('to')) {
            $query->whereDate('happened_at', '<=', $request->string('to')->toString());
        }

        return EventLogResource::collection($query->latest('happened_at')->paginate(15));
    }

    public function show(EventLog $eventLog): EventLogResource
    {
        $this->authorize('view', $eventLog);

        return new EventLogResource($eventLog);
    }
}
