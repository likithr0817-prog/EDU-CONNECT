<?php
// ============================================================
// Presentation Layer: Student Dashboard
// ============================================================

require_once __DIR__ . '/../../application/config.php';
requireRole('student');
require_once __DIR__ . '/../layout.php';

$db      = getDB();
$user_id = $_SESSION['user_id'];

// Stats
$mats    = $db->prepare("SELECT COUNT(*) as c FROM materials WHERE user_id = ?");
$mats->bind_param("i", $user_id); $mats->execute();
$matCount = $mats->get_result()->fetch_assoc()['c'];

$unread  = getUnreadNotifications($user_id, 'student');

$totalMat = $db->query("SELECT COUNT(*) as c FROM materials")->fetch_assoc()['c'];

// Recent materials
$recent = $db->query("
    SELECT m.*, u.name as uploader, u.role
    FROM materials m
    JOIN users u ON m.user_id = u.user_id
    ORDER BY m.upload_date DESC LIMIT 6
");

renderHead('Student Dashboard');
renderSidebar('dashboard');
renderFlash();
?>

<div class="page-header">
  <h1>📊 Student Dashboard</h1>
  <p>Welcome back, <?= htmlspecialchars($_SESSION['name']) ?>!</p>
</div>

<div class="stats-grid">
  <div class="stat-card blue">
    <div class="stat-icon">📚</div>
    <div class="stat-info">
      <div class="stat-number"><?= $matCount ?></div>
      <div class="stat-label">My Uploads</div>
    </div>
  </div>
  <div class="stat-card teal">
    <div class="stat-icon">🗂️</div>
    <div class="stat-info">
      <div class="stat-number"><?= $totalMat ?></div>
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
    <h3>📁 Recent Study Materials</h3>
    <a href="<?= BASE_URL ?>/presentation/student/materials.php" class="btn btn-outline btn-sm">View All</a>
  </div>
  <div class="card-body">
    <?php if ($recent->num_rows === 0): ?>
      <div class="empty-state">
        <div class="empty-icon">📭</div>
        <h3>No materials yet</h3>
        <p>Upload your first study material!</p>
      </div>
    <?php else: ?>
    <div class="materials-grid">
      <?php while ($m = $recent->fetch_assoc()):
        $icons = ['pdf' => '📄', 'video' => '🎬', 'image' => '🖼️', 'link' => '🔗'];
        $icon  = $icons[$m['file_type']] ?? '📁';
      ?>
      <div class="material-card">
        <div class="mat-header">
          <div class="mat-type-icon"><?= $icon ?></div>
          <div class="mat-title"><?= htmlspecialchars($m['title']) ?></div>
        </div>
        <div class="mat-body">
          <p class="mat-desc"><?= htmlspecialchars(substr($m['description'] ?? '', 0, 100)) ?></p>
          <p class="mat-meta">👤 <?= htmlspecialchars($m['uploader']) ?> (<?= ucfirst($m['role']) ?>)</p>
          <p class="mat-meta">📅 <?= date('d M Y', strtotime($m['upload_date'])) ?></p>
        </div>
        <div class="mat-actions">
          <?php if ($m['file_type'] === 'link'): ?>
            <a href="<?= htmlspecialchars($m['external_link']) ?>" target="_blank" class="btn btn-primary btn-sm">🔗 Open Link</a>
          <?php elseif ($m['file_path']): ?>
            <a href="<?= BASE_URL . '/' . $m['file_path'] ?>" target="_blank" class="btn btn-primary btn-sm">👁️ View</a>
            <a href="<?= BASE_URL . '/' . $m['file_path'] ?>" download class="btn btn-outline btn-sm">⬇️ Download</a>
          <?php endif; ?>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php renderFooter(); ?>
