<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>TrackingAid - Login</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600&display=swap" rel="stylesheet" />
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { width: 100%; height: 100%; }
        body { font-family: 'Instrument Sans', system-ui, sans-serif; background: #f7fafc; color: #1b1b18; }
    </style>
</head>
<body>
<?php echo e($slot); ?>

</body>
</html>
<?php /**PATH C:\Capstone\bag o\TrackingAid-System\resources\views/layouts/guest.blade.php ENDPATH**/ ?>