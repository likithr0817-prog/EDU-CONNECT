<?php
require_once __DIR__ . '/../../application/config.php';
requireRole('admin');
require_once __DIR__ . '/../layout.php';

$db = getDB();

$totalStudents = $db->query("SELECT COUNT(*) as c FROM users WHERE role='student'")->fetch_assoc()['c'];
$approvedStuds = $db->query("SELECT COUNT(*) as c FROM users WHERE role='student' AND is_approved='approved'")->fetch_assoc()['c'];
$totalTeachers = $db->query("SELECT COUNT(*) as c FROM users WHERE role='teacher'")->fetch_assoc()['c'];
$approvedTeach = $db->query("SELECT COUNT(*) as c FROM users WHERE role='teacher' AND is_approved='approved'")->fetch_assoc()['c'];
$pendingCount  = $db->query("SELECT COUNT(*) as c FROM users WHERE is_approved='pending' AND role!='admin'")->fetch_assoc()['c'];
$totalMats     = $db->query("SELECT COUNT(*) as c FROM materials")->fetch_assoc()['c'];
$totalNotifs   = $db->query("SELECT COUNT(*) as c FROM notifications")->fetch_assoc()['c'];

// Recent registrations
$recent = $db->query("SELECT * FROM users WHERE role != 'admin' ORDER BY created_at DESC LIMIT 8");

renderHead('Admin Dashboard');
renderSidebar('dashboard');
renderFlash();
?>

<div class="page-header">
  <h1>👑 Admin Dashboard</h1>
  <p>System overview and management</p>
</div>

<div class="stats-grid">
  <div class="stat-card blue">
    <div class="stat-icon">🎓</div>
    <div class="stat-info">
      <div class="stat-number"><?= $totalStudents ?></div>
      <div class="stat-label">Total Students</div>
    </div>
  </div>
  <div class="stat-card teal">
    <div class="stat-icon">👨‍🏫</div>
    <div class="stat-info">
      <div class="stat-number"><?= $totalTeachers ?></div>
      <div class="stat-label">Total Teachers</div>
    </div>
  </div>
  <div class="stat-card amber">
    <div class="stat-icon">⏳</div>
    <div class="stat-info">
      <div class="stat-number"><?= $pendingCount ?></div>
      <div class="stat-label">Pending Approvals</div>
    </div>
  </div>
  <div class="stat-card teal">
    <div class="stat-icon">📁</div>
    <div class="stat-info">
      <div class="stat-number"><?= $totalMats ?></div>
      <div class="stat-label">Materials Uploaded</div>
    </div>
  </div>
  <div class="stat-card rose">
    <div class="stat-icon">🔔</div>
    <div class="stat-info">
      <div class="stat-number"><?= $totalNotifs ?></div>
      <div class="stat-label">Notifications Sent</div>
    </div>
  </div>
</div>

<!-- Quick stats detail -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;" class="two-col">
  <div class="card">
    <div class="card-body" style="text-align:center;">
      <div style="font-size:1rem;font-weight:700;margin-bottom:12px;">🎓 Student Status</div>
      <div style="display:flex;justify-content:space-around;">
        <div><div style="font-size:1.6rem;font-weight:800;color:var(--success);"><?= $approvedStuds ?></div><div style="font-size:0.8rem;color:var(--tx3);">Approved</div></div>
        <div><div style="font-size:1.6rem;font-weight:800;color:var(--warning);"><?= $totalStudents - $approvedStuds ?></div><div style="font-size:0.8rem;color:var(--tx3);">Pending/Rejected</div></div>
      </div>
    </div>
  </div>
  <div class="card">
    <div class="card-body" style="text-align:center;">
      <div style="font-size:1rem;font-weight:700;margin-bottom:12px;">👨‍🏫 Teacher Status</div>
      <div style="display:flex;justify-content:space-around;">
        <div><div style="font-size:1.6rem;font-weight:800;color:var(--success);"><?= $approvedTeach ?></div><div style="font-size:0.8rem;color:var(--tx3);">Approved</div></div>
        <div><div style="font-size:1.6rem;font-weight:800;color:var(--warning);"><?= $totalTeachers - $approvedTeach ?></div><div style="font-size:0.8rem;color:var(--tx3);">Pending/Rejected</div></div>
      </div>
    </div>
  </div>
</div>
<style>@media(max-width:600px){.two-col{grid-template-columns:1fr!important;}}</style>

<!-- Recent Registrations -->
<div class="card">
  <div class="card-header">
    <h3>🆕 Recent Registrations</h3>
    <a href="<?= BASE_URL ?>/presentation/admin/approvals.php" class="btn btn-warning btn-sm">
      Pending (<?= $pendingCount ?>) →
    </a>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Unique ID</th><th>Name</th><th>Role</th><th>Email</th><th>Status</th><th>Registered</th></tr></thead>
      <tbody>
        <?php while ($u = $recent->fetch_assoc()): ?>
        <tr>
          <td><code><?= htmlspecialchars($u['unique_id']) ?></code></td>
          <td><?= htmlspecialchars($u['name']) ?></td>
          <td><span class="badge badge-primary"><?= ucfirst($u['role']) ?></span></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td>
            <span class="badge badge-<?=
              $u['is_approved']==='approved' ? 'success' :
              ($u['is_approved']==='rejected' ? 'danger' : 'warning')
            ?>">
              <?= ucfirst($u['is_approved']) ?>
            </span>
          </td>
          <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php renderFooter(); ?>
