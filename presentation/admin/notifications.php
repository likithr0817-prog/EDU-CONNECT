<?php
require_once __DIR__ . '/../../application/config.php';
requireRole('admin');
require_once __DIR__ . '/../layout.php';

$db  = getDB();
$uid = $_SESSION['user_id'];

$notifs = $db->query("
    SELECT n.*, u.name AS sender_name,
           (SELECT COUNT(*) FROM notification_reads WHERE notification_id=n.notification_id) AS read_count
    FROM notifications n JOIN users u ON n.sent_by=u.user_id
    ORDER BY n.created_at DESC LIMIT 50
")->fetch_all(MYSQLI_ASSOC);

renderHead('Notifications');
renderSidebar('notifications');
renderFlash();
?>

<div class="page-header">
  <h1>◉ Notifications</h1>
  <p>Send and manage platform notifications</p>
</div>

<div style="display:grid;grid-template-columns:400px 1fr;gap:24px;align-items:start;" class="an-layout">
  <!-- Send form -->
  <div class="card">
    <div class="card-header"><h3>+ Send Notification</h3></div>
    <div class="card-body">
      <form method="POST" action="<?=BASE_URL?>/application/notification_handler.php">
        <input type="hidden" name="action" value="send">
        <div class="form-group"><label>Title</label>
          <input type="text" name="title" class="form-control" placeholder="Notification title" required maxlength="200">
        </div>
        <div class="form-group"><label>Message</label>
          <textarea name="message" class="form-control" rows="5" placeholder="Your message…" required maxlength="2000"></textarea>
        </div>
        <div class="form-group"><label>Send To</label>
          <select name="target_role" class="form-control">
            <option value="all">Everyone</option>
            <option value="student">Students</option>
            <option value="teacher">Teachers</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary btn-block">◉ Send</button>
      </form>
    </div>
  </div>

  <!-- History -->
  <div>
    <div style="font-size:0.75rem;color:var(--tx3);text-transform:uppercase;letter-spacing:2px;font-weight:700;margin-bottom:14px;">All Notifications</div>
    <?php if (empty($notifs)): ?>
    <div class="empty-state"><div class="empty-icon">◉</div><h3>None yet</h3></div>
    <?php else: ?>
    <div class="notif-list">
    <?php foreach ($notifs as $n): ?>
    <div class="notif-item">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
        <h4><?=htmlspecialchars($n['title'])?></h4>
        <div style="display:flex;gap:6px;">
          <span class="badge badge-<?=$n['target_role']==='all'?'warning':($n['target_role']==='student'?'primary':'info')?>"><?=$n['target_role']?></span>
          <a href="<?=BASE_URL?>/application/notification_handler.php?action=delete&id=<?=$n['notification_id']?>"
             class="btn btn-sm btn-danger" style="padding:2px 9px;font-size:0.7rem;" onclick="return confirm('Delete?')">✕</a>
        </div>
      </div>
      <p><?=htmlspecialchars(substr($n['message'],0,200))?><?=strlen($n['message'])>200?'…':''?></p>
      <div class="notif-meta">
        From <?=htmlspecialchars($n['sender_name'])?>
        · <?=date('d M Y H:i',strtotime($n['created_at']))?>
        · <span style="color:var(--c);">◎ <?=$n['read_count']?> read</span>
      </div>
    </div>
    <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<style>@media(max-width:800px){.an-layout{grid-template-columns:1fr!important;}}</style>

<?php renderFooter(); ?>
