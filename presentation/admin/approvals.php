<?php
require_once __DIR__ . '/../../application/config.php';
requireRole('admin');
require_once __DIR__ . '/../layout.php';

$db = getDB();
$pending = $db->query("
    SELECT u.*, s.class_name, t.subject
    FROM users u
    LEFT JOIN students s ON u.user_id = s.user_id
    LEFT JOIN teachers t ON u.user_id = t.user_id
    WHERE u.is_approved = 'pending' AND u.role != 'admin'
    ORDER BY u.created_at ASC
");

renderHead('Pending Approvals');
renderSidebar('approvals');
renderFlash();
?>

<div class="page-header">
  <h1>✅ Pending Approvals</h1>
  <p>Approve or reject new registrations</p>
</div>

<?php if ($pending->num_rows === 0): ?>
  <div class="empty-state">
    <div class="empty-icon">🎉</div>
    <h3>All caught up!</h3>
    <p>No pending registrations at the moment.</p>
  </div>
<?php else: ?>
<div style="display:flex;flex-direction:column;gap:16px;">
  <?php while ($u = $pending->fetch_assoc()): ?>
  <div class="card">
    <div class="card-body">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
        <div>
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
            <div class="user-avatar" style="width:48px;height:48px;font-size:1.2rem;">
              <?= strtoupper(substr($u['name'], 0, 1)) ?>
            </div>
            <div>
              <div style="font-weight:700;font-size:1rem;"><?= htmlspecialchars($u['name']) ?></div>
              <div style="font-size:0.8rem;color:var(--tx3);">
                <span class="badge badge-primary"><?= ucfirst($u['role']) ?></span>
                &nbsp; Registered: <?= date('d M Y H:i', strtotime($u['created_at'])) ?>
              </div>
            </div>
          </div>
          <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:6px 20px;">
            <p style="font-size:0.85rem;">📧 <?= htmlspecialchars($u['email']) ?></p>
            <p style="font-size:0.85rem;">📞 <?= htmlspecialchars($u['contact_number'] ?? '-') ?></p>
            <p style="font-size:0.85rem;">🎂 Age: <?= $u['age'] ?></p>
            <p style="font-size:0.85rem;">🏫 <?= htmlspecialchars($u['college_name'] ?? '-') ?></p>
            <?php if ($u['role'] === 'student'): ?>
              <p style="font-size:0.85rem;">📚 Class: <?= htmlspecialchars($u['class_name'] ?? '-') ?></p>
            <?php elseif ($u['role'] === 'teacher'): ?>
              <p style="font-size:0.85rem;">📖 Subject: <?= htmlspecialchars($u['subject'] ?? '-') ?></p>
            <?php endif; ?>
          </div>
        </div>
        <div style="display:flex;gap:10px;flex-shrink:0;">
          <a href="<?= BASE_URL ?>/application/admin_actions.php?action=approve&id=<?= $u['user_id'] ?>"
             class="btn btn-success">✅ Approve</a>
          <a href="<?= BASE_URL ?>/application/admin_actions.php?action=reject&id=<?= $u['user_id'] ?>"
             class="btn btn-danger delete-confirm">❌ Reject</a>
        </div>
      </div>
    </div>
  </div>
  <?php endwhile; ?>
</div>
<?php endif; ?>

<?php renderFooter(); ?>
