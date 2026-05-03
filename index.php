<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Larong Pinoy - Home'; include __DIR__ . '/includes/header.php';
$stats = $pdo->query('SELECT COUNT(*) AS games_count FROM Traditional_Game')->fetch();
?>
<section class="panel landing-hero">
  <div>
    <h1 class="landing-title">Discover, Preserve, and Play Larong Pinoy</h1>
    <p class="landing-sub">
      Keep Filipino traditional games alive by exploring stories, rules, preservation-risk scores, and real player experiences.
      You can start as a guest, or log in to track favorites, comments, and share your stories.
    </p>
    <p><span class="chip"><?php echo (int)$stats['games_count']; ?> documented games</span></p>
    <div class="landing-actions">
      <a href="/larong_pinoy/login.php" class="btn btn-gold">Login</a>
      <a href="/larong_pinoy/games/list.php" class="btn btn-outline">Continue as Guest</a>
    </div>
  </div>
  <div class="kanding-wrap">
    <img class="kanding-art" src="/larong_pinoy/assets/images/kanding.png" alt="Larong Pinoy kids playing">
  </div>
</section>
<div class="grid-2">
<section class="panel"><h2>Smart Search & Filters</h2><p>Search by game name and filter by environment, preservation risk level, and age group.</p></section>
<section class="panel"><h2>Interactive Preservation</h2><p>Log your play sessions to help keep games alive and visible.</p></section>
<section class="panel"><h2>Community Comments</h2><p>Share memories and tips. </p></section>
<section class="panel"><h2>Preservation outlook</h2><p>See which games score higher on cultural endangerment.</p></section>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
