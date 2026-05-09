<?php
require_once __DIR__ . '/../../application/config.php';
requireRole('admin');
require_once __DIR__ . '/../layout.php';

$db   = getDB();
$mats = $db->query("
    SELECT m.*, u.name as uploader, u.role, u.unique_id
    FROM materials m JOIN users u ON m.user_id=u.user_id
    ORDER BY m.upload_date DESC
");

renderHead('All Materials');
renderSidebar('materials');
renderFlash();
?>

<div class="page-header">
  <h1>All Materials</h1>
  <p>Monitor and moderate uploaded content</p>
</div>

<div style="max-width:350px;margin-bottom:20px">
  <input type="text" id="tableSearch" class="form-control" placeholder="🔍 Search materials...">
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>Title</th><th>Type</th><th>Uploaded By</th><th>Role</th><th>Date</th><th>Actions</th></tr></thead>
      <tbody id="matTable">
      <?php
      $icons=['pdf'=>'📄','video'=>'🎬','image'=>'🖼️','link'=>'🔗'];
      if($mats->num_rows===0): ?>
        <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--tx3)">No materials yet</td></tr>
      <?php else: while($m=$mats->fetch_assoc()):
        $serveUrl = $m['file_path'] ? BASE_URL.'/uploads/serve.php?f='.urlencode($m['file_path']).'&download=0' : null;
      ?>
      <tr class="sr">
        <td><strong style="color:var(--tx)"><?=htmlspecialchars($m['title'])?></strong></td>
        <td><?=($icons[$m['file_type']]??'📁').' '.ucfirst($m['file_type'])?></td>
        <td><?=htmlspecialchars($m['uploader'])?><br>
            <small style="color:var(--tx3)"><?=htmlspecialchars($m['unique_id'])?></small></td>
        <td><span class="badge badge-primary"><?=ucfirst($m['role'])?></span></td>
        <td><?=date('d M Y',strtotime($m['upload_date']))?></td>
        <td style="white-space:nowrap">
          <?php if($m['file_type']==='link'): ?>
            <a href="<?=htmlspecialchars($m['external_link'])?>" target="_blank" rel="noopener" class="btn btn-primary btn-sm">🔗 Open</a>
          <?php elseif($m['file_path']): ?>
            <a href="<?=$serveUrl?>" target="_blank" class="btn btn-primary btn-sm">👁️ View</a>
            <a href="<?=$serveUrl?>&download=1" class="btn btn-ghost btn-sm">⬇️</a>
          <?php endif; ?>
          <a href="<?=BASE_URL?>/application/materials.php?action=delete&id=<?=$m['material_id']?>"
             class="btn btn-danger btn-sm" onclick="return confirm('Delete this material?')">🗑️</a>
        </td>
      </tr>
      <?php endwhile; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
document.getElementById('tableSearch').addEventListener('input', function(){
  const q = this.value.toLowerCase();
  document.querySelectorAll('#matTable .sr').forEach(r => {
    r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
});
</script>
<?php renderFooter(); ?>
