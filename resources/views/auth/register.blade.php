@extends('storefront.layout')

@section('content')
    <section class="mx-auto max-w-md">
        <div class="glass-card p-8">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Auth</p>
            <h1 class="mt-2 text-2xl font-extrabold tracking-tight">Create account</h1>
            <p class="mt-2 text-sm text-slate-600">Create a user profile for authenticated API access.</p>

            <form action="{{ route('auth.register') }}" method="POST" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label for="name" class="text-xs font-semibold uppercase tracking-wider text-slate-500">Name</label>
                    <input id="name" name="name" value="{{ old('name') }}" class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-2 text-sm focus:border-slate-600 focus:outline-none" required>
                </div>
                <div>
                    <label for="email" class="text-xs font-semibold uppercase tracking-wider text-slate-500">Email</label>
                    <input id="email" name="email" value="{{ old('email') }}" class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-2 text-sm focus:border-slate-600 focus:outline-none" required>
                </div>
                <div>
                    <label for="password" class="text-xs font-semibold uppercase tracking-wider text-slate-500">Password</label>
                    <input id="password" name="password" type="password" class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-2 text-sm focus:border-slate-600 focus:outline-none" required>
                </div>
                <div>
                    <label for="password_confirmation" class="text-xs font-semibold uppercase tracking-wider text-slate-500">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-2 text-sm focus:border-slate-600 focus:outline-none" required>
                </div>
                @if($errors->any())
                    <p class="text-sm font-semibold text-red-600">{{ $errors->first() }}</p>
                @endif
                <button class="action-btn w-full">Create account</button>
            </form>

            <p class="mt-4 text-xs text-slate-600">Already have account? <a href="{{ route('auth.login.form') }}" class="font-semibold underline">Sign in</a></p>
        </div>
    </section>
@endsection
