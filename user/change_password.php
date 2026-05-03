<?php
require_once __DIR__ . '/../includes/functions.php'; requireLogin(); $user=currentUser();
if($_SERVER['REQUEST_METHOD']==='POST'){
  $current=$_POST['current_password']??''; $new=$_POST['new_password']??'';
  $s=$pdo->prepare('SELECT password_hash FROM User_Account WHERE user_id=?');$s->execute([$user['user_id']]);$u=$s->fetch();
  if($u && password_verify($current,$u['password_hash']) && strlen($new)>=6){
    $pdo->prepare('UPDATE User_Account SET password_hash=? WHERE user_id=?')->execute([password_hash($new,PASSWORD_BCRYPT),$user['user_id']]);
    setFlash('success','Password changed.'); redirect('/larong_pinoy/user/profile.php');
  }
  setFlash('error','Invalid current password or weak new password.');
}
$pageTitle='Change Password'; include __DIR__ . '/../includes/header.php';
?>
<section class="panel"><h1>Change Password</h1><form method="post"><label>Current Password</label><input type="password" name="current_password" required><label>New Password</label><input type="password" name="new_password" required><button class="btn btn-gold">Update Password</button></form></section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
