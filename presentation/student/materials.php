<?php
require_once __DIR__ . '/../../application/config.php';
requireRole('student');
require_once __DIR__ . '/../layout.php';

$db      = getDB();
$user_id = $_SESSION['user_id'];
$filter  = $_GET['filter'] ?? 'all';

$sql = "SELECT m.*, u.name as uploader, u.role FROM materials m JOIN users u ON m.user_id=u.user_id";
if ($filter !== 'all') {
    $stmt = $db->prepare($sql." WHERE m.file_type=? ORDER BY m.upload_date DESC");
    $stmt->bind_param("s",$filter); $stmt->execute();
    $mats = $stmt->get_result();
} else {
    $mats = $db->query($sql." ORDER BY m.upload_date DESC");
}

renderHead('Study Materials');
renderSidebar('materials');
renderFlash();
?>

<div class="page-header">
  <h1>Study Materials</h1>
  <p>Browse, upload and download learning resources</p>
</div>

<!-- Upload Form -->
<div class="card" style="margin-bottom:24px">
  <div class="card-header" style="background:linear-gradient(90deg,rgba(110,86,255,.1),transparent)">
    <h3>⬆️ Upload Material</h3>
  </div>
  <div class="card-body">
    <form method="POST" action="<?=BASE_URL?>/application/materials.php" enctype="multipart/form-data">
      <input type="hidden" name="action" value="upload">
      <div class="form-row">
        <div class="form-group">
          <label>Title *</label>
          <input type="text" class="form-control" name="title" placeholder="Material title" required>
        </div>
        <div class="form-group">
          <label>File Type *</label>
          <select class="form-control" name="file_type" id="fileType" onchange="switchType(this.value)">
            <option value="pdf">📄 PDF</option>
            <option value="video">🎬 Video</option>
            <option value="image">🖼️ Image</option>
            <option value="link">🔗 External Link</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label>Description</label>
        <textarea class="form-control" name="description" rows="2" placeholder="Brief description..."></textarea>
      </div>
      <div id="fileGroup" class="form-group">
        <label>File * <span id="acceptHint" style="color:var(--tx3);font-size:.75rem;text-transform:none;letter-spacing:0;font-weight:400">— PDF up to 50MB</span></label>
        <input type="file" class="form-control" name="file" id="fileInput" accept=".pdf" onchange="showFileName(this)">
        <div id="fileNameDisplay" style="display:none;font-size:.78rem;color:var(--c);margin-top:5px;font-weight:600"></div>
      </div>
      <div id="linkGroup" class="form-group" style="display:none">
        <label>External URL *</label>
        <input type="url" class="form-control" name="external_link" placeholder="https://...">
      </div>
      <button type="submit" class="btn btn-primary">⬆️ &nbsp;Upload</button>
    </form>
  </div>
</div>

<!-- Filter Tabs -->
<div class="tab-bar" style="margin-bottom:20px">
  <?php foreach(['all'=>'All','pdf'=>'📄 PDFs','video'=>'🎬 Videos','image'=>'🖼️ Images','link'=>'🔗 Links'] as $k=>$v): ?>
  <a href="?filter=<?=$k?>" class="tab-btn<?=$filter===$k?' active':''?>"><?=$v?></a>
  <?php endforeach; ?>
</div>

<?php if ($mats->num_rows===0): ?>
<div class="empty-state">
  <div class="empty-icon">📭</div>
  <h3>No materials found</h3>
  <p>Be the first to upload a resource!</p>
</div>
<?php else: ?>
<div class="materials-grid">
  <?php while($m=$mats->fetch_assoc()):
    $icons=['pdf'=>'📄','video'=>'🎬','image'=>'🖼️','link'=>'🔗'];
    $icon=$icons[$m['file_type']]??'📁';
    $fileUrl = $m['file_path'] ? BASE_URL.'/uploads/serve.php?f='.urlencode($m['file_path']).'&download=0' : null;
    $dlUrl   = $m['file_path'] ? BASE_URL.'/uploads/serve.php?f='.urlencode($m['file_path']).'&download=1' : null;
  ?>
  <div class="material-card">
    <div class="mat-header">
      <div class="mat-type-badge"><?=$icon?> <?=strtoupper($m['file_type'])?></div>
      <div class="mat-title"><?=htmlspecialchars($m['title'])?></div>
    </div>
    <div class="mat-body">
      <p class="mat-desc"><?=htmlspecialchars(substr($m['description']??'No description',0,120))?></p>
      <p class="mat-meta">👤 <?=htmlspecialchars($m['uploader'])?> (<?=ucfirst($m['role'])?>)</p>
      <p class="mat-meta">📅 <?=date('d M Y',strtotime($m['upload_date']))?></p>
    </div>
    <div class="mat-actions">
      <?php if($m['file_type']==='link'): ?>
        <a href="<?=htmlspecialchars($m['external_link'])?>" target="_blank" rel="noopener" class="btn btn-primary btn-sm">🔗 Open Link</a>
      <?php elseif($m['file_path']): ?>
        <a href="<?=BASE_URL.'/uploads/serve.php?f='.urlencode($m['file_path']).'&download=0'?>" target="_blank" class="btn btn-primary btn-sm">👁️ View</a>
        <a href="<?=BASE_URL.'/uploads/serve.php?f='.urlencode($m['file_path']).'&download=1'?>" class="btn btn-outline btn-sm">⬇️ Download</a>
      <?php endif; ?>
      <?php if($m['user_id']==$user_id): ?>
        <a href="<?=BASE_URL?>/application/materials.php?action=delete&id=<?=$m['material_id']?>"
           class="btn btn-danger btn-sm" onclick="return confirm('Delete this material?')">🗑️</a>
      <?php endif; ?>
    </div>
  </div>
  <?php endwhile; ?>
</div>
<?php endif; ?>

<script>
const typeConfig = {
  pdf:   {accept:'.pdf',hint:'PDF up to 50MB'},
  video: {accept:'.mp4,.avi,.mov,.mkv,.webm',hint:'MP4, AVI, MOV, MKV, WEBM · 50MB max'},
  image: {accept:'.jpg,.jpeg,.png,.gif,.webp',hint:'JPG, PNG, GIF, WEBP · 50MB max'},
  link:  {accept:'',hint:''}
};
function switchType(t){
  document.getElementById('fileGroup').style.display = t==='link'?'none':'';
  document.getElementById('linkGroup').style.display = t==='link'?'':'none';
  if(t!=='link'){
    document.getElementById('fileInput').accept = typeConfig[t].accept;
    document.getElementById('acceptHint').textContent = '— '+typeConfig[t].hint;
    document.getElementById('fileNameDisplay').style.display='none';
  }
}
function showFileName(input){
  const d=document.getElementById('fileNameDisplay');
  if(input.files&&input.files[0]){d.textContent='✓ '+input.files[0].name;d.style.display='block';}
}
</script>
<?php renderFooter(); ?>
