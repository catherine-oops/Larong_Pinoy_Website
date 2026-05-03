<?php require_once __DIR__ . '/../includes/functions.php'; requireLogin(); $u=currentUser();
$s=$pdo->prepare('SELECT g.game_id,g.game_name,uf.saved_date FROM User_Favorite uf JOIN Traditional_Game g ON g.game_id=uf.game_id WHERE uf.user_id=? ORDER BY uf.saved_date DESC');$s->execute([$u['user_id']]);$rows=$s->fetchAll();
$pageTitle='Favorites'; include __DIR__ . '/../includes/header.php'; ?>
<section class="panel"><h1>Favorite Games</h1><table class="table"><tr><th>Game</th><th>Saved</th><th></th></tr><?php foreach($rows as $x): ?><tr><td><?php echo h($x['game_name']); ?></td><td><?php echo h($x['saved_date']); ?></td><td><a href="/larong_pinoy/games/detail.php?id=<?php echo (int)$x['game_id']; ?>">Open</a></td></tr><?php endforeach; ?></table></section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
