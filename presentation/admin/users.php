<?php
require_once __DIR__ . '/../../application/config.php';
requireRole('admin');
require_once __DIR__ . '/../layout.php';

$db    = getDB();
$role  = $_GET['role'] ?? 'all';

$sql  = "SELECT u.*, s.class_name, t.subject FROM users u
         LEFT JOIN students s ON u.user_id = s.user_id
         LEFT JOIN teachers t ON u.user_id = t.user_id
         WHERE u.role != 'admin'";
if ($role !== 'all') {
    $stmt = $db->prepare($sql . " AND u.role = ? ORDER BY u.created_at DESC");
    $stmt->bind_param("s", $role); $stmt->execute();
    $users = $stmt->get_result();
} else {
    $users = $db->query($sql . " ORDER BY u.created_at DESC");
}

renderHead('All Users');
renderSidebar('users');
renderFlash();
?>

<div class="page-header">
  <h1>👥 All Users</h1>
  <p>Manage registered students and teachers</p>
</div>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
  <div style="display:flex;gap:8px;">
    <a href="?role=all"     class="btn <?= $role==='all'     ? 'btn-primary':'btn-outline' ?> btn-sm">All</a>
    <a href="?role=student" class="btn <?= $role==='student' ? 'btn-primary':'btn-outline' ?> btn-sm">🎓 Students</a>
    <a href="?role=teacher" class="btn <?= $role==='teacher' ? 'btn-primary':'btn-outline' ?> btn-sm">👨‍🏫 Teachers</a>
  </div>
  <div class="search-bar" style="margin:0;width:250px;">
    <input type="text" id="table-search" placeholder="🔍 Search users...">
  </div>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>Unique ID</th><th>Name</th><th>Role</th><th>Email</th><th>College</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php if ($users->num_rows === 0): ?>
          <tr><td colspan="7" style="text-align:center;color:var(--tx3);padding:30px;">No users found</td></tr>
        <?php else: ?>
        <?php while ($u = $users->fetch_assoc()): ?>
        <tr class="searchable-row">
          <td><code><?= htmlspecialchars($u['unique_id']) ?></code></td>
          <td><?= htmlspecialchars($u['name']) ?></td>
          <td><span class="badge badge-primary"><?= ucfirst($u['role']) ?></span></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><?= htmlspecialchars($u['college_name'] ?? '-') ?></td>
          <td>
            <span class="badge badge-<?=
              $u['is_approved']==='approved' ? 'success' :
              ($u['is_approved']==='rejected' ? 'danger' : 'warning')
            ?>">
              <?= ucfirst($u['is_approved']) ?>
            </span>
          </td>
          <td style="white-space:nowrap;">
            <?php if ($u['is_approved'] === 'pending'): ?>
              <a href="<?= BASE_URL ?>/application/admin_actions.php?action=approve&id=<?= $u['user_id'] ?>"
                 class="btn btn-success btn-sm">✅</a>
              <a href="<?= BASE_URL ?>/application/admin_actions.php?action=reject&id=<?= $u['user_id'] ?>"
                 class="btn btn-warning btn-sm">❌</a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/application/admin_actions.php?action=delete_user&id=<?= $u['user_id'] ?>"
               class="btn btn-danger btn-sm delete-confirm" title="Delete user">🗑️</a>
          </td>
        </tr>
        <?php endwhile; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php renderFooter(); ?>
