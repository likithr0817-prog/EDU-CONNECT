<?php
require_once __DIR__ . '/../../application/config.php';
requireRole('teacher');
require_once __DIR__ . '/../layout.php';

$db      = getDB();
$user_id = $_SESSION['user_id'];

$mats  = $db->prepare("SELECT COUNT(*) as c FROM materials WHERE user_id = ?");
$mats->bind_param("i", $user_id); $mats->execute();
$myMats = $mats->get_result()->fetch_assoc()['c'];

$totalMats  = $db->query("SELECT COUNT(*) as c FROM materials")->fetch_assoc()['c'];
$unread     = getUnreadNotifications($user_id, 'teacher');
$totalStuds = $db->query("SELECT COUNT(*) as c FROM users WHERE role='student' AND is_approved='approved'")->fetch_assoc()['c'];

// My recent uploads
$recent = $db->prepare("SELECT * FROM materials WHERE user_id = ? ORDER BY upload_date DESC LIMIT 5");
$recent->bind_param("i", $user_id); $recent->execute();
$myRecent = $recent->get_result();

renderHead('Teacher Dashboard');
renderSidebar('dashboard');
renderFlash();
?>

<div class="page-header">
  <h1>👨‍🏫 Teacher Dashboard</h1>
  <p>Welcome back, <?= htmlspecialchars($_SESSION['name']) ?>!</p>
</div>

<div class="stats-grid">
  <div class="stat-card blue">
    <div class="stat-icon">📁</div>
    <div class="stat-info">
      <div class="stat-number"><?= $myMats ?></div>
      <div class="stat-label">My Uploads</div>
    </div>
  </div>
  <div class="stat-card teal">
    <div class="stat-icon">🎓</div>
    <div class="stat-info">
      <div class="stat-number"><?= $totalStuds ?></div>
      <div class="stat-label">Active Students</div>
    </div>
  </div>
  <div class="stat-card teal">
    <div class="stat-icon">🗂️</div>
    <div class="stat-info">
      <div class="stat-number"><?= $totalMats ?></div>
      <div class="stat-label">Total Materials</div>
    </div>
  </div>
  <div class="stat-card rose">
    <div class="stat-icon">🔔</div>
    <div class="stat-info">
      <div class="stat-number"><?= $unread ?></div>
      <div class="stat-label">Unread Notifications</div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h3>📁 My Recent Uploads</h3>
    <a href="<?= BASE_URL ?>/presentation/teacher/materials.php" class="btn btn-outline btn-sm">View All</a>
  </div>
  <div class="card-body">
    <?php if ($myRecent->num_rows === 0): ?>
      <div class="empty-state">
        <div class="empty-icon">📭</div>
        <h3>No uploads yet</h3>
        <p><a href="<?= BASE_URL ?>/presentation/teacher/upload.php">Upload your first material!</a></p>
      </div>
    <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Title</th><th>Type</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody>
          <?php while ($m = $myRecent->fetch_assoc()):
            $icons = ['pdf'=>'📄','video'=>'🎬','image'=>'🖼️','link'=>'🔗'];
          ?>
          <tr>
            <td><?= htmlspecialchars($m['title']) ?></td>
            <td><?= ($icons[$m['file_type']] ?? '📁') . ' ' . ucfirst($m['file_type']) ?></td>
            <td><?= date('d M Y', strtotime($m['upload_date'])) ?></td>
            <td>
              <?php if ($m['file_type'] === 'link'): ?>
                <a href="<?= htmlspecialchars($m['external_link']) ?>" target="_blank" class="btn btn-primary btn-sm">View</a>
              <?php elseif ($m['file_path']): ?>
                <a href="<?= BASE_URL . '/' . $m['file_path'] ?>" target="_blank" class="btn btn-primary btn-sm">View</a>
              <?php endif; ?>
              <a href="<?= BASE_URL ?>/application/materials.php?action=delete&id=<?= $m['material_id'] ?>"
                 class="btn btn-danger btn-sm delete-confirm">🗑️</a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php renderFooter(); ?>
