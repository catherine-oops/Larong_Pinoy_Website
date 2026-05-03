<?php
require_once __DIR__ . '/includes/functions.php';
if (isLoggedIn()) { redirect('/larong_pinoy/games/list.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT user_id, username, email, first_name, last_name, role, password_hash FROM User_Account WHERE username = ? AND account_status = "active"');
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        unset($user['password_hash']);
        $_SESSION['user'] = $user;
        $pdo->prepare('UPDATE User_Account SET last_login = NOW() WHERE user_id = ?')->execute([$user['user_id']]);
        $pdo->prepare('INSERT INTO User_Activity_Log (user_id, activity_type, details) VALUES (?, "login", "User login")')->execute([$user['user_id']]);
        setFlash('success', 'Welcome back, ' . $user['username'] . '!');
        redirect('/larong_pinoy/games/list.php');
    }
    setFlash('error', 'Invalid username or password.');
    redirect('/larong_pinoy/login.php');
}

$pageTitle = 'Login'; include __DIR__ . '/includes/header.php';
?>
<section class="games-page-header">
  <div class="games-eyebrow">Welcome Back</div>
  <h1 class="games-title">Login</h1>
  <p class="games-sub">Sign in to save favorites, comments, and play history</p>
</section>
<section class="auth-wrap">
  <div class="auth-card">
    <div class="auth-head">
      <div class="auth-title">Larong Pinoy</div>
      <div class="auth-sub">Sign in to your account</div>
    </div>
    <div class="auth-body">
      <form method="post">
        <label>Username</label><input name="username" required>
        <label>Password</label><input name="password" type="password" required>
        <div class="auth-actions">
          <button class="btn btn-gold" type="submit">Log In</button>
          <a class="auth-note" href="/larong_pinoy/register.php">Create account</a>
        </div>
      </form>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
