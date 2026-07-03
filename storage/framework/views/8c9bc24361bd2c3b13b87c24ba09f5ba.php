<?php if (isset($component)) { $__componentOriginal69dc84650370d1d4dc1b42d016d7226b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b = $attributes; } ?>
<?php $component = App\View\Components\GuestLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('guest-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\GuestLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<style>
.login-wrapper {
    min-height: 100vh;
    display: grid;
    grid-template-columns: 1fr;
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

@media (min-width: 768px) {
    .login-hero { display: flex; }
}

.hero-content {
    z-index: 10;
    max-width: 400px;
    text-align: center;
}

.hero-content h1 {
    font-size: 48px;
    font-weight: 700;
    margin-bottom: 16px;
    letter-spacing: -1px;
}

.hero-content p {
    font-size: 16px;
    opacity: 0.95;
    line-height: 1.6;
    margin-bottom: 40px;
}

.hero-features {
    display: grid;
    gap: 16px;
}

.hero-feature {
    background: rgba(255,255,255,0.1);
    padding: 14px;
    border-radius: 10px;
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    gap: 12px;
    border: 1px solid rgba(255,255,255,0.2);
}

.hero-feature-icon { font-size: 24px; }
.hero-feature-text { font-weight: 600; font-size: 13px; }

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
}

.form-header {
    margin-bottom: 32px;
}

.form-header h2 {
    font-size: 28px;
    font-weight: 700;
    color: #1b1b18;
    margin: 0 0 8px;
}

.form-header p {
    color: #718096;
    font-size: 14px;
    margin: 0;
}

.form-group {
    margin-bottom: 20px;
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

.form-input {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    font-family: inherit;
    transition: all 0.2s ease;
}

.form-input:focus {
    outline: none;
    border-color: #2ecc71;
    box-shadow: 0 0 0 3px rgba(46,204,113,0.1);
}

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

.form-checkbox input { width: 18px; height: 18px; cursor: pointer; accent-color: #2ecc71; }
.form-checkbox label { cursor: pointer; font-size: 14px; color: #4a5568; font-weight: 500; }

.forgot-link { font-size: 12px; color: #2ecc71; font-weight: 600; }
.forgot-link:hover { color: #27ae60; }

.submit-btn {
    width: 100%;
    padding: 14px 20px;
    background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(46,204,113,0.3);
}

.submit-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(46,204,113,0.4); }
.submit-btn:active { transform: translateY(0); }

.error-alert {
    background: #fed7d7;
    color: #c53030;
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid #fc8181;
    font-size: 13px;
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
            <h1>TrackingAid</h1>
            <p>Disaster Logistics System</p>
            <p style="font-size: 13px; opacity: 0.85; margin-bottom: 32px;">Post-disaster response and inventory management</p>
            
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
                    <span class="hero-feature-text">Fast & Reliable</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Section -->
    <div class="login-form-section">
        <div class="login-form-wrapper">
            <div class="form-header">
            </div>

            <?php if($errors->any()): ?>
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="error-alert"><?php echo e($error); ?></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('login')); ?>">
                <?php echo csrf_field(); ?>

                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input 
                        id="email" 
                        class="form-input" 
                        type="email" 
                        name="email" 
                        value="<?php echo e(old('email')); ?>" 
                        required 
                        autofocus 
                        placeholder="admin@trackingaid.org"
                    />
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input 
                        id="password" 
                        class="form-input" 
                        type="password" 
                        name="password" 
                        required 
                        placeholder="••••••••"
                    />
                </div>

                <div class="form-row">
                    <div class="form-checkbox">
                        <input id="remember_me" type="checkbox" name="remember" />
                        <label for="remember_me">Remember me</label>
                    </div>
                    
                    <?php if(Route::has('password.request')): ?>
                        <a href="<?php echo e(route('password.request')); ?>" class="forgot-link">Forgot Password?</a>
                    <?php endif; ?>
                </div>

                <button type="submit" class="submit-btn">Login</button>
            </form>

            <div class="form-footer">
                <p>&copy; 2026 TrackingAid. All Rights Reserved.</p>
            </div>
        </div>
    </div>
</div>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $attributes = $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $component = $__componentOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?><?php /**PATH C:\Capstone\bag o\TrackingAid-System\resources\views/auth/login.blade.php ENDPATH**/ ?>