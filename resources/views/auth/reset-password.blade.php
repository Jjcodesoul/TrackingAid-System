<x-guest-layout>
    <main class="signin-page">
        <section class="signin-panel auth-public-panel" aria-labelledby="reset-password-title">
            <a class="signin-brand" href="{{ route('welcome') }}">
                <img class="trackingaid-logo" src="{{ asset('images/trackingaid-logo.jpg') }}" alt="TrackingAid by ResQOperation">
            </a>
            <div class="signin-heading">
                <p class="public-eyebrow">Account access</p>
                <h1 id="reset-password-title">Choose a new password</h1>
                <p>Set a new password for your TrackingAid account.</p>
            </div>
            <form method="POST" action="{{ route('password.store') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">
                <div class="signin-field">
                    <label class="form-label" for="email">Email address</label>
                    <input class="form-input" id="email" type="email" name="email" value="{{ old('email', $request->email) }}" autocomplete="username" required autofocus>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>
                <div class="signin-field">
                    <label class="form-label" for="password">New password</label>
                    <div class="password-control">
                        <input class="form-input" id="password" type="password" name="password" autocomplete="new-password" required>
                        <button class="password-toggle" type="button" data-toggle-password="password" aria-label="Show new password">Show</button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>
                <div class="signin-field">
                    <label class="form-label" for="password_confirmation">Confirm new password</label>
                    <div class="password-control">
                        <input class="form-input" id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
                        <button class="password-toggle" type="button" data-toggle-password="password_confirmation" aria-label="Show confirmed password">Show</button>
                    </div>
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>
                <button class="btn-main signin-submit" type="submit">Reset password</button>
            </form>
            <a class="auth-back-link" href="{{ route('login') }}">Back to sign in</a>
        </section>
    </main>
    <script>
    document.querySelectorAll('[data-toggle-password]').forEach(button => button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.togglePassword);
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        button.textContent = show ? 'Hide' : 'Show';
        button.setAttribute('aria-label', `${show ? 'Hide' : 'Show'} ${input.id === 'password' ? 'new password' : 'confirmed password'}`);
    }));
    </script>
</x-guest-layout>
