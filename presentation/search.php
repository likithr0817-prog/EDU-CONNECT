<?php
// ============================================================
// Presentation Layer: User Search Page
// Accessible by: student, teacher (not admin profile search)
// File: presentation/search.php
// ============================================================

require_once __DIR__ . '/../application/config.php';
requireLogin();
require_once __DIR__ . '/layout.php';

$db      = getDB();
$role    = $_SESSION['role'];
$query   = trim($_GET['q'] ?? '');
$filter  = $_GET['filter'] ?? 'all';   // all | student | teacher
$results = [];

if (!empty($query) || $filter !== 'all') {
    $sql = "SELECT u.user_id, u.unique_id, u.name, u.role, u.college_name, u.created_at,
                   s.class_name, t.subject
            FROM users u
            LEFT JOIN students s ON u.user_id = s.user_id
            LEFT JOIN teachers t ON u.user_id = t.user_id
            WHERE u.role != 'admin' AND u.is_approved = 'approved'";

    $params = [];
    $types  = '';

    if (!empty($query)) {
        $sql   .= " AND (u.name LIKE ? OR u.unique_id LIKE ? OR u.college_name LIKE ?)";
        $like   = '%' . $query . '%';
        $params = [$like, $like, $like];
        $types  = 'sss';
    }

    if ($filter !== 'all') {
        $sql   .= " AND u.role = ?";
        $params[] = $filter;
        $types   .= 's';
    }

    $sql .= " ORDER BY u.name ASC LIMIT 50";

    if (!empty($params)) {
        $stmt = $db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
    } else {
        $res = $db->query($sql);
    }

    while ($row = $res->fetch_assoc()) {
        $results[] = $row;
    }
}

// Stats for header display
$totalStudents = $db->query("SELECT COUNT(*) as c FROM users WHERE role='student' AND is_approved='approved'")->fetch_assoc()['c'];
$totalTeachers = $db->query("SELECT COUNT(*) as c FROM users WHERE role='teacher' AND is_approved='approved'")->fetch_assoc()['c'];

renderHead('Search Users');
renderSidebar('search');
renderFlash();
?>

<div class="page-header">
    <h1>🔍 Search Users</h1>
    <p>Find students and teachers on Edu-Connect</p>
</div>

<!-- Stats Bar -->
<div class="stats-grid" style="margin-bottom:24px;">
    <div class="stat-card blue">
        <div class="stat-icon">🎓</div>
        <div class="stat-info">
            <div class="stat-number"><?= $totalStudents ?></div>
            <div class="stat-label">Active Students</div>
        </div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon">👨‍🏫</div>
        <div class="stat-info">
            <div class="stat-number"><?= $totalTeachers ?></div>
            <div class="stat-label">Active Teachers</div>
        </div>
    </div>
</div>

<!-- Search Form -->
<div class="card" style="margin-bottom:24px;">
    <div class="card-body">
        <form method="GET" action="" id="search-form">
            <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:220px;">
                    <label style="font-size:0.85rem;font-weight:600;color:var(--tx);display:block;margin-bottom:6px;">
                        Search by name, ID, or college
                    </label>
                    <input type="text" name="q" class="form-control"
                           value="<?= htmlspecialchars($query) ?>"
                           placeholder="e.g. John, STU-000001, ABC University"
                           autofocus>
                </div>
                <div style="min-width:160px;">
                    <label style="font-size:0.85rem;font-weight:600;color:var(--tx);display:block;margin-bottom:6px;">Filter by role</label>
                    <select name="filter" class="form-control" onchange="this.form.submit()">
                        <option value="all"     <?= $filter==='all'     ? 'selected':'' ?>>👥 All Users</option>
                        <option value="student" <?= $filter==='student' ? 'selected':'' ?>>🎓 Students</option>
                        <option value="teacher" <?= $filter==='teacher' ? 'selected':'' ?>>👨‍🏫 Teachers</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary" style="margin-top:2px;">🔍 Search</button>
                    <?php if (!empty($query) || $filter !== 'all'): ?>
                        <a href="?filter=all" class="btn btn-outline" style="margin-left:6px;">✕ Clear</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Results -->
<?php if (empty($query) && $filter === 'all'): ?>
    <!-- Show all users by default -->
    <?php
    $all = $db->query("SELECT u.user_id, u.unique_id, u.name, u.role, u.college_name, u.created_at,
                              s.class_name, t.subject
                       FROM users u
                       LEFT JOIN students s ON u.user_id = s.user_id
                       LEFT JOIN teachers t ON u.user_id = t.user_id
                       WHERE u.role != 'admin' AND u.is_approved = 'approved'
                       ORDER BY u.name ASC LIMIT 50");
    $results = [];
    while ($r = $all->fetch_assoc()) $results[] = $r;
    ?>
<?php endif; ?>

<?php if (!empty($results)): ?>
<div style="margin-bottom:12px;font-size:0.88rem;color:var(--tx3);">
    Showing <strong><?= count($results) ?></strong> result<?= count($results) !== 1 ? 's' : '' ?>
    <?= !empty($query) ? ' for "<strong>' . htmlspecialchars($query) . '</strong>"' : '' ?>
</div>

<div class="user-search-grid">
    <?php foreach ($results as $u):
        $init    = strtoupper(substr($u['name'], 0, 1));
        $isTeach = $u['role'] === 'teacher';
        $extra   = $isTeach ? ($u['subject'] ?? '') : ($u['class_name'] ?? '');
        $extraLabel = $isTeach ? 'Subject' : 'Class';
        $avatarColor = $isTeach ? 'linear-gradient(135deg,#06d6a0,#4cc9f0)' : 'linear-gradient(135deg,#4361ee,#4cc9f0)';
    ?>
    <div class="user-card" onclick="location.href='<?= BASE_URL ?>/presentation/view_profile.php?id=<?= $u['user_id'] ?>'">
        <div class="user-card-avatar" style="background:<?= $avatarColor ?>">
            <?= $init ?>
        </div>
        <div class="user-card-info">
            <div class="user-card-name"><?= htmlspecialchars($u['name']) ?></div>
            <div class="user-card-uid"><?= htmlspecialchars($u['unique_id']) ?></div>
            <div style="margin-top:6px;">
                <span class="badge badge-<?= $isTeach ? 'success' : 'primary' ?>">
                    <?= $isTeach ? '👨‍🏫 Teacher' : '🎓 Student' ?>
                </span>
            </div>
            <?php if ($extra): ?>
            <div class="user-card-meta">📚 <?= $extraLabel ?>: <?= htmlspecialchars($extra) ?></div>
            <?php endif; ?>
            <?php if ($u['college_name']): ?>
            <div class="user-card-meta">🏫 <?= htmlspecialchars($u['college_name']) ?></div>
            <?php endif; ?>
        </div>
        <div class="user-card-action">
            <a href="<?= BASE_URL ?>/presentation/view_profile.php?id=<?= $u['user_id'] ?>"
               class="btn btn-outline btn-sm" onclick="event.stopPropagation()">View Profile →</a>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php elseif (!empty($query)): ?>
<div class="empty-state">
    <div class="empty-icon">🔍</div>
    <h3>No results found</h3>
    <p>No users match "<strong><?= htmlspecialchars($query) ?></strong>". Try a different name or ID.</p>
</div>
<?php endif; ?>

<style>
.user-search-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 16px;
}
.user-card {
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    padding: 20px;
    display: flex;
    gap: 16px;
    align-items: flex-start;
    cursor: pointer;
    transition: var(--transition);
    border: 2px solid transparent;
}
.user-card:hover {
    border-color: var(--vb);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(67,97,238,0.15);
}
.user-card-avatar {
    width: 52px; height: 52px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: white; font-size: 1.4rem; font-weight: 700;
    flex-shrink: 0;
}
.user-card-info { flex: 1; min-width: 0; }
.user-card-name { font-weight: 700; font-size: 1rem; color: var(--tx); }
.user-card-uid  { font-size: 0.78rem; color: var(--vb); font-weight: 600; margin-top:2px; }
.user-card-meta { font-size: 0.8rem; color: var(--tx3); margin-top: 4px; }
.user-card-action { flex-shrink: 0; align-self: center; }
@media(max-width:500px){ .user-card-action { display:none; } }
</style>

<?php renderFooter(); ?>
