<?php
require_once __DIR__ . '/../includes/functions.php'; requireLogin(); $user=currentUser();
if($_SERVER['REQUEST_METHOD']==='POST'){
  $pdo->prepare('UPDATE User_Account SET first_name=?, last_name=?, bio=?, location=?, birthdate=? WHERE user_id=?')->execute([
    trim($_POST['first_name']??''), trim($_POST['last_name']??''), trim($_POST['bio']??''), trim($_POST['location']??''), ($_POST['birthdate']??'')?:null, $user['user_id']
  ]);
  refreshUserSession($pdo, (int)$user['user_id']); setFlash('success','Profile updated.'); redirect('/larong_pinoy/user/profile.php');
}
$s=$pdo->prepare('SELECT first_name,last_name,bio,location,birthdate FROM User_Account WHERE user_id=?');$s->execute([$user['user_id']]);$p=$s->fetch();
$pageTitle='Edit Profile'; include __DIR__ . '/../includes/header.php';
?>
<section class="panel"><h1>Edit Profile</h1><form method="post">
<label>First Name</label><input name="first_name" value="<?php echo h($p['first_name']??''); ?>">
<label>Last Name</label><input name="last_name" value="<?php echo h($p['last_name']??''); ?>">
<label>Bio</label><textarea name="bio"><?php echo h($p['bio']??''); ?></textarea>
<label>Location</label><input name="location" value="<?php echo h($p['location']??''); ?>">
<label>Birthdate</label><input type="date" name="birthdate" value="<?php echo h($p['birthdate']??''); ?>">
<button class="btn btn-gold" type="submit">Save</button></form></section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
