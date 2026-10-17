<x-guest-layout>
<style>
.login-wrapper {
    width: 100%;
    min-width: 0;
    min-height: 100vh;
    overflow-x: clip;
    display: grid;
    grid-template-columns: 1fr;
    animation: fadeIn 0.4s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@media (min-width: 768px) {
    .login-wrapper { grid-template-columns: 1fr 1fr; }
}

.login-hero {
    display: none;
    background: #18332e;
    color: white;
    padding: 60px 40px;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.login-hero::before {
    display: none;
}

.login-hero::after {
    display: none;
}

.login-form-section { min-width: 0; }

@media (min-width: 768px) {
    .login-hero { display: flex; }
}

.hero-content {
    z-index: 10;
    max-width: 400px;
    text-align: center;
}

.hero-logo {
    width: 56px;
    height: 56px;
    background: var(--brand);
    border: 0;
    border-radius: var(--radius-surface);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    margin: 0 auto 20px;
}

.hero-content h1 {
    font-size: 34px;
    font-weight: 700;
    margin-bottom: 12px;
    letter-spacing: 0;
}

.hero-content p {
    font-size: 16px;
    font-weight: 500;
    line-height: 1.6;
    margin-bottom: 8px;
}

}

.login-form-section {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 24px;
    background: var(--canvas);
}

@media (min-width: 768px) {
    .login-form-section { padding: 60px 40px; }
}

.login-form-wrapper {
    width: 100%;
    max-width: 400px;
    background: #ffffff;
    border: 1px solid var(--line);
    border-radius: var(--radius-surface);
    padding: 36px 32px;
    box-shadow: var(--shadow-surface);
}

.form-mobile-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 24px;
}

@media (min-width: 768px) {
    .form-mobile-logo { display: none; }
}

.form-mobile-logo-badge {
    width: 36px;
    height: 36px;
    background: var(--brand);
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 16px;
    flex-shrink: 0;
}

.form-mobile-logo-text {
    font-weight: 700;
    font-size: 16px;
    color: #1a202c;
}

.form-header {
    margin-bottom: 28px;
}

.form-header h2 {
    font-size: 24px;
    font-weight: 700;
    color: #1a202c;
    margin: 0 0 6px;
}

.form-header p {
    color: var(--muted);
    font-size: 14px;
    margin: 0;
}

.form-group {
    margin-bottom: 18px;
}

.form-label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #344541;
    margin-bottom: 8px;
}

.input-wrapper {
    position: relative;
}

.input-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 14px;
    pointer-events: none;
}

.form-input {
    width: 100%;
    min-height: 44px;
    padding: 12px 16px 12px 42px;
    border: 1px solid #bdcbc5;
    border-radius: var(--radius-control);
    font-size: 14px;
    font-family: inherit;
    transition: all 0.2s ease;
    box-sizing: border-box;
}

.form-input:focus {
    outline: none;
    border-color: var(--brand);
    box-shadow: 0 0 0 3px rgb(23 107 87 / 14%);
}

.password-toggle {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    font-size: 14px;
    padding: 4px;
    display: flex;
    align-items: center;
}

.password-toggle:hover { color: #4a5568; }

.form-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.form-checkbox {
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-checkbox input { width: 17px; height: 17px; cursor: pointer; accent-color: var(--brand); }
.form-checkbox label { cursor: pointer; font-size: 13px; color: #4a5568; font-weight: 500; }

.forgot-link { font-size: 13px; color: var(--brand); font-weight: 600; text-decoration: none; }
.forgot-link:hover { color: var(--brand-hover); text-decoration: underline; }

.submit-btn {
    width: 100%;
    padding: 14px 20px;
    background: var(--brand);
    color: white;
    border: none;
    border-radius: var(--radius-control);
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.15s ease;
    box-shadow: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.submit-btn:hover { background: var(--brand-hover); }
.submit-btn:active { background: var(--brand-hover); }
.submit-btn:disabled { opacity: 0.75; cursor: not-allowed; transform: none; }

.spinner {
    width: 15px;
    height: 15px;
    border: 2px solid rgba(255,255,255,0.4);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
    display: none;
}

@keyframes spin { to { transform: rotate(360deg); } }

.error-alert {
    background: var(--danger-soft);
    color: #912018;
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid #e6b8b4;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-footer {
    text-align: center;
    margin-top: 24px;
    font-size: 12px;
    color: #a0aec0;
}
</style>

<div class="login-wrapper">
    <!-- Hero Section -->
    <div class="login-hero">
        <div class="hero-content">
            <x-brand-mark class="hero-logo" />
            <h1>TrackingAid</h1>
            <p>Disaster Logistics System</p>
        </div>
    </div>

    <!-- Form Section -->
    <div class="login-form-section">
        <div class="login-form-wrapper">

            <div class="form-mobile-logo">
                <x-brand-mark class="form-mobile-logo-badge" />
                <span class="form-mobile-logo-text">TrackingAid</span>
            </div>

            <div class="form-header">
                <h2>Sign in</h2>
                <p>Use your assigned TrackingAid account.</p>
            </div>

            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    <div class="error-alert" role="alert">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span>{{ $error }}</span>
                    </div>
                @endforeach
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf

                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-wrapper">
                        <i class="fa-regular fa-envelope input-icon"></i>
                        <input
                            id="email"
                            class="form-input"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            placeholder="admin@trackingaid.org"
                        />
                    </div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock input-icon"></i>
                        <input
                            id="password"
                            class="form-input"
                            type="password"
                            name="password"
                            required
                            placeholder="••••••••"
                            style="padding-right:42px;"
                        />
                        <button type="button" class="password-toggle" onclick="togglePassword()" aria-label="Show password" title="Show password">
                            <i class="fa-regular fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-checkbox">
                        <input id="remember_me" type="checkbox" name="remember" />
                        <label for="remember_me">Remember me</label>
                    </div>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-link">Forgot Password?</a>
                    @endif
                </div>

                <button type="submit" class="submit-btn" id="submitBtn">
                    <span class="spinner" id="btnSpinner"></span>
                    <span id="btnText">Sign in</span>
                </button>
            </form>

            <div class="form-footer">
                <p>&copy; 2026 TrackingAid</p>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('password');
    const icon = document.getElementById('toggleIcon');
    if (input.type === 'password') {
        input.type = 'text';
        document.querySelector('.password-toggle').setAttribute('aria-label', 'Hide password');
        document.querySelector('.password-toggle').title = 'Hide password';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        document.querySelector('.password-toggle').setAttribute('aria-label', 'Show password');
        document.querySelector('.password-toggle').title = 'Show password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

document.getElementById('loginForm').addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    const spinner = document.getElementById('btnSpinner');
    const text = document.getElementById('btnText');

    btn.disabled = true;
    spinner.style.display = 'inline-block';
    text.innerText = 'Signing in...';
});
</script>

</x-guest-layout>