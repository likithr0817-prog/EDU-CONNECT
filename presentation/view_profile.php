<?php
require_once __DIR__ . '/../application/config.php';
requireLogin();
require_once __DIR__ . '/layout.php';

$db      = getDB();
$myRole  = $_SESSION['role'];
$myId    = $_SESSION['user_id'];
$view_id = intval($_GET['id'] ?? 0);

if (!$view_id) { setFlash('error','No user specified.'); redirect(BASE_URL.'/presentation/search.php'); }

$stmt = $db->prepare("
    SELECT u.*, s.class_name, t.subject
    FROM users u
    LEFT JOIN students s ON u.user_id=s.user_id
    LEFT JOIN teachers t ON u.user_id=t.user_id
    WHERE u.user_id=? AND u.role!='admin' AND u.is_approved='approved'
");
$stmt->bind_param("i",$view_id); $stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
if (!$profile) { setFlash('error','Profile not found.'); redirect(BASE_URL.'/presentation/search.php'); }

$mstmt = $db->prepare("SELECT * FROM materials WHERE user_id=? ORDER BY upload_date DESC");
$mstmt->bind_param("i",$view_id); $mstmt->execute();
$materials = $mstmt->get_result();

$isOwn    = ($myId === $view_id);
$isTeach  = ($profile['role'] === 'teacher');
$init     = strtoupper(substr($profile['name'],0,1));
$matCount = $materials->num_rows;
$hasPhoto = !empty($profile['profile_photo']) && file_exists(BASE_PATH.'/'.$profile['profile_photo']);
$photoUrl = $hasPhoto ? BASE_URL.'/'.$profile['profile_photo'] : null;

renderHead(htmlspecialchars($profile['name'])."'s Profile");
renderSidebar('search');
renderFlash();
?>

<div style="font-size:.85rem;color:var(--tx3);margin-bottom:20px;display:flex;align-items:center;gap:6px">
  <a href="<?=BASE_URL?>/presentation/search.php" style="color:var(--vb)">← Search</a>
  <span>›</span>
  <span><?=htmlspecialchars($profile['name'])?></span>
  <?php if($isOwn): ?><span class="badge badge-info">You</span><?php endif; ?>
</div>

<div style="display:grid;grid-template-columns:290px 1fr;gap:24px;align-items:start" class="view-profile-grid">

  <!-- PROFILE CARD -->
  <div class="card">
    <div class="card-body" style="text-align:center;padding:28px 22px">

      <?php if($photoUrl): ?>
      <img src="<?=$photoUrl?>" alt="Profile photo"
           style="width:84px;height:84px;border-radius:20px;object-fit:cover;
                  border:3px solid var(--gb2);box-shadow:var(--gv);margin:0 auto 14px;display:block">
      <?php else: ?>
      <div class="profile-photo" style="width:84px;height:84px;font-size:2.1rem;margin:0 auto 14px;
           background:<?=$isTeach?'linear-gradient(135deg,var(--c),#00aaaa)':'linear-gradient(135deg,var(--vb),var(--c))'?>">
        <?=$init?>
      </div>
      <?php endif; ?>

      <div class="uid-badge"><?=htmlspecialchars($profile['unique_id'])?></div>
      <h2 style="font-family:'Syne',sans-serif;font-size:1.2rem;font-weight:800;margin:8px 0 4px">
        <?=htmlspecialchars($profile['name'])?>
      </h2>
      <div style="font-size:.78rem;color:<?=$isTeach?'var(--c)':'var(--vb)'?>;font-weight:700;
                  letter-spacing:2px;text-transform:uppercase;margin-bottom:14px">
        <?=$isTeach?'Teacher':'Student'?>
      </div>

      <div style="border-top:1px solid var(--gb);padding-top:14px;text-align:left;display:flex;flex-direction:column;gap:10px">
        <?php if($profile['college_name']): ?>
        <div style="display:flex;gap:10px;align-items:flex-start">
          <span>🏫</span>
          <div>
            <div style="font-size:.68rem;color:var(--tx3);text-transform:uppercase;letter-spacing:1px;font-weight:700">College</div>
            <div style="font-size:.86rem;font-weight:600"><?=htmlspecialchars($profile['college_name'])?></div>
          </div>
        </div>
        <?php endif; ?>
        <?php if($isTeach && $profile['subject']): ?>
        <div style="display:flex;gap:10px;align-items:flex-start">
          <span>📖</span>
          <div>
            <div style="font-size:.68rem;color:var(--tx3);text-transform:uppercase;letter-spacing:1px;font-weight:700">Subject</div>
            <div style="font-size:.86rem;font-weight:600"><?=htmlspecialchars($profile['subject'])?></div>
          </div>
        </div>
        <?php endif; ?>
        <?php if(!$isTeach && $profile['class_name']): ?>
        <div style="display:flex;gap:10px;align-items:flex-start">
          <span>📚</span>
          <div>
            <div style="font-size:.68rem;color:var(--tx3);text-transform:uppercase;letter-spacing:1px;font-weight:700">Class</div>
            <div style="font-size:.86rem;font-weight:600"><?=htmlspecialchars($profile['class_name'])?></div>
          </div>
        </div>
        <?php endif; ?>
        <?php if($profile['age']): ?>
        <div style="display:flex;gap:10px;align-items:flex-start">
          <span>🎂</span>
          <div>
            <div style="font-size:.68rem;color:var(--tx3);text-transform:uppercase;letter-spacing:1px;font-weight:700">Age</div>
            <div style="font-size:.86rem;font-weight:600"><?=$profile['age']?></div>
          </div>
        </div>
        <?php endif; ?>
        <div style="display:flex;gap:10px;align-items:flex-start">
          <span>📅</span>
          <div>
            <div style="font-size:.68rem;color:var(--tx3);text-transform:uppercase;letter-spacing:1px;font-weight:700">Member Since</div>
            <div style="font-size:.86rem;font-weight:600"><?=date('F Y',strtotime($profile['created_at']))?></div>
          </div>
        </div>
      </div>

      <div style="margin-top:16px;padding:12px;background:var(--vc);border-radius:var(--rsm);border:1px solid var(--gb2)">
        <div style="font-family:'Syne',sans-serif;font-size:1.8rem;font-weight:900;color:var(--vb)"><?=$matCount?></div>
        <div style="font-size:.72rem;color:var(--tx3);font-weight:600">Materials Uploaded</div>
      </div>

      <?php if($isOwn): ?>
      <div style="margin-top:14px">
        <a href="<?=BASE_URL?>/presentation/<?=$myRole?>/profile.php" class="btn btn-outline btn-sm btn-block">✏️ Edit My Profile</a>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- MATERIALS -->
  <div class="card">
    <div class="card-header">
      <h3>📁 <?=$isOwn?'My':htmlspecialchars($profile['name'])."'s"?> Materials
        <span style="font-size:.8rem;font-weight:400;color:var(--tx3)">(<?=$matCount?>)</span>
      </h3>
    </div>
    <div class="card-body">
      <?php if($matCount===0): ?>
      <div class="empty-state" style="padding:40px 20px">
        <div class="empty-icon">📭</div>
        <h3>No materials yet</h3>
      </div>
      <?php else:
        $icons=['pdf'=>'📄','video'=>'🎬','image'=>'🖼️','link'=>'🔗'];
        $materials->data_seek(0);
      ?>
      <div class="materials-grid">
        <?php while($m=$materials->fetch_assoc()):
          $icon=$icons[$m['file_type']]??'📁';
          $serveUrl=$m['file_path']?BASE_URL.'/uploads/serve.php?f='.urlencode($m['file_path']).'&download=0':null;
          $dlUrl=$m['file_path']?BASE_URL.'/uploads/serve.php?f='.urlencode($m['file_path']).'&download=1':null;
        ?>
        <div class="material-card">
          <div class="mat-header">
            <div class="mat-type-badge"><?=$icon?> <?=strtoupper($m['file_type'])?></div>
            <div class="mat-title"><?=htmlspecialchars($m['title'])?></div>
          </div>
          <div class="mat-body">
            <p class="mat-desc"><?=htmlspecialchars(substr($m['description']??'No description',0,100))?></p>
            <p class="mat-meta">📅 <?=date('d M Y',strtotime($m['upload_date']))?></p>
          </div>
          <div class="mat-actions">
            <?php if($m['file_type']==='link'): ?>
              <a href="<?=htmlspecialchars($m['external_link'])?>" target="_blank" rel="noopener" class="btn btn-primary btn-sm">🔗 Open</a>
            <?php elseif($m['file_path']): ?>
              <a href="<?=$serveUrl?>" target="_blank" class="btn btn-primary btn-sm">👁️ View</a>
              <a href="<?=$dlUrl?>" class="btn btn-outline btn-sm">⬇️ Download</a>
            <?php endif; ?>
            <?php if($isOwn): ?>
              <a href="<?=BASE_URL?>/application/materials.php?action=delete&id=<?=$m['material_id']?>"
                 class="btn btn-danger btn-sm" onclick="return confirm('Delete?')">🗑️</a>
            <?php endif; ?>
          </div>
        </div>
        <?php endwhile; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>

</div>

<style>@media(max-width:800px){.view-profile-grid{grid-template-columns:1fr!important}}</style>
<?php renderFooter(); ?>
