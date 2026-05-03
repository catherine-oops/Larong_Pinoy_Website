<?php require_once __DIR__ . '/../includes/functions.php'; requireLogin(); $u=currentUser();
$s=$pdo->prepare('SELECT p.played_date,p.location,p.duration_minutes,p.enjoyment_rating,g.game_name FROM Play_Log p JOIN Traditional_Game g ON g.game_id=p.game_id WHERE p.user_id=? ORDER BY p.played_date DESC');$s->execute([$u['user_id']]);$rows=$s->fetchAll();
$pageTitle='Play History'; include __DIR__ . '/../includes/header.php'; ?>
<section class="panel"><h1>Play History</h1><table class="table"><tr><th>Date</th><th>Game</th><th>Location</th><th>Duration</th><th>Rating</th></tr><?php foreach($rows as $x): ?><tr><td><?php echo h($x['played_date']); ?></td><td><?php echo h($x['game_name']); ?></td><td><?php echo h($x['location']); ?></td><td><?php echo (int)$x['duration_minutes']; ?>m</td><td><?php echo (int)$x['enjoyment_rating']; ?>/5</td></tr><?php endforeach; ?></table></section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
