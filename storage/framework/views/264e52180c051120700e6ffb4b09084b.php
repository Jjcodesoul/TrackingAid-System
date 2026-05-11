<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TrackingAid</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>

        body{
            margin:0;
            font-family:Inter,sans-serif;
            background:#F8FAFC;
        }

        /* SIDEBAR */
        .sidebar{
            width:250px;
            height:100vh;
            background:#0F172A;
            position:fixed;
            padding:20px;
            color:white;
        }

        .sidebar h2{
            color:#10B981;
            margin-bottom:30px;
            font-size:24px;
            font-weight:700;
        }

        .sidebar a{
            display:flex;
            align-items:center;
            gap:10px;
            color:#CBD5E1;
            text-decoration:none;
            padding:12px 15px;
            margin-bottom:10px;
            border-radius:12px;
            transition:0.2s;
        }

        .sidebar a:hover{
            background:#1E293B;
            color:white;
        }

        .sidebar .active{
            background:#10B981;
            color:white;
        }

        /* MAIN */
        .main{
            margin-left:270px;
            padding:25px;
        }

        /* CARD */
        .card-ui{
            background:white;
            border-radius:16px;
            padding:20px;
            box-shadow:0 2px 10px rgba(0,0,0,0.05);
            border:none;
        }

        /* GRID */
        .grid{
            display:grid;
            grid-template-columns:repeat(3,1fr);
            gap:15px;
        }

        /* BUTTON */
        .btn-main{
            background:#10B981;
            color:white;
            border:none;
            padding:10px 18px;
            border-radius:10px;
        }

        .btn-main:hover{
            background:#059669;
        }

        .btn-danger-custom{
            background:#E11D48;
            color:white;
            border:none;
            padding:7px 12px;
            border-radius:8px;
        }

        /* INPUT */
        input,select{
            width:100%;
            padding:11px;
            border:1px solid #E2E8F0;
            border-radius:10px;
            margin-bottom:15px;
        }

        /* TABLE */
        table{
            width:100%;
            border-collapse:collapse;
        }

        table th{
            background:#0F172A;
            color:white;
            padding:14px;
        }

        table td{
            padding:14px;
            border-bottom:1px solid #E2E8F0;
        }

        /* BADGES */
        .badge-success{
            background:#DCFCE7;
            color:#166534;
            padding:6px 10px;
            border-radius:20px;
        }

        .badge-warning{
            background:#FEF3C7;
            color:#92400E;
            padding:6px 10px;
            border-radius:20px;
        }

        .badge-danger{
            background:#FEE2E2;
            color:#991B1B;
            padding:6px 10px;
            border-radius:20px;
        }

        /* MODAL */
        .modal-content{
            border-radius:18px;
            border:none;
        }

    </style>
</head>
<body>


<div class="sidebar">

    <h2>TrackingAid</h2>

    <a href="/inventory" class="active">
        <i class="fa fa-box"></i>
        Inventory
    </a>

    <a href="/stock-in">
        <i class="fa fa-warehouse"></i>
        Stock In
    </a>

    <a href="#">
        <i class="fa fa-inbox"></i>
        Requests
    </a>

    <a href="#">
        <i class="fa fa-truck"></i>
        Borrow / Release
    </a>

    <a href="#">
        <i class="fa fa-chart-line"></i>
        Reports
    </a>

</div>


<div class="main">

    <?php if(session('success')): ?>
        <div class="alert alert-success">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php echo $__env->yieldContent('content'); ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>

function generateSKU() {

    let category =
        document.getElementById('category')?.value || '';

    let name =
        document.getElementById('name')?.value || '';

    let unit =
        document.getElementById('unit_type')?.value || '';

    let size =
        document.getElementById('size_weight')?.value || '';

    let target =
        document.getElementById('target_beneficiary')?.value || '';

    let variant =
        document.getElementById('variant')?.value || '';

    // FORMAT
    name = name.replace(/\s+/g, '-').toUpperCase();

    size = size.replace(/\s+/g, '').toUpperCase();

    variant = variant.replace(/\s+/g, '-').toUpperCase();

    let sku =
        `${category}-${name}-${unit}-${size}-${target}-${variant}`;

    sku = sku.replace(/--+/g, '-');

    // PREVIEW
    document.getElementById('skuPreview').innerText = sku;

    // HIDDEN INPUT
    document.getElementById('sku').value = sku;
}

// AUTO UPDATE
document.addEventListener('input', generateSKU);
document.addEventListener('change', generateSKU);

</script>
</body>
</html><?php /**PATH /home/oem/TrackingAid-System/resources/views/layouts/app.blade.php ENDPATH**/ ?>