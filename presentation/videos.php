<?php
require_once __DIR__ . '/../application/config.php';
requireLogin();
require_once __DIR__ . '/layout.php';

$db   = getDB();
$uid  = (int)$_SESSION['user_id'];
$role = $_SESSION['role'];

$all = $db->query("
    SELECT sv.*, u.name AS poster_name, u.role AS poster_role,
      (SELECT COUNT(*) FROM video_likes    WHERE video_id=sv.video_id) AS like_count,
      (SELECT COUNT(*) FROM video_comments WHERE video_id=sv.video_id) AS cmt_count,
      (SELECT COUNT(*) FROM video_saves    WHERE video_id=sv.video_id) AS save_count,
      (SELECT COUNT(*) FROM video_likes    WHERE video_id=sv.video_id AND user_id=$uid) AS i_liked,
      (SELECT COUNT(*) FROM video_saves    WHERE video_id=sv.video_id AND user_id=$uid) AS i_saved
    FROM study_videos sv JOIN users u ON sv.user_id=u.user_id
    ORDER BY sv.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

$mine  = array_values(array_filter($all, fn($v)=>$v['user_id']==$uid));
$saved = array_values(array_filter($all, fn($v)=>$v['i_saved']));

renderHead('Study Videos');
renderSidebar('videos');
renderFlash();
?>
<style>
.vid-share-form{background:var(--surf);border:1px solid var(--rim);border-radius:var(--r);overflow:hidden;margin-bottom:28px;transition:border-color var(--t)}
.vid-share-form:focus-within{border-color:var(--rim3)}
.vid-share-header{padding:16px 22px;border-bottom:1px solid var(--rim);background:linear-gradient(90deg,rgba(110,86,255,.1),transparent);display:flex;align-items:center;justify-content:space-between}
.vid-share-header h3{font-family:'Outfit',sans-serif;font-size:1rem;font-weight:700;color:var(--tx)}
.vid-share-body{padding:20px 22px}
.vid-form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px}
#ytPreviewBox{display:none;margin-bottom:16px}
.preview-label{font-size:.7rem;font-weight:800;color:var(--c);letter-spacing:2px;text-transform:uppercase;margin-bottom:8px;display:flex;align-items:center;gap:6px}
.preview-frame{max-width:300px;border-radius:12px;overflow:hidden;border:1px solid var(--rim2);box-shadow:0 0 30px rgba(110,86,255,.2)}
@media(max-width:700px){.vid-form-row{grid-template-columns:1fr}}
</style>

<div class="page-header">
  <h1>Study Videos</h1>
  <p><?=count($all)?> videos in the community</p>
</div>

<!-- SHARE FORM -->
<div class="vid-share-form">
  <div class="vid-share-header">
    <h3>▶ Share a Video</h3>
    <span style="font-size:.75rem;color:var(--tx3)">YouTube links only</span>
  </div>
  <div class="vid-share-body">
    <form method="POST" action="<?=BASE_URL?>/application/videos.php">
      <input type="hidden" name="action" value="post">
      <div class="vid-form-row">
        <div class="form-group" style="margin:0">
          <label>YouTube URL *</label>
          <input type="url" name="youtube_url" id="ytUrlInput" class="form-control"
                 placeholder="https://youtube.com/watch?v=..." required>
        </div>
        <div class="form-group" style="margin:0">
          <label>Title *</label>
          <input type="text" name="title" class="form-control" placeholder="e.g. Quadratic Equations Explained" required maxlength="200">
        </div>
      </div>
      <div class="form-group" style="margin-bottom:16px">
        <label>Description <span style="color:var(--tx3);font-size:.7rem;text-transform:none;letter-spacing:0">(optional)</span></label>
        <textarea name="description" class="form-control" rows="2" placeholder="Topics covered, chapter references…" maxlength="500"></textarea>
      </div>
      <div id="ytPreviewBox">
        <div class="preview-label">✓ Valid URL — Preview</div>
        <div class="preview-frame">
          <div class="video-thumb-wrap"><iframe id="ytPreviewFrame" allowfullscreen></iframe></div>
        </div>
      </div>
      <button type="submit" class="btn btn-primary">▶ &nbsp;Post Video</button>
    </form>
  </div>
</div>

<!-- TABS -->
<div class="tab-bar">
  <button class="tab-btn active" data-tab="all">All Videos <span style="opacity:.5;font-size:.8rem">(<?=count($all)?>)</span></button>
  <button class="tab-btn" data-tab="mine">My Videos <span style="opacity:.5;font-size:.8rem">(<?=count($mine)?>)</span></button>
  <button class="tab-btn" data-tab="saved">Saved <span style="opacity:.5;font-size:.8rem">(<?=count($saved)?>)</span></button>
</div>

<?php
function renderCards(array $list, int $uid, string $base, string $role): void {
    if (!$list) {
        echo '<div class="empty-state"><div class="empty-icon">📹</div>'
           . '<h3>No videos here yet</h3><p>Be the first to share one!</p></div>';
        return;
    }
    echo '<div class="video-grid">';
    foreach ($list as $v):
        $init  = strtoupper($v['poster_name'][0] ?? '?');
        $isOwn = ($v['user_id'] == $uid || $role === 'admin');
        $rc    = $v['poster_role']==='teacher' ? 'info' : 'primary';
?>
<div class="video-card" id="vc-<?=$v['video_id']?>">
  <div class="video-thumb-wrap">
    <iframe src="https://www.youtube.com/embed/<?=htmlspecialchars($v['youtube_id'])?>?rel=0&modestbranding=1"
            allowfullscreen loading="lazy"></iframe>
  </div>
  <div class="video-body">
    <div class="video-title"><?=htmlspecialchars($v['title'])?></div>
    <?php if ($v['description']): ?>
    <div class="video-desc"><?=htmlspecialchars(mb_substr($v['description'],0,110))?><?=mb_strlen($v['description'])>110?'…':''?></div>
    <?php endif; ?>
    <div class="video-poster">
      <div class="poster-av"><?=$init?></div>
      <?=htmlspecialchars($v['poster_name'])?>
      <span class="badge badge-<?=$rc?>"><?=$v['poster_role']?></span>
      <span style="margin-left:auto;font-size:.7rem"><?=date('d M y',strtotime($v['created_at']))?></span>
    </div>
  </div>
  <div class="video-actions">
    <button class="action-btn<?=$v['i_liked']?' liked':''?>" id="like-<?=$v['video_id']?>"
            onclick="doLike(<?=$v['video_id']?>)" title="Like">
      ♥ <span class="lc"><?=(int)$v['like_count']?></span>
    </button>
    <button class="action-btn<?=$v['i_saved']?' saved':''?>" id="save-<?=$v['video_id']?>"
            onclick="doSave(<?=$v['video_id']?>)" title="Save">
      ★ <span class="sc"><?=(int)$v['save_count']?></span>
    </button>
    <?php if ($isOwn): ?>
    <a href="<?=$base?>/application/videos.php?action=delete&id=<?=$v['video_id']?>"
       class="btn btn-sm btn-danger" style="margin-left:auto"
       onclick="return confirm('Delete this video?')">✕ Delete</a>
    <?php endif; ?>
  </div>
  <div class="comments-section">
    <button class="comments-toggle" id="ctoggle-<?=$v['video_id']?>"
            onclick="toggleComments(<?=$v['video_id']?>)">
      💬 <span class="cc"><?=(int)$v['cmt_count']?></span> comment<?=$v['cmt_count']!=1?'s':''?> · tap to expand
    </button>
    <div class="comments-body" id="cbody-<?=$v['video_id']?>">
      <div class="comments-inner" id="cinner-<?=$v['video_id']?>">
        <div style="color:var(--tx3);font-size:.8rem;padding:6px 0">Click to load comments…</div>
      </div>
      <div class="comment-form">
        <input class="comment-input" id="cinput-<?=$v['video_id']?>"
               placeholder="Write a comment…" maxlength="500">
        <button class="btn btn-sm btn-primary" onclick="doComment(<?=$v['video_id']?>)">Send</button>
      </div>
    </div>
  </div>
</div>
<?php endforeach;
    echo '</div>';
}
?>

<div id="tab-all"><?php renderCards($all,   $uid, BASE_URL, $role); ?></div>
<div id="tab-mine"  style="display:none"><?php renderCards($mine,  $uid, BASE_URL, $role); ?></div>
<div id="tab-saved" style="display:none"><?php renderCards($saved, $uid, BASE_URL, $role); ?></div>

<script>
const base = window.__baseUrl;

/* tabs */
document.querySelectorAll('.tab-btn').forEach(b => b.addEventListener('click', () => {
    document.querySelectorAll('.tab-btn').forEach(x => x.classList.remove('active'));
    b.classList.add('active');
    ['all','mine','saved'].forEach(k =>
        document.getElementById('tab-'+k).style.display = k===b.dataset.tab ? 'block' : 'none');
}));

/* youtube preview */
document.getElementById('ytUrlInput').addEventListener('input', function(){
    const m = this.value.match(/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([A-Za-z0-9_\-]{11})/);
    const box = document.getElementById('ytPreviewBox');
    if (m) {
        document.getElementById('ytPreviewFrame').src = 'https://www.youtube.com/embed/'+m[1]+'?rel=0';
        box.style.display = 'block';
    } else { box.style.display = 'none'; }
});

/* like */
function doLike(vid) {
    fetch(base+'/application/videos.php?action=like&id='+vid, {method:'POST'})
    .then(r=>r.json()).then(d=>{
        if (!d.ok) return;
        const btn = document.getElementById('like-'+vid);
        btn.classList.toggle('liked', d.liked);
        btn.querySelector('.lc').textContent = d.count;
    }).catch(console.error);
}

/* save */
function doSave(vid) {
    fetch(base+'/application/videos.php?action=save&id='+vid, {method:'POST'})
    .then(r=>r.json()).then(d=>{
        if (!d.ok) return;
        const btn = document.getElementById('save-'+vid);
        btn.classList.toggle('saved', d.saved);
        btn.querySelector('.sc').textContent = d.count;
    }).catch(console.error);
}

/* comments */
const loadedComments = {};
function toggleComments(vid) {
    const body = document.getElementById('cbody-'+vid);
    const open = body.classList.toggle('open');
    if (open && !loadedComments[vid]) loadComments(vid);
}
function loadComments(vid) {
    loadedComments[vid] = true;
    fetch(base+'/application/videos.php?action=comments&id='+vid)
    .then(r=>r.json()).then(d=>{
        const box = document.getElementById('cinner-'+vid);
        if (!d.comments || !d.comments.length) {
            box.innerHTML='<div style="color:var(--tx3);font-size:.8rem;padding:6px 0">No comments yet — be first!</div>';
            return;
        }
        box.innerHTML = d.comments.map(c=>`
            <div class="comment-item">
                <div class="comment-av">${c.name[0].toUpperCase()}</div>
                <div class="comment-bubble">
                    <div class="comment-author">${c.name} <span style="color:var(--tx3);font-weight:400;font-size:.7rem">· ${c.dt}</span></div>
                    <div class="comment-text">${c.comment}</div>
                </div>
            </div>`).join('');
    }).catch(()=>{ document.getElementById('cinner-'+vid).innerHTML='<div style="color:var(--tx3)">Failed to load.</div>'; });
}
function doComment(vid) {
    const inp  = document.getElementById('cinput-'+vid);
    const text = inp.value.trim();
    if (!text) return;
    inp.disabled = true;
    fetch(base+'/application/videos.php?action=comment&id='+vid, {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'comment='+encodeURIComponent(text)
    }).then(r=>r.json()).then(d=>{
        if (d.ok) {
            inp.value=''; inp.disabled=false;
            loadedComments[vid] = false;
            loadComments(vid);
            const cc = document.querySelector('#vc-'+vid+' .cc');
            if (cc) cc.textContent = parseInt(cc.textContent||0)+1;
        } else { inp.disabled=false; alert('Failed to post comment.'); }
    }).catch(()=>{ inp.disabled=false; });
}
</script>
<?php renderFooter(); ?>
