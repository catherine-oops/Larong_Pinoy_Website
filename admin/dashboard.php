<?php
require_once __DIR__ . '/../includes/functions.php'; requireAdmin();
$commentsTotal = $pdo->query('SELECT COUNT(*) c FROM Comment')->fetch()['c'];
$users = $pdo->query('SELECT COUNT(*) c FROM User_Account')->fetch()['c'];
$games = $pdo->query('SELECT COUNT(*) c FROM Traditional_Game')->fetch()['c'];
$riskRows = $pdo->query('SELECT risk_level, COUNT(*) AS total FROM Risk_Assessment GROUP BY risk_level')->fetchAll();
$riskMap = ['High' => 0, 'Medium' => 0, 'Low' => 0];
foreach ($riskRows as $r) { $riskMap[$r['risk_level']] = (int)$r['total']; }
$totalRisk = max(1, $riskMap['High'] + $riskMap['Medium'] + $riskMap['Low']);
$pageTitle='Admin Dashboard'; include __DIR__ . '/../includes/header.php';
?>
<section class="games-page-header">
  <div class="games-eyebrow">Control Center</div>
  <h1 class="games-title">Admin Dashboard</h1>
  <p class="games-sub">Monitor users, preservation-risk patterns, and site activity</p>
</section>
<section class="grid-2">
  <article class="stat-card"><div class="stat-title">Games</div><div class="stat-value"><?php echo (int)$games; ?></div></article>
  <article class="stat-card"><div class="stat-title">Users</div><div class="stat-value"><?php echo (int)$users; ?></div></article>
  <article class="stat-card"><div class="stat-title">Comments</div><div class="stat-value"><?php echo (int)$commentsTotal; ?></div></article>
</section>
<section class="panel">
  <h2>Preservation risk snapshot</h2>
  <p class="games-sub" style="margin:.2rem 0 .5rem;font-size:.78rem">Counts games by cultural endangerment score—not typhoon/earthquake (DRRM) risk.</p>
  <p>High preservation risk: <?php echo $riskMap['High']; ?></p>
  <progress max="<?php echo $totalRisk; ?>" value="<?php echo $riskMap['High']; ?>" style="width:100%;height:12px"></progress>
  <p>Medium preservation risk: <?php echo $riskMap['Medium']; ?></p>
  <progress max="<?php echo $totalRisk; ?>" value="<?php echo $riskMap['Medium']; ?>" style="width:100%;height:12px"></progress>
  <p>Low preservation risk: <?php echo $riskMap['Low']; ?></p>
  <progress max="<?php echo $totalRisk; ?>" value="<?php echo $riskMap['Low']; ?>" style="width:100%;height:12px"></progress>
</section>
<section class="panel quick-links"><a class="btn btn-gold" href="/larong_pinoy/admin/games.php">Manage Games</a> <a class="btn btn-outline" href="/larong_pinoy/admin/comments.php">Comments</a> <a class="btn btn-outline" href="/larong_pinoy/admin/risk_report.php">Preservation risk</a> <a class="btn btn-outline" href="/larong_pinoy/admin/users.php">Users</a></section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
