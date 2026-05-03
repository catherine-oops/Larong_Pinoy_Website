<?php
require_once __DIR__ . '/../includes/functions.php';
$id1=(int)($_GET['a']??1); $id2=(int)($_GET['b']??2);
$stmt=$pdo->prepare('SELECT game_id, game_name, play_environment, frequency_of_practice, COALESCE(risk_level,"Medium") risk_level, COALESCE(risk_score,50) risk_score FROM vw_Game_Details WHERE game_id IN (?,?)');
$stmt->execute([$id1,$id2]); $rows=$stmt->fetchAll();
$pageTitle='Compare Games'; include __DIR__ . '/../includes/header.php';
?>
<section class="panel"><h1>Compare Games</h1><p>Tip: use URL like <code>?a=1&b=7</code>.</p></section>
<section class="grid-2"><?php foreach($rows as $g): ?><article class="panel"><h2><?php echo h($g['game_name']); ?></h2><p>Environment: <?php echo h($g['play_environment']); ?></p><p>Frequency: <?php echo h($g['frequency_of_practice']); ?></p><p><span class="chip" style="color:#fff;background:<?php echo riskColor($g['risk_level']); ?>">Preservation risk: <?php echo h($g['risk_level']); ?> — <?php echo (int)$g['risk_score']; ?>%</span></p></article><?php endforeach; ?></section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
