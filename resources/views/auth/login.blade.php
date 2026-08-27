<x-guest-layout>
<style>
.login-wrapper {
    min-height: 100vh;
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
    background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
    color: white;
    padding: 60px 40px;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.login-hero::before {
    content: '';
    position: absolute;
    top: -80px;
    right: -80px;
    width: 260px;
    height: 260px;
    background: rgba(255,255,255,0.06);
    border-radius: 50%;
}

.login-hero::after {
    content: '';
    position: absolute;
    bottom: -100px;
    left: -60px;
    width: 220px;
    height: 220px;
    background: rgba(255,255,255,0.05);
    border-radius: 50%;
}

@media (min-width: 768px) {
    .login-hero { display: flex; }
}

.hero-content {
    z-index: 10;
    max-width: 400px;
    text-align: center;
}

.hero-logo {
    width: 64px;
    height: 64px;
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.3);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    margin: 0 auto 20px;
    backdrop-filter: blur(10px);
}

.hero-content h1 {
    font-size: 42px;
    font-weight: 700;
    margin-bottom: 12px;
    letter-spacing: -1px;
}

.hero-content p {
    font-size: 16px;
    opacity: 0.95;
    line-height: 1.6;
    margin-bottom: 8px;
}

.hero-tagline {
    font-size: 13px;
    opacity: 0.85;
    margin-bottom: 32px !important;
}

.hero-features {
    display: grid;
    gap: 12px;
}

.hero-feature {
    background: rgba(255,255,255,0.12);
    padding: 14px 16px;
    border-radius: 10px;
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    gap: 14px;
    border: 1px solid rgba(255,255,255,0.25);
    transition: all 0.2s ease;
}

.hero-feature:hover {
    background: rgba(255,255,255,0.18);
    transform: translateX(4px);
}

.hero-feature-icon {
    width: 36px;
    height: 36px;
    background: rgba(255,255,255,0.2);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 17px;
    flex-shrink: 0;
}

.hero-feature-text {
    font-weight: 600;
    font-size: 13.5px;
    text-align: left;
}

.login-form-section {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 24px;
    background: #f7fafc;
}

@media (min-width: 768px) {
    .login-form-section { padding: 60px 40px; }
}

.login-form-wrapper {
    width: 100%;
    max-width: 400px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 36px 32px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.05);
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
    background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
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
    color: #718096;
    font-size: 13px;
    margin: 0;
}

.form-group {
    margin-bottom: 18px;
}

.form-label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #4a5568;
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
    padding: 12px 16px 12px 42px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    font-size: 14px;
    font-family: inherit;
    transition: all 0.2s ease;
    box-sizing: border-box;
}

.form-input:focus {
    outline: none;
    border-color: #2ecc71;
    box-shadow: 0 0 0 3px rgba(46,204,113,0.1);
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

.form-checkbox input { width: 17px; height: 17px; cursor: pointer; accent-color: #2ecc71; }
.form-checkbox label { cursor: pointer; font-size: 13px; color: #4a5568; font-weight: 500; }

.forgot-link { font-size: 12px; color: #2ecc71; font-weight: 600; text-decoration: none; }
.forgot-link:hover { color: #27ae60; text-decoration: underline; }

.submit-btn {
    width: 100%;
    padding: 14px 20px;
    background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(46,204,113,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.submit-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(46,204,113,0.4); }
.submit-btn:active { transform: translateY(0); }
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
    background: #fed7d7;
    color: #c53030;
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid #fc8181;
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
            <div class="hero-logo">📦</div>
            <h1>TrackingAid</h1>
            <p>Disaster Logistics System</p>
            <p class="hero-tagline">Post-disaster response and inventory management</p>

            <div class="hero-features">
                <div class="hero-feature">
                    <span class="hero-feature-icon">🔒</span>
                    <span class="hero-feature-text">Secure Access</span>
                </div>
                <div class="hero-feature">
                    <span class="hero-feature-icon">📊</span>
                    <span class="hero-feature-text">Real-time Tracking</span>
                </div>
                <div class="hero-feature">
                    <span class="hero-feature-icon">⚡</span>
                    <span class="hero-feature-text">Fast &amp; Reliable</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Section -->
    <div class="login-form-section">
        <div class="login-form-wrapper">

            <div class="form-mobile-logo">
                <div class="form-mobile-logo-badge">📦</div>
                <span class="form-mobile-logo-text">TrackingAid</span>
            </div>

            <div class="form-header">
                <h2>Welcome back</h2>
                <p>Sign in to continue to your dashboard</p>
            </div>

            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    <div class="error-alert">
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
                        <button type="button" class="password-toggle" onclick="togglePassword()">
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
                    <span id="btnText">Login</span>
                </button>
            </form>

            <div class="form-footer">
                <p>&copy; 2026 TrackingAid. All Rights Reserved.</p>
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
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
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