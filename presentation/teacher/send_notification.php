<?php
require_once __DIR__ . '/../../application/config.php';
requireRole('teacher');
require_once __DIR__ . '/../layout.php';

$db  = getDB();
$uid = $_SESSION['user_id'];

// Notifications sent by this teacher
$sent = $db->query("
    SELECT n.*, (SELECT COUNT(*) FROM notification_reads WHERE notification_id=n.notification_id) AS read_count,
           (SELECT COUNT(*) FROM users WHERE role='student' AND is_approved='approved') AS total_students
    FROM notifications n WHERE n.sent_by=$uid AND n.sender_role='teacher'
    ORDER BY n.created_at DESC LIMIT 30
")->fetch_all(MYSQLI_ASSOC);

renderHead('Send Notification');
renderSidebar('send_notif');
renderFlash();
?>

<div class="page-header">
  <h1>◈ Send Notification</h1>
  <p>Broadcast messages directly to your students</p>
</div>

<div style="display:grid;grid-template-columns:420px 1fr;gap:24px;align-items:start;" class="sn-layout">

  <!-- Form -->
  <div class="card">
    <div class="card-header"><h3>+ New Notification</h3></div>
    <div class="card-body">
      <form method="POST" action="<?=BASE_URL?>/application/notification_handler.php">
        <input type="hidden" name="action" value="send">
        <div class="form-group">
          <label>Title</label>
          <input type="text" name="title" class="form-control" placeholder="Notification title…" required maxlength="200">
        </div>
        <div class="form-group">
          <label>Message</label>
          <textarea name="message" class="form-control" rows="5" placeholder="Your message to students…" required maxlength="2000"></textarea>
        </div>
        <div class="form-group">
          <label>Send To</label>
          <select name="target_role" class="form-control">
            <option value="student">Students only</option>
            <option value="all">Everyone</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary btn-block">◉ Send Notification</button>
      </form>
    </div>
  </div>

  <!-- Sent history -->
  <div>
    <div style="font-size:0.75rem;color:var(--tx3);text-transform:uppercase;letter-spacing:2px;font-weight:700;margin-bottom:14px;">
      Sent History
    </div>
    <?php if (empty($sent)): ?>
    <div class="empty-state"><div class="empty-icon">◈</div><h3>No notifications sent yet</h3></div>
    <?php else: ?>
    <div class="notif-list">
    <?php foreach ($sent as $n): ?>
    <div class="notif-item">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
        <h4><?=htmlspecialchars($n['title'])?></h4>
        <div style="display:flex;gap:6px;">
          <span class="badge badge-<?=$n['target_role']==='all'?'info':'primary'?>"><?=$n['target_role']?></span>
          <a href="<?=BASE_URL?>/application/notification_handler.php?action=delete&id=<?=$n['notification_id']?>"
             class="btn btn-sm btn-danger" style="padding:2px 9px;font-size:0.7rem;" onclick="return confirm('Delete?')">✕</a>
        </div>
      </div>
      <p><?=htmlspecialchars(substr($n['message'],0,200))?><?=strlen($n['message'])>200?'…':''?></p>
      <div class="notif-meta">
        <?=date('d M Y H:i',strtotime($n['created_at']))?>
        · <span style="color:var(--c);">◎ <?=$n['read_count']?> read</span>
      </div>
    </div>
    <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<style>@media(max-width:800px){.sn-layout{grid-template-columns:1fr!important;}}</style>

<?php renderFooter(); ?>
