@extends('storefront.layout')

@section('content')
    <section class="mx-auto max-w-md">
        <div class="glass-card p-8">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Auth</p>
            <h1 class="mt-2 text-2xl font-extrabold tracking-tight">Sign in</h1>
            <p class="mt-2 text-sm text-slate-600">Access dashboard and API token tools.</p>

            <form action="{{ route('auth.login') }}" method="POST" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label for="email" class="text-xs font-semibold uppercase tracking-wider text-slate-500">Email</label>
                    <input id="email" name="email" value="{{ old('email') }}" class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-2 text-sm focus:border-slate-600 focus:outline-none" required>
                </div>
                <div>
                    <label for="password" class="text-xs font-semibold uppercase tracking-wider text-slate-500">Password</label>
                    <input id="password" name="password" type="password" class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-2 text-sm focus:border-slate-600 focus:outline-none" required>
                </div>
                @if($errors->any())
                    <p class="text-sm font-semibold text-red-600">{{ $errors->first() }}</p>
                @endif
                <button class="action-btn w-full">Sign in</button>
            </form>

            <p class="mt-4 text-xs text-slate-600">No account? <a href="{{ route('auth.register.form') }}" class="font-semibold underline">Register</a></p>
        </div>
    </section>
@endsection
