<?php
require_once __DIR__ . '/includes/functions.php';
if (isLoggedIn()) { redirect('/larong_pinoy/games/list.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = trim($_POST['first_name'] ?? '');
    $last = trim($_POST['last_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($first === '' || $username === '' || $email === '' || strlen($password) < 6) {
        setFlash('error', 'Please fill required fields. Password must be at least 6 chars.');
        redirect('/larong_pinoy/register.php');
    }
    $hash = password_hash($password, PASSWORD_BCRYPT);
    try {
        $stmt = $pdo->prepare('INSERT INTO User_Account (username, email, password_hash, first_name, last_name, role, account_status) VALUES (?, ?, ?, ?, ?, "user", "active")');
        $stmt->execute([$username, $email, $hash, $first, $last]);
        setFlash('success', 'Registration successful. You can now login.');
        redirect('/larong_pinoy/login.php');
    } catch (Throwable $e) {
        setFlash('error', 'Username or email is already used.');
        redirect('/larong_pinoy/register.php');
    }
}

$pageTitle = 'Register'; include __DIR__ . '/includes/header.php';
?>
<section class="games-page-header">
  <div class="games-eyebrow">Join The Laro</div>
  <h1 class="games-title">Create Account</h1>
  <p class="games-sub">Be part of preserving traditional Filipino games</p>
</section>
<section class="auth-wrap">
  <div class="auth-card">
    <div class="auth-head">
      <div class="auth-title">Larong Pinoy</div>
      <div class="auth-sub">Create your account</div>
    </div>
    <div class="auth-body">
      <form method="post">
        <label>First Name</label><input name="first_name" required>
        <label>Last Name</label><input name="last_name">
        <label>Username</label><input name="username" required>
        <label>Email</label><input name="email" type="email" required>
        <label>Password</label><input name="password" type="password" required>
        <div class="auth-actions">
          <button class="btn btn-gold" type="submit">Register</button>
          <a class="auth-note" href="/larong_pinoy/login.php">Already have account?</a>
        </div>
      </form>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
