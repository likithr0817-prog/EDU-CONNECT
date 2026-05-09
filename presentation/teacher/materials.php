<?php
require_once __DIR__ . '/../../application/config.php';
requireRole('teacher');
require_once __DIR__ . '/../layout.php';

$db      = getDB();
$user_id = $_SESSION['user_id'];
$filter  = $_GET['filter'] ?? 'all';

if ($filter !== 'all') {
    $stmt = $db->prepare("SELECT * FROM materials WHERE user_id=? AND file_type=? ORDER BY upload_date DESC");
    $stmt->bind_param("is",$user_id,$filter);
} else {
    $stmt = $db->prepare("SELECT * FROM materials WHERE user_id=? ORDER BY upload_date DESC");
    $stmt->bind_param("i",$user_id);
}
$stmt->execute();
$mats = $stmt->get_result();

renderHead('My Materials');
renderSidebar('materials');
renderFlash();
?>

<div class="page-header">
  <h1>My Materials</h1>
  <p>Manage your uploaded resources</p>
</div>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px">
  <div class="tab-bar" style="border:none;margin:0">
    <?php foreach(['all'=>'All','pdf'=>'📄 PDFs','video'=>'🎬 Videos','image'=>'🖼️ Images','link'=>'🔗 Links'] as $k=>$v): ?>
    <a href="?filter=<?=$k?>" class="tab-btn<?=$filter===$k?' active':''?>"><?=$v?></a>
    <?php endforeach; ?>
  </div>
  <a href="<?=BASE_URL?>/presentation/teacher/upload.php" class="btn btn-primary btn-sm">⬆️ Upload New</a>
</div>

<?php if($mats->num_rows===0): ?>
<div class="empty-state">
  <div class="empty-icon">📭</div>
  <h3>No materials uploaded yet</h3>
  <p><a href="<?=BASE_URL?>/presentation/teacher/upload.php">Upload your first material →</a></p>
</div>
<?php else: ?>
<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>Title</th><th>Type</th><th>Description</th><th>Uploaded</th><th>Actions</th></tr></thead>
      <tbody>
      <?php
      $icons=['pdf'=>'📄','video'=>'🎬','image'=>'🖼️','link'=>'🔗'];
      while($m=$mats->fetch_assoc()):
        $serveUrl = $m['file_path'] ? BASE_URL.'/uploads/serve.php?f='.urlencode($m['file_path']).'&download=0' : null;
        $dlUrl    = $m['file_path'] ? BASE_URL.'/uploads/serve.php?f='.urlencode($m['file_path']).'&download=1' : null;
      ?>
      <tr>
        <td><strong style="color:var(--tx)"><?=htmlspecialchars($m['title'])?></strong></td>
        <td><?=($icons[$m['file_type']]??'📁').' '.ucfirst($m['file_type'])?></td>
        <td style="max-width:200px;color:var(--tx3)"><?=htmlspecialchars(substr($m['description']??'—',0,80))?></td>
        <td><?=date('d M Y',strtotime($m['upload_date']))?></td>
        <td style="white-space:nowrap">
          <?php if($m['file_type']==='link'): ?>
            <a href="<?=htmlspecialchars($m['external_link'])?>" target="_blank" rel="noopener" class="btn btn-primary btn-sm">🔗 Open</a>
          <?php elseif($m['file_path']): ?>
            <a href="<?=$serveUrl?>" target="_blank" class="btn btn-primary btn-sm">👁️ View</a>
            <a href="<?=$dlUrl?>" class="btn btn-ghost btn-sm">⬇️</a>
          <?php endif; ?>
          <a href="<?=BASE_URL?>/application/materials.php?action=delete&id=<?=$m['material_id']?>"
             class="btn btn-danger btn-sm" onclick="return confirm('Delete this material?')">🗑️</a>
        </td>
      </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
<?php renderFooter(); ?>
