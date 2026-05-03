<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$me = (int)(currentUser()['user_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_user'])) {
        $uid = (int)($_POST['user_id'] ?? 0);
        if ($uid <= 0) {
            setFlash('error', 'Invalid user.');
        } elseif ($uid === $me) {
            setFlash('error', 'You cannot delete your own account from this screen.');
        } else {
            $st = $pdo->prepare('SELECT role FROM User_Account WHERE user_id = ?');
            $st->execute([$uid]);
            $row = $st->fetch();
            if (!$row) {
                setFlash('error', 'User not found.');
            } elseif ($row['role'] === 'admin') {
                $adminCount = (int)$pdo->query('SELECT COUNT(*) FROM User_Account WHERE role = "admin"')->fetchColumn();
                if ($adminCount <= 1) {
                    setFlash('error', 'Cannot delete the only admin account.');
                } else {
                    $pdo->prepare('UPDATE Risk_Assessment SET assessed_by = NULL WHERE assessed_by = ?')->execute([$uid]);
                    $pdo->prepare('UPDATE Preservation_Status SET documented_by = NULL WHERE documented_by = ?')->execute([$uid]);
                    $pdo->prepare('DELETE FROM User_Account WHERE user_id = ?')->execute([$uid]);
                    setFlash('success', 'User deleted.');
                }
            } else {
                $pdo->prepare('UPDATE Risk_Assessment SET assessed_by = NULL WHERE assessed_by = ?')->execute([$uid]);
                $pdo->prepare('UPDATE Preservation_Status SET documented_by = NULL WHERE documented_by = ?')->execute([$uid]);
                $pdo->prepare('DELETE FROM User_Account WHERE user_id = ?')->execute([$uid]);
                setFlash('success', 'User deleted.');
            }
        }
        redirect('/larong_pinoy/admin/users.php');
    }

    if (isset($_POST['update_role'])) {
        $uid = (int)($_POST['user_id'] ?? 0);
        $role = $_POST['role'] ?? '';
        if ($uid <= 0 || !in_array($role, ['admin', 'user'], true)) {
            setFlash('error', 'Invalid request.');
        } else {
            $st = $pdo->prepare('SELECT role FROM User_Account WHERE user_id = ?');
            $st->execute([$uid]);
            $row = $st->fetch();
            if (!$row) {
                setFlash('error', 'User not found.');
            } elseif ($uid === $me && $row['role'] === 'admin' && $role === 'user') {
                $adminCount = (int)$pdo->query('SELECT COUNT(*) FROM User_Account WHERE role = "admin"')->fetchColumn();
                if ($adminCount <= 1) {
                    setFlash('error', 'You are the only admin. Promote another user to admin before demoting yourself.');
                } else {
                    $pdo->prepare('UPDATE User_Account SET role = ? WHERE user_id = ?')->execute([$role, $uid]);
                    refreshUserSession($pdo, $me);
                    setFlash('success', 'Role updated.');
                }
            } else {
                $pdo->prepare('UPDATE User_Account SET role = ? WHERE user_id = ?')->execute([$role, $uid]);
                if ($uid === $me) {
                    refreshUserSession($pdo, $me);
                }
                setFlash('success', 'Role updated.');
            }
        }
        redirect('/larong_pinoy/admin/users.php');
    }
}

$rows = $pdo->query('SELECT user_id, username, email, role, account_status FROM User_Account ORDER BY created_at DESC')->fetchAll();
$pageTitle = 'Manage Users';
include __DIR__ . '/../includes/header.php';
?>
<section class="panel">
  <h1>Users</h1>
  <p class="games-sub" style="margin:.35rem 0 .75rem">Change a user’s role or remove an account. You cannot delete yourself or the last admin.</p>
  <table class="table">
    <tr>
      <th>Username</th>
      <th>Email</th>
      <th>Role</th>
      <th>Status</th>
      <th>Actions</th>
    </tr>
    <?php foreach ($rows as $u): ?>
    <tr>
      <td><?php echo h($u['username']); ?><?php echo (int)$u['user_id'] === $me ? ' <span class="chip">you</span>' : ''; ?></td>
      <td><?php echo h($u['email']); ?></td>
      <td>
        <form method="post" style="display:flex;flex-wrap:wrap;gap:.4rem;align-items:center">
          <input type="hidden" name="user_id" value="<?php echo (int)$u['user_id']; ?>">
          <input type="hidden" name="update_role" value="1">
          <select name="role" aria-label="Role for <?php echo h($u['username']); ?>">
            <option value="user" <?php echo $u['role'] === 'user' ? 'selected' : ''; ?>>user</option>
            <option value="admin" <?php echo $u['role'] === 'admin' ? 'selected' : ''; ?>>admin</option>
          </select>
          <button class="btn btn-outline" type="submit">Save role</button>
        </form>
      </td>
      <td><?php echo h($u['account_status']); ?></td>
      <td>
        <?php if ((int)$u['user_id'] !== $me): ?>
        <form method="post" onsubmit="return confirm('Permanently delete this user account? This cannot be undone.');">
          <input type="hidden" name="user_id" value="<?php echo (int)$u['user_id']; ?>">
          <input type="hidden" name="delete_user" value="1">
          <button class="btn btn-outline" type="submit" style="border-color:#a93226;color:#a93226">Delete user</button>
        </form>
        <?php else: ?>
        <span class="chip">—</span>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
