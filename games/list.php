<?php
require_once __DIR__ . '/../includes/functions.php';
$q = trim($_GET['q'] ?? '');
$env = trim($_GET['env'] ?? '');
$risk = trim($_GET['risk'] ?? '');
$age = (int)($_GET['age'] ?? 0);
$sql = 'SELECT g.game_id, g.game_name, g.game_description, g.play_environment, g.frequency_of_practice, COALESCE(r.risk_level,"Medium") AS risk_level, COALESCE(r.risk_score,50) AS risk_score
FROM Traditional_Game g
LEFT JOIN Risk_Assessment r ON r.game_id = g.game_id
LEFT JOIN Game_Age ga ON ga.game_id = g.game_id
WHERE 1=1';
$params = [];
if ($q !== '') { $sql .= ' AND g.game_name LIKE ?'; $params[] = "%$q%"; }
if ($env !== '') { $sql .= ' AND g.play_environment = ?'; $params[] = $env; }
if ($risk !== '') { $sql .= ' AND r.risk_level = ?'; $params[] = $risk; }
if ($age > 0) { $sql .= ' AND ga.age_id = ?'; $params[] = $age; }
$sql .= ' GROUP BY g.game_id ORDER BY r.risk_score DESC, g.game_name';
$stmt = $pdo->prepare($sql); $stmt->execute($params); $games = $stmt->fetchAll();
$ages = $pdo->query('SELECT age_id, age_range FROM Age_Bracket ORDER BY age_id')->fetchAll();
$pageTitle = 'Games List'; include __DIR__ . '/../includes/header.php';
?>
<section class="games-page-header">
  <div class="games-eyebrow">Collection Of</div>
  <h1 class="games-title">Traditional Games</h1>
  <p class="games-sub">Laro ng lahi, kwento ng kabataan, ligaya ng bawat henerasyon</p>
</section>
<section class="panel"><h2>Browse Games</h2>
<form method="get" class="grid-2"><div><label>Search</label><input name="q" value="<?php echo h($q); ?>" placeholder="e.g., Patintero"></div>
<div><label>Environment</label><select name="env"><option value="">All</option><option <?php echo $env==='Indoor'?'selected':''; ?>>Indoor</option><option <?php echo $env==='Outdoor'?'selected':''; ?>>Outdoor</option><option <?php echo $env==='Both'?'selected':''; ?>>Both</option></select></div>
<div><label>Preservation risk</label><select name="risk"><option value="">All</option><option <?php echo $risk==='High'?'selected':''; ?>>High</option><option <?php echo $risk==='Medium'?'selected':''; ?>>Medium</option><option <?php echo $risk==='Low'?'selected':''; ?>>Low</option></select></div>
<div><label>Age Bracket</label><select name="age"><option value="0">All</option><?php foreach($ages as $a): ?><option value="<?php echo (int)$a['age_id']; ?>" <?php echo $age===(int)$a['age_id']?'selected':''; ?>><?php echo h($a['age_range']); ?></option><?php endforeach; ?></select></div>
<div><button class="btn btn-gold" type="submit">Apply Filters</button></div></form></section>
<section class="grid-2 games-list-grid"><?php foreach ($games as $g): ?><article class="panel game-card games-list-card">
  <div class="games-list-card-body">
    <h2><?php echo h($g['game_name']); ?></h2>
    <p class="game-meta"><span class="chip"><?php echo h($g['play_environment']); ?></span> <span class="chip"><?php echo h($g['frequency_of_practice']); ?></span> <span class="chip" style="color:#fff;background:<?php echo riskColor($g['risk_level']); ?>">Preservation: <?php echo h($g['risk_level']); ?> (<?php echo (int)$g['risk_score']; ?>)</span></p>
    <p><?php echo h(substr($g['game_description'] ?? 'No description yet.', 0, 160)); ?>...</p>
  </div>
  <div class="games-list-card-footer">
    <a class="btn btn-outline games-list-card-btn" href="/larong_pinoy/games/detail.php?id=<?php echo (int)$g['game_id']; ?>">View Details</a>
  </div>
</article><?php endforeach; ?></section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
