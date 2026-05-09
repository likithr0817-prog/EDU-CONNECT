<?php
require_once __DIR__ . '/../../application/config.php';
requireRole('student');
require_once __DIR__ . '/../layout.php';

$db   = getDB();
$uid  = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch notifications for this role
$notifs = $db->query("
    SELECT n.*, u.name AS sender_name, u.role AS sender_role_val,
           (SELECT COUNT(*) FROM notification_reads WHERE notification_id=n.notification_id AND user_id=$uid) AS is_read
    FROM notifications n
    JOIN users u ON n.sent_by = u.user_id
    WHERE n.target_role='all' OR n.target_role='$role'
    ORDER BY n.created_at DESC LIMIT 50
")->fetch_all(MYSQLI_ASSOC);

$unread = array_filter($notifs, fn($n) => !$n['is_read']);

renderHead('Notifications');
renderSidebar('notifications');
renderFlash();
?>

<div class="page-header" style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;">
  <div>
    <h1>◉ Notifications</h1>
    <p><?=count($unread)?> unread · <?=count($notifs)?> total</p>
  </div>
  <?php if (count($unread)): ?>
  <button class="btn btn-ghost btn-sm" onclick="markAllRead()">✓ Mark all read</button>
  <?php endif; ?>
</div>

<?php if (empty($notifs)): ?>
<div class="empty-state" style="margin-top:40px;">
  <div class="empty-icon">◉</div><h3>All clear!</h3><p>No notifications yet.</p>
</div>
<?php else: ?>
<div class="notif-list" id="notifList">
<?php foreach ($notifs as $n):
  $isRead   = $n['is_read'];
  $senderBadge = $n['sender_role_val']==='teacher' ? 'badge-info' : 'badge-primary';
?>
<div class="notif-item<?=!$isRead?' unread':''?>" id="ni-<?=$n['notification_id']?>"
     onclick="markOne(<?=$n['notification_id']?>,this)" style="cursor:pointer;">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;gap:10px;">
    <h4><?=htmlspecialchars($n['title'])?></h4>
    <div style="display:flex;gap:6px;align-items:center;flex-shrink:0;">
      <?php if (!$isRead): ?><span style="width:8px;height:8px;border-radius:50%;background:var(--vb);flex-shrink:0;"></span><?php endif; ?>
      <span class="badge <?=$senderBadge?>"><?=$n['sender_role_val']?></span>
      <span class="badge badge-<?=$n['target_role']==='all'?'warning':'success'?>"><?=$n['target_role']?></span>
    </div>
  </div>
  <p><?=htmlspecialchars($n['message'])?></p>
  <div class="notif-meta">
    From <strong><?=htmlspecialchars($n['sender_name'])?></strong>
    · <?=date('d M Y H:i',strtotime($n['created_at']))?>
    <?php if (!$isRead): ?><span style="color:var(--vb);">· tap to mark read</span><?php endif; ?>
  </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<script>
const base = window.__baseUrl;
function markOne(id, el) {
  fetch(base+'/application/notification_handler.php?action=mark_one&id='+id,{method:'POST'})
    .then(r=>r.json()).then(d=>{
      if(d.ok){ el.classList.remove('unread'); el.querySelector('span[style*="border-radius:50%"]')?.remove(); }
    });
}
function markAllRead() {
  fetch(base+'/application/notification_handler.php?action=mark_read',{method:'POST'})
    .then(r=>r.json()).then(d=>{ if(d.ok) location.reload(); });
}
</script>

<?php renderFooter(); ?>
