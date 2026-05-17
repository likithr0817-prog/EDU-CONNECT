<?php
require_once __DIR__ . '/../../application/config.php';
requireRole('teacher');
require_once __DIR__ . '/../layout.php';

renderHead('Upload Material');
renderSidebar('upload');
renderFlash();
?>

<div class="page-header">
  <h1>Upload Material</h1>
  <p>Share study resources with your students</p>
</div>

<div class="card" style="max-width:720px">
  <div class="card-body">
    <form method="POST" action="<?=BASE_URL?>/application/materials.php" enctype="multipart/form-data" id="uploadForm">
      <input type="hidden" name="action" value="upload">

      <div class="form-group">
        <label>Title *</label>
        <input type="text" class="form-control" name="title" placeholder="e.g. Chapter 3: Trigonometry Notes" required maxlength="200">
      </div>
      <div class="form-group">
        <label>Description</label>
        <textarea class="form-control" name="description" rows="3" placeholder="What does this material cover?"></textarea>
      </div>
      <div class="form-group">
        <label>File Type *</label>
        <select class="form-control" name="file_type" id="fileType" onchange="switchType(this.value)">
          <option value="pdf">📄 PDF Document</option>
          <option value="video">🎬 Video</option>
          <option value="image">🖼️ Image</option>
          <option value="link">🔗 External Link</option>
        </select>
      </div>

      <!-- File upload area -->
      <div id="fileGroup" class="form-group">
        <label>File * <span id="acceptHint" style="color:var(--tx3);font-weight:400;text-transform:none;letter-spacing:0;font-size:.78rem">— PDF up to 50MB</span></label>
        <div class="upload-area" id="dropZone"
             onclick="document.getElementById('fileInput').click()"
             ondragover="event.preventDefault();this.style.borderColor='var(--vb)';this.style.background='var(--vc)'"
             ondragleave="this.style.borderColor='';this.style.background=''"
             ondrop="handleFileDrop(event)">
          <div style="font-size:2.5rem;margin-bottom:8px" id="dropIcon">📂</div>
          <div style="font-weight:700;color:var(--tx2)" id="dropTitle">Click to browse or drag & drop</div>
          <div style="font-size:.75rem;color:var(--tx3);margin-top:4px" id="dropSub">Max size: 50MB</div>
        </div>
        <input type="file" name="file" id="fileInput" accept=".pdf" style="display:none"
               onchange="fileSelected(this)">
        <!-- File preview info -->
        <div id="fileInfo" style="display:none;margin-top:10px;padding:10px 14px;
             background:var(--vc);border:1px solid var(--gb2);border-radius:var(--rsm);
             display:flex;align-items:center;gap:10px">
          <span id="fileIcon" style="font-size:1.4rem"></span>
          <div style="flex:1;min-width:0">
            <div id="fileName" style="font-size:.84rem;font-weight:700;color:var(--tx);overflow:hidden;text-overflow:ellipsis;white-space:nowrap"></div>
            <div id="fileSize" style="font-size:.72rem;color:var(--tx3)"></div>
          </div>
          <button type="button" onclick="clearFile()" style="background:none;border:none;color:var(--pk);cursor:pointer;font-size:1rem">✕</button>
        </div>
      </div>

      <!-- External link area -->
      <div id="linkGroup" class="form-group" style="display:none">
        <label>External URL *</label>
        <input type="url" class="form-control" name="external_link"
               placeholder="https://youtube.com/... or https://drive.google.com/...">
        <div style="font-size:.75rem;color:var(--tx3);margin-top:6px">
          YouTube, Google Drive, Notion, or any public URL
        </div>
      </div>

      <div style="display:flex;gap:12px;margin-top:8px">
        <button type="submit" class="btn btn-primary">⬆️ &nbsp;Upload Material</button>
        <a href="<?=BASE_URL?>/presentation/teacher/materials.php" class="btn btn-ghost">Cancel</a>
      </div>
    </form>
  </div>
</div>

<script>
const typeConfig = {
  pdf:   { accept:'.pdf',                        icon:'📄', hint:'PDF up to 50MB',   dropIcon:'📄' },
  video: { accept:'.mp4,.avi,.mov,.mkv,.webm',   icon:'🎬', hint:'MP4, AVI, MOV, MKV, WEBM up to 50MB', dropIcon:'🎬' },
  image: { accept:'.jpg,.jpeg,.png,.gif,.webp',  icon:'🖼️', hint:'JPG, PNG, GIF, WEBP up to 50MB',      dropIcon:'🖼️' },
  link:  { accept:'',                            icon:'🔗', hint:'',                 dropIcon:'🔗' }
};

function switchType(type) {
  const cfg     = typeConfig[type] || typeConfig.pdf;
  const fileGrp = document.getElementById('fileGroup');
  const linkGrp = document.getElementById('linkGroup');
  if (type === 'link') {
    fileGrp.style.display = 'none';
    linkGrp.style.display = '';
  } else {
    fileGrp.style.display = '';
    linkGrp.style.display = 'none';
    document.getElementById('fileInput').accept = cfg.accept;
    document.getElementById('acceptHint').textContent = '— ' + cfg.hint;
    document.getElementById('dropIcon').textContent   = cfg.dropIcon;
    clearFile();
  }
}

function fileSelected(input) {
  if (!input.files || !input.files[0]) return;
  const f    = input.files[0];
  const type = document.getElementById('fileType').value;
  const cfg  = typeConfig[type] || typeConfig.pdf;
  document.getElementById('fileInfo').style.display = 'flex';
  document.getElementById('fileIcon').textContent   = cfg.icon;
  document.getElementById('fileName').textContent   = f.name;
  document.getElementById('fileSize').textContent   = formatBytes(f.size);
  document.getElementById('dropTitle').textContent  = '✓ File selected';
}

function clearFile() {
  document.getElementById('fileInput').value = '';
  document.getElementById('fileInfo').style.display = 'none';
  document.getElementById('dropTitle').textContent  = 'Click to browse or drag & drop';
}

function handleFileDrop(e) {
  e.preventDefault();
  const input = document.getElementById('fileInput');
  const dt    = new DataTransfer();
  dt.items.add(e.dataTransfer.files[0]);
  input.files = dt.files;
  document.getElementById('dropZone').style.borderColor = '';
  document.getElementById('dropZone').style.background  = '';
  fileSelected(input);
}

function formatBytes(bytes) {
  if (bytes < 1024)       return bytes + ' B';
  if (bytes < 1048576)    return (bytes/1024).toFixed(1) + ' KB';
  return (bytes/1048576).toFixed(1) + ' MB';
}
</script>

<?php renderFooter(); ?>
