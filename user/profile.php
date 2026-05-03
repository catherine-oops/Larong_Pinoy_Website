<?php
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
$user = currentUser();
$stmt = $pdo->prepare('SELECT * FROM vw_User_Profile WHERE user_id=?'); $stmt->execute([$user['user_id']]); $profile = $stmt->fetch();
$activities = $pdo->prepare('SELECT activity_type, details, activity_date FROM User_Activity_Log WHERE user_id=? ORDER BY activity_date DESC LIMIT 6');
$activities->execute([$user['user_id']]);
$recent = $activities->fetchAll();
$pageTitle='My Profile'; include __DIR__ . '/../includes/header.php';
?>
<section class="games-page-header">
  <div class="games-eyebrow">Player Space</div>
  <h1 class="games-title">My Profile</h1>
  <p class="games-sub">Track your activity and cultural game journey</p>
</section>
<section class="panel">
  <h1><?php echo h(($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? '')); ?></h1>
  <p>@<?php echo h($profile['username']); ?> | <?php echo h($profile['email']); ?></p>
  <p><?php echo h($profile['bio'] ?? 'No bio yet.'); ?></p>
</section>
<section class="grid-2">
  <article class="stat-card"><div class="stat-title">Favorites</div><div class="stat-value"><?php echo (int)$profile['favorites_count']; ?></div></article>
  <article class="stat-card"><div class="stat-title">Play Sessions</div><div class="stat-value"><?php echo (int)$profile['total_sessions']; ?></div></article>
</section>
<section class="panel quick-links"><a class="btn btn-outline" href="/larong_pinoy/user/edit_profile.php">Edit Profile</a> <a class="btn btn-outline" href="/larong_pinoy/user/change_password.php">Change Password</a></section>
<section class="panel">
  <h2>Recent Activity</h2>
  <?php foreach ($recent as $item): ?>
    <div class="timeline-item">
      <strong><?php echo h(str_replace('_', ' ', strtoupper($item['activity_type']))); ?></strong>
      <div><?php echo h($item['details'] ?? ''); ?></div>
      <small><?php echo h($item['activity_date']); ?></small>
    </div>
  <?php endforeach; ?>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
