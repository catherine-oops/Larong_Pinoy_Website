<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment'])) {
    $id = (int)($_POST['comment_id'] ?? 0);
    if ($id > 0) {
        $pdo->prepare('DELETE FROM Comment WHERE parent_comment_id = ?')->execute([$id]);
        $pdo->prepare('DELETE FROM Comment WHERE comment_id = ?')->execute([$id]);
        setFlash('success', 'Comment deleted.');
    } else {
        setFlash('error', 'Invalid comment.');
    }
    redirect('/larong_pinoy/admin/comments.php');
}

$rows = $pdo->query('
    SELECT c.comment_id, c.game_id, c.comment_text, c.comment_date, u.username, g.game_name
    FROM Comment c
    JOIN User_Account u ON u.user_id = c.user_id
    JOIN Traditional_Game g ON g.game_id = c.game_id
    ORDER BY c.comment_date DESC
')->fetchAll();

$pageTitle = 'Comments';
include __DIR__ . '/../includes/header.php';
?>
<section class="panel">
  <h1>Comments</h1>
  <p class="games-sub" style="margin-top:.35rem">Comments appear on game pages as soon as they are posted. Remove any comment here if needed.</p>
  <?php if (!$rows): ?>
  <p>No comments yet.</p>
  <?php else: ?>
  <table class="table">
    <tr>
      <th>Date</th>
      <th>User</th>
      <th>Game</th>
      <th>Comment</th>
      <th>Action</th>
    </tr>
    <?php foreach ($rows as $r): ?>
    <tr>
      <td><?php echo h(substr($r['comment_date'], 0, 16)); ?></td>
      <td><?php echo h($r['username']); ?></td>
      <td><a href="/larong_pinoy/games/detail.php?id=<?php echo (int)$r['game_id']; ?>"><?php echo h($r['game_name']); ?></a></td>
      <td><?php
        $ct = (string)$r['comment_text'];
        echo h(strlen($ct) > 120 ? substr($ct, 0, 117) . '...' : $ct);
      ?></td>
      <td>
        <form method="post" onsubmit="return confirm('Delete this comment permanently?');">
          <input type="hidden" name="comment_id" value="<?php echo (int)$r['comment_id']; ?>">
          <input type="hidden" name="delete_comment" value="1">
          <button class="btn btn-outline" type="submit" style="border-color:#a93226;color:#a93226">Delete</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
