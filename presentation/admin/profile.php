<?php
// ============================================================
// Presentation Layer: Admin Profile
// File: presentation/admin/profile.php
// ============================================================

require_once __DIR__ . '/../../application/config.php';
requireRole('admin');
require_once __DIR__ . '/../layout.php';

$db   = getDB();
$user = getCurrentUser();

renderHead('Admin Profile');
renderSidebar('profile');
renderFlash();
$init = strtoupper(substr($user['name'], 0, 1));
?>

<div class="page-header">
  <h1>👤 Admin Profile</h1>
  <p>Manage your administrator account</p>
</div>

<div style="display:grid;grid-template-columns:1fr 2fr;gap:24px;align-items:start;" class="profile-grid">

  <!-- Info Card -->
  <div class="card">
    <div class="card-body" style="text-align:center;">
      <div class="profile-photo" style="background:linear-gradient(135deg,#ef476f,#ffd166);"><?= $init ?></div>
      <div class="uid-badge"><?= htmlspecialchars($user['unique_id']) ?></div>
      <h3 style="margin-bottom:4px;"><?= htmlspecialchars($user['name']) ?></h3>
      <p style="color:var(--tx3);font-size:0.88rem;">👑 Administrator</p>
      <p style="font-size:0.85rem;margin-top:8px;"><?= htmlspecialchars($user['email']) ?></p>
      <p style="font-size:0.85rem;"><?= htmlspecialchars($user['college_name'] ?? '-') ?></p>
      <div style="margin-top:12px;">
        <span class="badge badge-success">Active</span>
      </div>
      <p style="font-size:0.78rem;color:var(--tx3);margin-top:12px;">
        Admin since <?= date('M Y', strtotime($user['created_at'])) ?>
      </p>
    </div>
  </div>

  <!-- Edit Form -->
  <div class="card">
    <div class="card-header"><h3>✏️ Edit Profile</h3></div>
    <div class="card-body">
      <form method="POST" action="<?= BASE_URL ?>/application/profile.php">
        <input type="hidden" name="action" value="update">
        <div class="form-row">
          <div class="form-group">
            <label>Full Name</label>
            <input type="text" class="form-control" name="name"
                   value="<?= htmlspecialchars($user['name']) ?>" required>
          </div>
          <div class="form-group">
            <label>Age</label>
            <input type="number" class="form-control" name="age"
                   value="<?= htmlspecialchars($user['age'] ?? '') ?>" min="18" max="100">
          </div>
        </div>
        <div class="form-group">
          <label>Email Address</label>
          <input type="email" class="form-control"
                 value="<?= htmlspecialchars($user['email']) ?>" disabled
                 title="Email cannot be changed">
          <small style="color:var(--tx3);">Email address cannot be changed.</small>
        </div>
        <div class="form-group">
          <label>Institution / Organisation</label>
          <input type="text" class="form-control" name="college_name"
                 value="<?= htmlspecialchars($user['college_name'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Contact Number</label>
          <input type="tel" class="form-control" name="contact_number"
                 value="<?= htmlspecialchars($user['contact_number'] ?? '') ?>">
        </div>
        <button type="submit" class="btn btn-primary">💾 Save Changes</button>
      </form>
    </div>
  </div>

</div>

<!-- Quick Stats -->
<div class="card" style="margin-top:24px;">
  <div class="card-header"><h3>📊 System Overview</h3></div>
  <div class="card-body">
    <?php
    $stats = [
      'Total Students'  => $db->query("SELECT COUNT(*) as c FROM users WHERE role='student'")->fetch_assoc()['c'],
      'Total Teachers'  => $db->query("SELECT COUNT(*) as c FROM users WHERE role='teacher'")->fetch_assoc()['c'],
      'Pending Approval'=> $db->query("SELECT COUNT(*) as c FROM users WHERE is_approved='pending' AND role!='admin'")->fetch_assoc()['c'],
      'Total Materials' => $db->query("SELECT COUNT(*) as c FROM materials")->fetch_assoc()['c'],
      'Notifications Sent' => $db->query("SELECT COUNT(*) as c FROM notifications")->fetch_assoc()['c'],
    ];
    ?>
    <div style="display:flex;flex-wrap:wrap;gap:20px;">
      <?php foreach ($stats as $label => $value): ?>
      <div style="background:#f8f9ff;border-radius:10px;padding:16px 24px;text-align:center;min-width:130px;">
        <div style="font-size:1.8rem;font-weight:800;color:var(--vb);"><?= $value ?></div>
        <div style="font-size:0.8rem;color:var(--tx3);margin-top:4px;"><?= $label ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<style>@media(max-width:700px){.profile-grid{grid-template-columns:1fr!important;}}</style>

<?php renderFooter(); ?>
