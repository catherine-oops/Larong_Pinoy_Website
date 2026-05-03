<?php require_once __DIR__ . '/functions.php'; $pageTitle = $pageTitle ?? 'Larong Pinoy'; $flash = getFlash(); $assetVersion = (string)@filemtime(__DIR__ . '/../css/common.css'); ?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- Prevent browsers from caching any page that includes this header -->
<meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
<title><?php echo h($pageTitle); ?></title>
<link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@400;700;900&family=Cinzel:wght@400;600;700&family=Lora:ital,wght@0,400;0,500;0,600;1,400;1,500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/larong_pinoy/css/common.css?v=<?php echo h($assetVersion); ?>"><link rel="stylesheet" href="/larong_pinoy/assets/css/style.css?v=<?php echo h($assetVersion); ?>">
</head><body>
<?php include __DIR__ . '/navbar.php'; ?>
<div class="bandiritas" id="bandiritasContainer"></div>
<main class="page-container">
<?php if ($flash): ?><div class="panel" style="border-left:5px solid <?php echo $flash['type']==='error'?'#c0392b':'#1e8449'; ?>"><?php echo h($flash['message']); ?></div><?php endif; ?>
