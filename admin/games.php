<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$environments = ['Indoor', 'Outdoor', 'Both'];
$frequencies = ['Frequent', 'Occasional', 'Rare', 'Extinct'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_setup_links') {
        $gameId = (int)($_POST['game_id'] ?? 0);
        $requirementId = (int)($_POST['requirement_id'] ?? 0);
        $equipmentId = (int)($_POST['equipment_id'] ?? 0);
        $ageId = (int)($_POST['age_id'] ?? 0);
        $addedAny = false;

        if ($gameId > 0 && ($requirementId > 0 || $equipmentId > 0)) {
            $stmt = $pdo->prepare('
                INSERT INTO Game_Requirement (game_id, requirement_id, equipment_id)
                VALUES (?, ?, ?)
            ');
            $stmt->execute([
                $gameId,
                $requirementId > 0 ? $requirementId : null,
                $equipmentId > 0 ? $equipmentId : null
            ]);
            $addedAny = true;
        }

        if ($gameId > 0 && $ageId > 0) {
            $check = $pdo->prepare('SELECT game_age_id FROM Game_Age WHERE game_id = ? AND age_id = ? LIMIT 1');
            $check->execute([$gameId, $ageId]);
            if (!$check->fetch()) {
                $pdo->prepare('INSERT INTO Game_Age (game_id, age_id) VALUES (?, ?)')->execute([$gameId, $ageId]);
                $addedAny = true;
            }
        }

        if ($addedAny) {
            setFlash('success', 'Setup links added.');
        } else {
            setFlash('error', 'Select at least one: requirement/equipment or age bracket.');
        }
        redirect('/larong_pinoy/admin/games.php?edit=' . $gameId);
    }

    if ($action === 'delete_requirement_link') {
        $gameId = (int)($_POST['game_id'] ?? 0);
        $gameReqId = (int)($_POST['game_req_id'] ?? 0);
        if ($gameReqId > 0) {
            $pdo->prepare('DELETE FROM Game_Requirement WHERE game_req_id = ?')->execute([$gameReqId]);
            setFlash('success', 'Requirement link removed.');
        }
        redirect('/larong_pinoy/admin/games.php?edit=' . $gameId);
    }

    if ($action === 'delete_age_link') {
        $gameId = (int)($_POST['game_id'] ?? 0);
        $gameAgeId = (int)($_POST['game_age_id'] ?? 0);
        if ($gameAgeId > 0) {
            $pdo->prepare('DELETE FROM Game_Age WHERE game_age_id = ?')->execute([$gameAgeId]);
            setFlash('success', 'Age bracket removed.');
        }
        redirect('/larong_pinoy/admin/games.php?edit=' . $gameId);
    }

    if ($action === 'delete') {
        $gameId = (int)($_POST['game_id'] ?? 0);
        if ($gameId > 0) {
            $pdo->prepare('DELETE FROM Traditional_Game WHERE game_id = ?')->execute([$gameId]);
            setFlash('success', 'Game deleted.');
        }
        redirect('/larong_pinoy/admin/games.php');
    }

    if ($action === 'create' || $action === 'update') {
        $gameId = (int)($_POST['game_id'] ?? 0);
        $gameName = trim($_POST['game_name'] ?? '');
        $gameDescription = trim($_POST['game_description'] ?? '');
        $gameRules = trim($_POST['game_rules'] ?? '');
        $setupInstructions = trim($_POST['setup_instructions'] ?? '');
        $howToWin = trim($_POST['how_to_win'] ?? '');
        $playEnvironment = trim($_POST['play_environment'] ?? 'Outdoor');
        $frequency = trim($_POST['frequency_of_practice'] ?? 'Occasional');
        $videoLink = trim($_POST['video_link'] ?? '');
        $cultural = trim($_POST['cultural_significance'] ?? '');
        $region = trim($_POST['origin_region'] ?? '');

        if ($gameName === '' || !in_array($playEnvironment, $environments, true) || !in_array($frequency, $frequencies, true)) {
            setFlash('error', 'Please fill required fields correctly.');
            redirect('/larong_pinoy/admin/games.php' . ($gameId > 0 ? '?edit=' . $gameId : ''));
        }

        if ($action === 'create') {
            $stmt = $pdo->prepare('
                INSERT INTO Traditional_Game (
                    game_name, game_description, game_rules, setup_instructions, how_to_win,
                    play_environment, frequency_of_practice, video_link, cultural_significance, origin_region
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                $gameName, $gameDescription, $gameRules, $setupInstructions, $howToWin,
                $playEnvironment, $frequency, $videoLink, $cultural, $region
            ]);
            setFlash('success', 'New game added.');
            $newGameId = (int)$pdo->lastInsertId();
            if ($newGameId > 0) {
                redirect('/larong_pinoy/admin/games.php?edit=' . $newGameId);
            }
        } else {
            $stmt = $pdo->prepare('
                UPDATE Traditional_Game
                SET
                    game_name = ?, game_description = ?, game_rules = ?, setup_instructions = ?, how_to_win = ?,
                    play_environment = ?, frequency_of_practice = ?, video_link = ?, cultural_significance = ?, origin_region = ?
                WHERE game_id = ?
            ');
            $stmt->execute([
                $gameName, $gameDescription, $gameRules, $setupInstructions, $howToWin,
                $playEnvironment, $frequency, $videoLink, $cultural, $region, $gameId
            ]);
            setFlash('success', 'Game updated.');
        }

        redirect('/larong_pinoy/admin/games.php' . ($action === 'update' ? '?edit=' . $gameId : ''));
    }
}

$editId = (int)($_GET['edit'] ?? 0);
$editing = null;
if ($editId > 0) {
    $s = $pdo->prepare('SELECT * FROM Traditional_Game WHERE game_id = ?');
    $s->execute([$editId]);
    $editing = $s->fetch();
}

$physicalRows = $pdo->query('
    SELECT requirement_id, physical_demand_level, level_description
    FROM Physical_Requirement
    ORDER BY physical_demand_level, requirement_id
')->fetchAll();

$equipmentRows = $pdo->query('
    SELECT equipment_id, equipment_level, level_description
    FROM Equipment_Requirement
    ORDER BY equipment_level, equipment_id
')->fetchAll();

$ageRows = $pdo->query('
    SELECT age_id, age_range, age_description
    FROM Age_Bracket
    ORDER BY age_id
')->fetchAll();

$linkedRequirements = [];
$linkedAges = [];
if ($editing) {
    $lr = $pdo->prepare('
        SELECT
            gr.game_req_id,
            pr.requirement_id,
            pr.physical_demand_level,
            pr.level_description AS physical_desc,
            er.equipment_id,
            er.equipment_level,
            er.level_description AS equipment_desc
        FROM Game_Requirement gr
        LEFT JOIN Physical_Requirement pr ON pr.requirement_id = gr.requirement_id
        LEFT JOIN Equipment_Requirement er ON er.equipment_id = gr.equipment_id
        WHERE gr.game_id = ?
        ORDER BY gr.game_req_id DESC
    ');
    $lr->execute([(int)$editing['game_id']]);
    $linkedRequirements = $lr->fetchAll();

    $la = $pdo->prepare('
        SELECT ga.game_age_id, ab.age_id, ab.age_range, ab.age_description
        FROM Game_Age ga
        INNER JOIN Age_Bracket ab ON ab.age_id = ga.age_id
        WHERE ga.game_id = ?
        ORDER BY ga.game_age_id DESC
    ');
    $la->execute([(int)$editing['game_id']]);
    $linkedAges = $la->fetchAll();
}

$rows = $pdo->query('
    SELECT game_id, game_name, play_environment, frequency_of_practice, updated_at
    FROM Traditional_Game
    ORDER BY game_name
')->fetchAll();

$pageTitle = 'Manage Games';
include __DIR__ . '/../includes/header.php';
?>

<section class="panel" id="top">
  <h1><?php echo $editing ? 'Edit Game' : 'Add New Game'; ?></h1>
  <form method="post" class="grid-2">
    <input type="hidden" name="action" value="<?php echo $editing ? 'update' : 'create'; ?>">
    <input type="hidden" name="game_id" value="<?php echo (int)($editing['game_id'] ?? 0); ?>">

    <div style="grid-column:1/-1">
      <label>Game Name *</label>
      <input name="game_name" required value="<?php echo h($editing['game_name'] ?? ''); ?>">
    </div>

    <div>
      <label>Environment *</label>
      <select name="play_environment" required>
        <?php foreach ($environments as $env): ?>
          <option value="<?php echo h($env); ?>" <?php echo (($editing['play_environment'] ?? 'Outdoor') === $env) ? 'selected' : ''; ?>><?php echo h($env); ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label>Frequency *</label>
      <select name="frequency_of_practice" required>
        <?php foreach ($frequencies as $f): ?>
          <option value="<?php echo h($f); ?>" <?php echo (($editing['frequency_of_practice'] ?? 'Occasional') === $f) ? 'selected' : ''; ?>><?php echo h($f); ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div style="grid-column:1/-1">
      <label>Description</label>
      <textarea name="game_description" rows="3"><?php echo h($editing['game_description'] ?? ''); ?></textarea>
    </div>

    <div style="grid-column:1/-1">
      <label>Rules</label>
      <textarea name="game_rules" rows="4"><?php echo h($editing['game_rules'] ?? ''); ?></textarea>
    </div>

    <div style="grid-column:1/-1">
      <label>Setup Instructions</label>
      <textarea name="setup_instructions" rows="3"><?php echo h($editing['setup_instructions'] ?? ''); ?></textarea>
    </div>

    <div style="grid-column:1/-1">
      <label>How To Win</label>
      <textarea name="how_to_win" rows="3"><?php echo h($editing['how_to_win'] ?? ''); ?></textarea>
    </div>

    <div>
      <label>Origin Region</label>
      <input name="origin_region" value="<?php echo h($editing['origin_region'] ?? ''); ?>">
    </div>

    <div>
      <label>Video Link</label>
      <input name="video_link" value="<?php echo h($editing['video_link'] ?? ''); ?>">
    </div>

    <div style="grid-column:1/-1">
      <label>Cultural Significance</label>
      <textarea name="cultural_significance" rows="3"><?php echo h($editing['cultural_significance'] ?? ''); ?></textarea>
    </div>

    <div class="quick-links" style="grid-column:1/-1">
      <button class="btn btn-gold" type="submit"><?php echo $editing ? 'Save Changes' : 'Add Game'; ?></button>
      <?php if ($editing): ?><a class="btn btn-outline" href="/larong_pinoy/admin/games.php">Cancel Edit</a><?php endif; ?>
    </div>
  </form>
</section>

<section class="panel">
  <h2>Game Requirements & Age Setup</h2>
  <?php if ($editing): ?>
    <p>Manage gameplay requirement links for: <strong><?php echo h($editing['game_name']); ?></strong></p>
  <?php else: ?>
    <p>Select a game first to manage requirement and age links.</p>
  <?php endif; ?>

  <?php if ($editing): ?>

  <div class="grid-2">
    <div>
      <h3>Add Setup Links (One Button)</h3>
      <form method="post">
        <input type="hidden" name="action" value="add_setup_links">
        <input type="hidden" name="game_id" value="<?php echo (int)$editing['game_id']; ?>">

        <label>Physical Requirement (optional)</label>
        <select name="requirement_id">
          <option value="0">-- None --</option>
          <?php foreach ($physicalRows as $pr): ?>
            <option value="<?php echo (int)$pr['requirement_id']; ?>">
              L<?php echo (int)$pr['physical_demand_level']; ?> - <?php echo h($pr['level_description'] ?: 'No description'); ?>
            </option>
          <?php endforeach; ?>
        </select>

        <label>Equipment Requirement (optional)</label>
        <select name="equipment_id">
          <option value="0">-- None --</option>
          <?php foreach ($equipmentRows as $er): ?>
            <option value="<?php echo (int)$er['equipment_id']; ?>">
              L<?php echo (int)$er['equipment_level']; ?> - <?php echo h($er['level_description'] ?: 'No description'); ?>
            </option>
          <?php endforeach; ?>
        </select>

        <label>Age Bracket (optional)</label>
        <select name="age_id">
          <option value="0">-- None --</option>
          <?php foreach ($ageRows as $ab): ?>
            <option value="<?php echo (int)$ab['age_id']; ?>">
              <?php echo h($ab['age_range']); ?> - <?php echo h($ab['age_description'] ?: 'No description'); ?>
            </option>
          <?php endforeach; ?>
        </select>

        <div class="quick-links" style="margin-top:10px">
          <button class="btn btn-gold" type="submit">Add Selected Setup</button>
        </div>
      </form>

      <table class="table" style="margin-top:12px">
        <tr><th>Physical</th><th>Equipment</th><th></th></tr>
        <?php foreach ($linkedRequirements as $lr): ?>
          <tr>
            <td>
              <?php echo $lr['requirement_id'] ? ('L' . (int)$lr['physical_demand_level'] . ' - ' . h($lr['physical_desc'] ?: 'No description')) : 'None'; ?>
            </td>
            <td>
              <?php echo $lr['equipment_id'] ? ('L' . (int)$lr['equipment_level'] . ' - ' . h($lr['equipment_desc'] ?: 'No description')) : 'None'; ?>
            </td>
            <td>
              <form method="post" onsubmit="return confirm('Remove this requirement link?');" style="display:inline">
                <input type="hidden" name="action" value="delete_requirement_link">
                <input type="hidden" name="game_id" value="<?php echo (int)$editing['game_id']; ?>">
                <input type="hidden" name="game_req_id" value="<?php echo (int)$lr['game_req_id']; ?>">
                <button class="btn btn-outline" type="submit">Remove</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
    </div>

    <div>
      <h3>Current Age Brackets</h3>
      <table class="table" style="margin-top:12px">
        <tr><th>Age Range</th><th>Description</th><th></th></tr>
        <?php foreach ($linkedAges as $la): ?>
          <tr>
            <td><?php echo h($la['age_range']); ?></td>
            <td><?php echo h($la['age_description']); ?></td>
            <td>
              <form method="post" onsubmit="return confirm('Remove this age bracket?');" style="display:inline">
                <input type="hidden" name="action" value="delete_age_link">
                <input type="hidden" name="game_id" value="<?php echo (int)$editing['game_id']; ?>">
                <input type="hidden" name="game_age_id" value="<?php echo (int)$la['game_age_id']; ?>">
                <button class="btn btn-outline" type="submit">Remove</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
    </div>
  </div>
  <?php else: ?>
    <div class="quick-links">
      <a class="btn btn-outline" href="#top">Create a game above first</a>
    </div>
  <?php endif; ?>
</section>

<section class="panel">
  <h2>All Games</h2>
  <table class="table">
    <tr>
      <th>Name</th>
      <th>Environment</th>
      <th>Frequency</th>
      <th>Updated</th>
      <th>Actions</th>
    </tr>
    <?php foreach ($rows as $g): ?>
      <tr>
        <td><?php echo h($g['game_name']); ?></td>
        <td><?php echo h($g['play_environment']); ?></td>
        <td><?php echo h($g['frequency_of_practice']); ?></td>
        <td><?php echo h($g['updated_at']); ?></td>
        <td class="quick-links">
          <a class="btn btn-outline" href="/larong_pinoy/admin/games.php?edit=<?php echo (int)$g['game_id']; ?>">Edit</a>
          <a class="btn btn-outline" href="/larong_pinoy/games/detail.php?id=<?php echo (int)$g['game_id']; ?>">View</a>
          <form method="post" onsubmit="return confirm('Delete this game?');" style="display:inline">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="game_id" value="<?php echo (int)$g['game_id']; ?>">
            <button class="btn btn-outline" type="submit">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
