@php
    $normalized = strtolower((string) ($value ?? 'unknown'));

    $successValues = ['succeeded', 'paid', 'active', 'allow', 'yes', 'fulfilled', 'credit', 'charge'];
    $warningValues = ['processing', 'requires_action', 'trialing', 'pending', 'review', 'open', 'past_due', 'adjustment'];
    $dangerValues = ['failed', 'canceled', 'unpaid', 'void', 'uncollectible', 'block', 'no', 'fraud_blocked', 'debit', 'refund', 'fee', 'payout'];

    if (in_array($normalized, $successValues, true)) {
        $statusClasses = 'border-emerald-200 bg-emerald-50 text-emerald-700';
        $dotClasses = 'bg-emerald-500';
    } elseif (in_array($normalized, $warningValues, true)) {
        $statusClasses = 'border-amber-200 bg-amber-50 text-amber-700';
        $dotClasses = 'bg-amber-500';
    } elseif (in_array($normalized, $dangerValues, true)) {
        $statusClasses = 'border-red-200 bg-red-50 text-red-700';
        $dotClasses = 'bg-red-500';
    } else {
        $statusClasses = 'border-slate-200 bg-slate-50 text-slate-700';
        $dotClasses = 'bg-slate-500';
    }
@endphp

<span class="status-pill {{ $statusClasses }}">
    <span class="status-dot {{ $dotClasses }}"></span>
    {{ str_replace('_', ' ', $normalized) }}
</span>
