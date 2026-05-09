<?php
function renderHead($title = 'Edu-Connect') {
    $base = BASE_URL;
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>$title | Edu-Connect</title>
<link rel="stylesheet" href="{$base}/presentation/css/style.css">
<script>window.__baseUrl='{$base}';</script>
</head>
<body>
HTML;
}

function renderSidebar($activeMenu = '') {
    $role = $_SESSION['role'];
    $name = $_SESSION['name'];
    $uid  = $_SESSION['unique_id'];
    $base = BASE_URL;
    $init = strtoupper(substr($name, 0, 1));

    // Load profile photo from DB for sidebar avatar
    $db       = getDB();
    $uid_int  = (int)$_SESSION['user_id'];
    $photoRow = $db->query("SELECT profile_photo FROM users WHERE user_id=$uid_int")->fetch_assoc();
    $photo    = $photoRow['profile_photo'] ?? null;
    $hasPhoto = $photo && file_exists(BASE_PATH . '/' . $photo);

    $navItems = [];
    if ($role === 'student') {
        $navItems = [
            ['url'=>"$base/presentation/student/dashboard.php",    'icon'=>'⬡', 'label'=>'Dashboard',    'key'=>'dashboard'],
            ['url'=>"$base/presentation/student/materials.php",    'icon'=>'◈', 'label'=>'Materials',    'key'=>'materials'],
            ['url'=>"$base/presentation/videos.php",               'icon'=>'▶', 'label'=>'Study Videos', 'key'=>'videos'],
            ['url'=>"$base/presentation/search.php",               'icon'=>'◎', 'label'=>'Find People',  'key'=>'search'],
            ['url'=>"$base/presentation/student/notifications.php",'icon'=>'◉', 'label'=>'Notifications','key'=>'notifications'],
            ['url'=>"$base/presentation/feedback.php",             'icon'=>'◇', 'label'=>'Feedback',     'key'=>'feedback'],
            ['url'=>"$base/presentation/student/profile.php",      'icon'=>'◐', 'label'=>'My Profile',   'key'=>'profile'],
        ];
    } elseif ($role === 'teacher') {
        $navItems = [
            ['url'=>"$base/presentation/teacher/dashboard.php",       'icon'=>'⬡', 'label'=>'Dashboard',    'key'=>'dashboard'],
            ['url'=>"$base/presentation/teacher/materials.php",       'icon'=>'◈', 'label'=>'My Materials', 'key'=>'materials'],
            ['url'=>"$base/presentation/teacher/upload.php",          'icon'=>'⬆', 'label'=>'Upload',       'key'=>'upload'],
            ['url'=>"$base/presentation/videos.php",                  'icon'=>'▶', 'label'=>'Study Videos', 'key'=>'videos'],
            ['url'=>"$base/presentation/search.php",                  'icon'=>'◎', 'label'=>'Find People',  'key'=>'search'],
            ['url'=>"$base/presentation/teacher/notifications.php",   'icon'=>'◉', 'label'=>'Notifications','key'=>'notifications'],
            ['url'=>"$base/presentation/teacher/send_notification.php",'icon'=>'◈','label'=>'Send Notif',  'key'=>'send_notif'],
            ['url'=>"$base/presentation/feedback.php",                'icon'=>'◇', 'label'=>'Feedback',     'key'=>'feedback'],
            ['url'=>"$base/presentation/teacher/profile.php",         'icon'=>'◐', 'label'=>'My Profile',   'key'=>'profile'],
        ];
    } elseif ($role === 'admin') {
        $navItems = [
            ['url'=>"$base/presentation/admin/dashboard.php",    'icon'=>'⬡', 'label'=>'Dashboard',    'key'=>'dashboard'],
            ['url'=>"$base/presentation/admin/users.php",        'icon'=>'◎', 'label'=>'All Users',    'key'=>'users'],
            ['url'=>"$base/presentation/admin/approvals.php",    'icon'=>'✓', 'label'=>'Approvals',    'key'=>'approvals'],
            ['url'=>"$base/presentation/admin/materials.php",    'icon'=>'◈', 'label'=>'Materials',    'key'=>'materials'],
            ['url'=>"$base/presentation/videos.php",             'icon'=>'▶', 'label'=>'Study Videos', 'key'=>'videos'],
            ['url'=>"$base/presentation/admin/notifications.php",'icon'=>'◉', 'label'=>'Notifications','key'=>'notifications'],
            ['url'=>"$base/presentation/feedback.php",           'icon'=>'◇', 'label'=>'Feedback',     'key'=>'feedback'],
            ['url'=>"$base/presentation/admin/profile.php",      'icon'=>'◐', 'label'=>'My Profile',   'key'=>'profile'],
        ];
    }

    $unread    = getUnreadNotifications($_SESSION['user_id'], $role);
    $badgeHtml = $unread > 0 ? "<span class='notif-badge'>$unread</span>" : '';

    // Build avatar HTML — real photo if available, else initial
    if ($hasPhoto) {
        $photoUrl   = $base . '/' . htmlspecialchars($photo, ENT_QUOTES);
        $avatarHtml = "<img src='$photoUrl' alt='$name' style='width:100%;height:100%;object-fit:cover;border-radius:12px'>";
    } else {
        $avatarHtml = $init;
    }

    echo <<<HTML
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div class="layout">
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
      <h2>EduConnect</h2>
      <p>Learning Platform</p>
    </div>
    <div class="sidebar-user">
      <div class="user-avatar" style="overflow:hidden">$avatarHtml</div>
      <div class="user-info">
        <div class="user-name">$name</div>
        <div class="user-role">$role</div>
      </div>
    </div>
    <nav class="sidebar-nav">
      <div class="nav-section"><span>Navigation</span></div>
      <ul>
HTML;

    foreach ($navItems as $item) {
        $active = ($activeMenu === $item['key']) ? 'active' : '';
        $badge  = ($item['key'] === 'notifications' && $unread > 0)
                  ? "<span class='nav-badge'>$unread</span>" : '';
        echo "<li class='nav-item'><a href='{$item['url']}' class='$active'>"
           . "<span class='icon'>{$item['icon']}</span> {$item['label']}$badge</a></li>\n";
    }

    // Build topbar profile button with photo
    if ($hasPhoto) {
        $photoUrl = $base . '/' . htmlspecialchars($photo, ENT_QUOTES);
        $profileBtn = "<a href='{$base}/presentation/{$role}/profile.php' class='notif-btn' title='Profile' style='text-decoration:none;padding:4px;'>"
                    . "<img src='$photoUrl' style='width:28px;height:28px;border-radius:8px;object-fit:cover;display:block'></a>";
    } else {
        $profileBtn = "<a href='{$base}/presentation/{$role}/profile.php' class='notif-btn' title='Profile' style='text-decoration:none;'>◐</a>";
    }

    echo <<<HTML
      </ul>
    </nav>
    <div class="sidebar-footer">
      <a href="{$base}/application/auth.php?action=logout">✕ &nbsp;Sign Out</a>
    </div>
  </aside>
  <div class="main-content">
    <header class="topbar">
      <button class="menu-toggle" id="menuToggle">☰</button>
      <div class="topbar-title">Welcome back, <strong>$name</strong></div>
      <div class="topbar-right">
        <button class="notif-btn" title="Notifications" onclick="location.href='{$base}/presentation/{$role}/notifications.php'">
          ◉
          $badgeHtml
        </button>
        $profileBtn
      </div>
    </header>
    <div class="page-content">
HTML;
}

function renderFlash() {
    $flash = getFlash();
    if ($flash) {
        $type = $flash['type'] === 'success' ? 'alert-success' : 'alert-error';
        echo "<div class='alert $type'>" . htmlspecialchars($flash['message']) . "</div>";
    }
}

function renderFooter() {
    $base = BASE_URL;
    echo <<<HTML
    </div><!-- page-content -->
  </div><!-- main-content -->
</div><!-- layout -->
<script src="{$base}/presentation/js/main.js"></script>
</body>
</html>
HTML;
}
?>
