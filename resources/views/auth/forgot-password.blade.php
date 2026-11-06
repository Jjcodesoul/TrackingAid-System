<x-guest-layout>
    <main class="signin-page">
        <section class="signin-panel auth-public-panel" aria-labelledby="reset-request-title">
            <a class="signin-brand" href="{{ route('welcome') }}">
                <img class="trackingaid-logo" src="{{ asset('images/trackingaid-logo.jpg') }}" alt="TrackingAid by ResQOperation">
            </a>
            <div class="signin-heading">
                <p class="public-eyebrow">Account access</p>
                <h1 id="reset-request-title">Reset your password</h1>
                <p>Enter your account email and we’ll send a secure reset link.</p>
            </div>
            <x-auth-session-status class="mb-4" :status="session('status')" />
            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="signin-field">
                    <label class="form-label" for="email">Email address</label>
                    <input class="form-input" id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>
                <button class="btn-main signin-submit" type="submit">Send reset link</button>
            </form>
            <a class="auth-back-link" href="{{ route('login') }}">Back to sign in</a>
        </section>
    </main>
</x-guest-layout>
