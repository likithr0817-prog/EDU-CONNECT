<?php
require_once __DIR__ . '/../../application/config.php';
requireRole('teacher');
require_once __DIR__ . '/../layout.php';

$db   = getDB();
$user = getCurrentUser();
$uid  = $user['user_id'];

$stmt = $db->prepare("SELECT * FROM teachers WHERE user_id=?");
$stmt->bind_param("i", $uid); $stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();

$init     = strtoupper(substr($user['name'], 0, 1));
$hasPhoto = !empty($user['profile_photo']) && file_exists(BASE_PATH . '/' . $user['profile_photo']);
$photoUrl = $hasPhoto ? BASE_URL . '/' . $user['profile_photo'] : null;

renderHead('My Profile');
renderSidebar('profile');
renderFlash();
?>

<div class="page-header">
  <h1>My Profile</h1>
  <p>Manage your teacher account</p>
</div>

<div style="display:grid;grid-template-columns:300px 1fr;gap:24px;align-items:start" class="profile-grid">

  <div style="display:flex;flex-direction:column;gap:16px">

    <!-- Profile card -->
    <div class="card">
      <div class="card-body" style="text-align:center;padding:28px 22px">
        <div id="photoWrap" style="position:relative;display:inline-block;margin-bottom:16px;cursor:pointer"
             onclick="document.getElementById('photoInput').click()">
          <?php if ($photoUrl): ?>
          <img id="photoPreview" src="<?=$photoUrl?>" alt="Profile photo"
               style="width:88px;height:88px;border-radius:20px;object-fit:cover;
                      border:3px solid var(--rim2);box-shadow:var(--gv);display:block">
          <?php else: ?>
          <div id="photoPreview" class="profile-photo" style="width:88px;height:88px;font-size:2.2rem;margin:0">
            <?=$init?>
          </div>
          <?php endif; ?>
          <div style="position:absolute;bottom:0;right:0;width:28px;height:28px;border-radius:50%;
                      background:var(--vb);display:flex;align-items:center;justify-content:center;
                      font-size:.75rem;border:2px solid var(--bg);box-shadow:0 0 10px var(--vg)">
            📷
          </div>
        </div>

        <div class="uid-badge"><?=htmlspecialchars($user['unique_id'])?></div>
        <h3 style="font-family:'Syne',sans-serif;font-size:1.1rem;font-weight:800;margin:8px 0 4px">
          <?=htmlspecialchars($user['name'])?>
        </h3>
        <div style="font-size:.78rem;color:var(--c);font-weight:700;letter-spacing:2px;text-transform:uppercase;margin-bottom:10px">
          Teacher
        </div>
        <div style="font-size:.82rem;color:var(--tx3);line-height:1.8">
          <?=htmlspecialchars($user['email'])?><br>
          <?=htmlspecialchars($user['college_name'] ?? '—')?><br>
          Subject: <?=htmlspecialchars($teacher['subject'] ?? '—')?>
        </div>
        <div style="margin-top:12px"><span class="badge badge-success">Approved</span></div>
      </div>
    </div>

    <!-- Photo upload -->
    <div class="card">
      <div class="card-header"><h3>📷 Change Photo</h3></div>
      <div class="card-body">
        <form method="POST" action="<?=BASE_URL?>/application/profile.php" enctype="multipart/form-data" id="photoForm">
          <input type="hidden" name="action" value="upload_photo">
          <input type="file" name="photo" id="photoInput" accept=".jpg,.jpeg,.png,.gif,.webp"
                 style="display:none" onchange="previewPhoto(this)">
          <div id="photoDropzone"
               onclick="document.getElementById('photoInput').click()"
               style="border:2px dashed var(--rim2);border-radius:var(--rsm);padding:20px;
                      text-align:center;cursor:pointer;transition:all var(--t);margin-bottom:14px"
               ondragover="event.preventDefault();this.style.borderColor='var(--vb)'"
               ondrop="handleDrop(event)">
            <div style="font-size:1.8rem;margin-bottom:6px">🖼️</div>
            <div style="font-size:.8rem;color:var(--tx3);font-weight:600" id="dropText">Click or drag a photo here</div>
            <div style="font-size:.7rem;color:var(--tx3);margin-top:4px">JPG, PNG, WEBP · max 5MB</div>
          </div>
          <button type="submit" id="photoSubmitBtn" class="btn btn-primary btn-block" disabled
                  style="opacity:.5;cursor:not-allowed">Upload Photo</button>
        </form>
        <?php if ($hasPhoto): ?>
        <div style="text-align:center;margin-top:10px">
          <a href="<?=BASE_URL?>/application/profile.php?action=remove_photo"
             onclick="return confirm('Remove your profile photo?')"
             style="font-size:.76rem;color:var(--pk)">Remove photo</a>
        </div>
        <?php endif; ?>
      </div>
    </div>

  </div>

  <!-- Edit form -->
  <div class="card">
    <div class="card-header"><h3>✏️ Edit Profile</h3></div>
    <div class="card-body">
      <form method="POST" action="<?=BASE_URL?>/application/profile.php">
        <input type="hidden" name="action" value="update">
        <div class="form-row">
          <div class="form-group">
            <label>Full Name</label>
            <input type="text" class="form-control" name="name" value="<?=htmlspecialchars($user['name'])?>" required>
          </div>
          <div class="form-group">
            <label>Age</label>
            <input type="number" class="form-control" name="age" value="<?=$user['age']?>" min="5" max="100">
          </div>
        </div>
        <div class="form-group">
          <label>Email <span style="color:var(--tx3);font-weight:400;text-transform:none;letter-spacing:0;font-size:.8rem">(cannot change)</span></label>
          <input type="email" class="form-control" value="<?=htmlspecialchars($user['email'])?>" disabled style="opacity:.5">
        </div>
        <div class="form-group">
          <label>Subject / Department</label>
          <input type="text" class="form-control" name="subject" value="<?=htmlspecialchars($teacher['subject'] ?? '')?>">
        </div>
        <div class="form-group">
          <label>College / Institution</label>
          <input type="text" class="form-control" name="college_name" value="<?=htmlspecialchars($user['college_name'] ?? '')?>">
        </div>
        <div class="form-group">
          <label>Contact Number</label>
          <input type="tel" class="form-control" name="contact_number" value="<?=htmlspecialchars($user['contact_number'] ?? '')?>">
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </form>
    </div>
  </div>

</div>

<style>
@media(max-width:800px){.profile-grid{grid-template-columns:1fr!important}}
#photoDropzone:hover{border-color:var(--vb)!important;background:var(--vc)}
</style>
<script>
function previewPhoto(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    const reader = new FileReader();
    reader.onload = e => {
        const prev = document.getElementById('photoPreview');
        if (prev.tagName === 'IMG') {
            prev.src = e.target.result;
        } else {
            const img = document.createElement('img');
            img.id = 'photoPreview';
            img.src = e.target.result;
            img.style.cssText = 'width:88px;height:88px;border-radius:20px;object-fit:cover;border:3px solid var(--rim2);box-shadow:var(--gv);display:block';
            prev.replaceWith(img);
        }
        document.getElementById('dropText').textContent = file.name;
        const btn = document.getElementById('photoSubmitBtn');
        btn.disabled = false; btn.style.opacity = '1'; btn.style.cursor = 'pointer';
    };
    reader.readAsDataURL(file);
}
function handleDrop(e) {
    e.preventDefault();
    const input = document.getElementById('photoInput');
    const dt = new DataTransfer();
    dt.items.add(e.dataTransfer.files[0]);
    input.files = dt.files;
    previewPhoto(input);
}
</script>

<?php renderFooter(); ?>
