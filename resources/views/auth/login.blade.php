<x-guest-layout>
<main class="signin-page signin-page--portal">
    <div class="signin-shell">
        <section class="signin-showcase" aria-label="TrackingAid disaster logistics">
            <a class="brand-logo-frame signin-showcase-logo" href="{{ route('welcome') }}" aria-label="TrackingAid home">
                <img class="trackingaid-logo" src="{{ asset('images/trackingaid-logo.jpg') }}" alt="TrackingAid by ResQOperation">
            </a>
            <div class="signin-showcase-copy">
                <p class="signin-showcase-kicker"><span></span> Disaster logistics management</p>
                <h1>Welcome back.</h1>
                <p>Sign in to coordinate supplies, review requests, and follow relief stock activity.</p>
            </div>
            <img class="signin-showcase-image" src="{{ asset('images/relief-warehouse.svg') }}" alt="Organized relief supplies inside a distribution warehouse">
            <div class="signin-showcase-foot"><i class="fa-solid fa-shield-heart" aria-hidden="true"></i><span>Secure access for authorized response teams</span></div>
            <span class="signin-showcase-orb signin-showcase-orb--one" aria-hidden="true"></span>
            <span class="signin-showcase-orb signin-showcase-orb--two" aria-hidden="true"></span>
        </section>

        <section class="signin-panel signin-panel--portal" aria-labelledby="signin-title">
            <div class="signin-heading">
                <p class="public-eyebrow">TrackingAid information system</p>
                <h2 id="signin-title">Sign in to your account</h2>
                <p>Use your assigned account to continue.</p>
            </div>

            @if (session('status'))
                <div class="alert-box alert-success" role="status">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert-box alert-error" role="alert">
                    <div><strong>Sign-in failed.</strong><ul class="signin-errors">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul></div>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="signin-field">
                    <label class="form-label" for="email">Email address</label>
                    <div class="signin-input-wrap"><i class="fa-regular fa-envelope" aria-hidden="true"></i><input class="form-input" id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus></div>
                </div>
                <div class="signin-field">
                    <label class="form-label" for="password">Password</label>
                    <div class="signin-input-wrap signin-input-wrap--password">
                        <i class="fa-solid fa-lock" aria-hidden="true"></i>
                        <input class="form-input" id="password" type="password" name="password" autocomplete="current-password" required>
                        <button class="password-toggle" type="button" data-toggle-password="password" aria-label="Show password">Show</button>
                    </div>
                </div>
                <div class="signin-options">
                    <label class="signin-remember"><input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> <span>Remember me</span></label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}">Forgot password?</a>
                    @endif
                </div>
                <button class="btn-main signin-submit" type="submit">Sign in <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
            </form>
            <a class="signin-back" href="{{ route('welcome') }}"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to overview</a>
        </section>
    </div>
</main>
<script>
document.querySelectorAll('[data-toggle-password]').forEach(button => button.addEventListener('click', () => {
    const input = document.getElementById(button.dataset.togglePassword);
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    button.textContent = show ? 'Hide' : 'Show';
    button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
}));
</script>
</x-guest-layout>
