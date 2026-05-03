<?php require_once __DIR__ . '/../includes/functions.php'; requireAdmin();
$rows=$pdo->query('SELECT risk_level, COUNT(*) total FROM Risk_Assessment GROUP BY risk_level')->fetchAll();
$list=$pdo->query('SELECT g.game_name,r.risk_level,r.risk_score FROM Risk_Assessment r JOIN Traditional_Game g ON g.game_id=r.game_id ORDER BY r.risk_score DESC LIMIT 15')->fetchAll();
$pageTitle = 'Preservation risk report';
include __DIR__ . '/../includes/header.php';
?>
<section class="panel">
  <h1>Preservation risk report</h1>
  <p class="games-sub" style="margin:.25rem 0 .75rem">Cultural endangerment index (physical demand, space, equipment, practice frequency)—not DRRM / natural hazards.</p>
  <h2>Distribution</h2>
  <?php foreach ($rows as $r): ?>
  <p><span class="chip" style="color:#fff;background:<?php echo riskColor($r['risk_level']); ?>"><?php echo h($r['risk_level']); ?></span> <?php echo (int)$r['total']; ?> games</p>
  <?php endforeach; ?>
</section>
<section class="panel">
  <h2>Highest preservation risk</h2>
  <table class="table">
    <tr><th>Game</th><th>Level</th><th>Score</th></tr>
    <?php foreach ($list as $x): ?>
    <tr><td><?php echo h($x['game_name']); ?></td><td><?php echo h($x['risk_level']); ?></td><td><?php echo (int)$x['risk_score']; ?></td></tr>
    <?php endforeach; ?>
  </table>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
