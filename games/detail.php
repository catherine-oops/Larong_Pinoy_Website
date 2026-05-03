<?php
require_once __DIR__ . '/../includes/functions.php';
$gameId = (int)($_GET['id'] ?? 0);
if ($gameId <= 0) { redirect('/larong_pinoy/games/list.php'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireLogin();
    $user = currentUser();
    if (isset($_POST['favorite'])) {
        $pdo->prepare('INSERT IGNORE INTO User_Favorite (user_id, game_id) VALUES (?, ?)')->execute([$user['user_id'], $gameId]);
        setFlash('success', 'Added to favorites.');
    }
    if (isset($_POST['played'])) {
        $pdo->prepare('INSERT INTO Play_Log (user_id, game_id, played_date, location, players_count, duration_minutes, enjoyment_rating, notes) VALUES (?, ?, CURDATE(), ?, ?, ?, ?, ?)')->execute([
            $user['user_id'], $gameId, trim($_POST['location'] ?? ''), (int)($_POST['players_count'] ?? 0), (int)($_POST['duration_minutes'] ?? 0), (int)($_POST['enjoyment_rating'] ?? 5), trim($_POST['play_notes'] ?? '')
        ]);
        setFlash('success', 'Play logged. Great job preserving culture!');
    }
    if (isset($_POST['comment'])) {
        $text = trim($_POST['comment_text'] ?? '');
        if ($text !== '') {
            $pdo->prepare('INSERT INTO Comment (game_id, user_id, comment_text, comment_status) VALUES (?, ?, ?, "approved")')->execute([$gameId, $user['user_id'], $text]);
            setFlash('success', 'Comment posted.');
        }
    }
    redirect('/larong_pinoy/games/detail.php?id=' . $gameId);
}
$stmt = $pdo->prepare('
    SELECT
        g.*,
        COALESCE(r.risk_level, "Medium") AS risk_level,
        COALESCE(r.risk_score, 50) AS risk_score,
        r.risk_basis,
        COALESCE(ps.preservation_status, "Unknown") AS preservation_status
    FROM Traditional_Game g
    LEFT JOIN Risk_Assessment r ON r.game_id = g.game_id
    LEFT JOIN Preservation_Status ps ON ps.game_id = g.game_id
    WHERE g.game_id = ?
    LIMIT 1
');
$stmt->execute([$gameId]); $game = $stmt->fetch();
if (!$game) { redirect('/larong_pinoy/games/list.php'); }
$c = $pdo->prepare('SELECT c.comment_text, c.comment_date, u.username FROM Comment c JOIN User_Account u ON u.user_id = c.user_id WHERE c.game_id = ? ORDER BY c.comment_date DESC');
$c->execute([$gameId]);
$comments = $c->fetchAll();

$videoId = null;
if (!empty($game['video_link'])) {
    $url = trim($game['video_link']);
    if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([A-Za-z0-9_-]{6,})/', $url, $m)) {
        $videoId = $m[1];
    }
}

$pageTitle = $game['game_name']; include __DIR__ . '/../includes/header.php';
?>
<section class="games-page-header">
  <div class="games-eyebrow">Traditional Game</div>
  <h1 class="games-title"><?php echo h($game['game_name']); ?></h1>
  <p class="games-sub">Rules, setup, strategy, and preservation insights</p>
</section>
<section class="panel">
  <h2>Game Overview</h2>
  <p class="game-meta">
    <span class="chip">📍 <?php echo h($game['play_environment']); ?></span>
    <span class="chip">⏱ <?php echo h($game['frequency_of_practice']); ?></span>
    <span class="chip">🗺 <?php echo h($game['origin_region'] ?: 'Unknown Region'); ?></span>
    <span class="chip" style="color:#fff;background:<?php echo riskColor($game['risk_level']); ?>">Preservation risk: <?php echo h($game['risk_level']); ?> (<?php echo (int)$game['risk_score']; ?>%)</span>
  </p>
  <p class="games-sub" style="margin:.25rem 0 .5rem;font-size:.82rem">This score reflects how hard the game may be to keep alive (space, gear, how often it is still played)—not disaster (DRRM) hazard.</p>
  <div class="risk-meter">
    <progress max="100" value="<?php echo (int)$game['risk_score']; ?>" style="width:100%;height:14px"></progress>
  </div>
  <p><strong>Preservation Status:</strong> <?php echo h($game['preservation_status']); ?></p>
</section>

<section class="section-grid">
  <article class="panel detail-section">
    <h3>Description</h3>
    <p><?php echo nl2br(h($game['game_description'] ?? 'No description available.')); ?></p>
  </article>
  <article class="panel detail-section">
    <h3>Core Gameplay</h3>
    <details open>
      <summary>Rules</summary>
      <p><?php echo nl2br(h($game['game_rules'] ?? 'No rules available yet.')); ?></p>
    </details>
    <details>
      <summary>Setup Instructions</summary>
      <p><?php echo nl2br(h($game['setup_instructions'] ?? 'No setup instructions available yet.')); ?></p>
    </details>
    <details>
      <summary>How To Win</summary>
      <p><?php echo nl2br(h($game['how_to_win'] ?? 'No winning condition available yet.')); ?></p>
    </details>
  </article>
  <article class="panel detail-section">
    <h3>Cultural Significance</h3>
    <p><?php echo nl2br(h($game['cultural_significance'] ?? 'No cultural notes available yet.')); ?></p>
  </article>
  <?php if ($videoId): ?>
  <article class="panel detail-section">
    <h3>Video Demo</h3>
    <iframe class="video-embed" src="https://www.youtube.com/embed/<?php echo h($videoId); ?>" title="Game Video" allowfullscreen></iframe>
  </article>
  <?php endif; ?>
  <article class="panel detail-section">
    <h3>Other Details</h3>
    <p><strong>Video:</strong> <?php echo h($game['video_link'] ?: 'N/A'); ?></p>
    <p><strong>How the score is built:</strong> <?php echo nl2br(h($game['risk_basis'] ?? 'N/A')); ?></p>
    <p><strong>Updated:</strong> <?php echo h($game['updated_at'] ?? 'N/A'); ?></p>
  </article>
</section>
<?php if (isLoggedIn()): ?><section class="panel"><form method="post" class="grid-2"><div><button name="favorite" value="1" class="btn btn-outline" type="submit">Add to Favorites</button></div>
<div><button name="played" value="1" class="btn btn-gold" type="submit">I Played This</button></div>
<div><label>Location</label><input name="location"></div><div><label>Players</label><input name="players_count" type="number" min="1" value="2"></div>
<div><label>Duration (minutes)</label><input name="duration_minutes" type="number" min="1" value="30"></div><div><label>Enjoyment (1-5)</label><input name="enjoyment_rating" type="number" min="1" max="5" value="5"></div>
<div style="grid-column:1/-1"><label>Your Comment</label><textarea name="comment_text" rows="3"></textarea><button class="btn btn-outline" type="submit" name="comment" value="1">Submit Comment</button></div></form></section><?php endif; ?>
<section class="panel">
  <h2>Community comments</h2>
  <p class="games-sub" style="margin:.35rem 0 .6rem">Comments from logged-in players. Admins can remove comments from the admin panel.</p>
  <?php if (!$comments): ?>
  <p>No comments yet.</p>
  <?php else: ?>
    <?php foreach ($comments as $cm): ?>
  <p><strong><?php echo h($cm['username']); ?></strong> — <?php echo h($cm['comment_text']); ?> <span class="chip" style="font-size:.58rem"><?php echo h(substr($cm['comment_date'], 0, 10)); ?></span></p>
    <?php endforeach; ?>
  <?php endif; ?>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
