<?php
require_once __DIR__ . '/../application/config.php';
requireLogin();
require_once __DIR__ . '/layout.php';

$db  = getDB();
$uid = (int)$_SESSION['user_id'];

$all  = $db->query("
    SELECT f.*, u.name, u.unique_id, u.role AS urole
    FROM feedback f JOIN users u ON f.user_id=u.user_id
    ORDER BY f.created_at DESC LIMIT 100
")->fetch_all(MYSQLI_ASSOC);

$mine = $db->query("SELECT * FROM feedback WHERE user_id=$uid LIMIT 1")->fetch_assoc();

$avg  = $db->query("SELECT ROUND(AVG(rating),1) AS a, COUNT(*) AS c FROM feedback")->fetch_assoc();
$avgR = $avg['a'] ?? 0;
$tot  = (int)($avg['c'] ?? 0);

/* star distribution */
$dist = [];
for ($i=1;$i<=5;$i++) {
    $dist[$i] = (int)$db->query("SELECT COUNT(*) FROM feedback WHERE rating=$i")->fetch_row()[0];
}

renderHead('Feedback');
renderSidebar('feedback');
renderFlash();
?>

<div class="page-header">
  <h1>Community Feedback</h1>
  <p>Rate your experience and read what others think</p>
</div>

<!-- STAT BANNER -->
<div style="display:grid;grid-template-columns:auto 1fr;gap:24px;background:var(--surf);border:1px solid var(--rim);
            border-radius:var(--r);padding:28px;margin-bottom:28px;align-items:center;animation:scaleIn .4s var(--spring)">
  <!-- Big rating -->
  <div style="text-align:center;padding-right:24px;border-right:1px solid var(--rim)">
    <div style="font-family:'Outfit',sans-serif;font-size:4.5rem;font-weight:900;line-height:1;
                background:linear-gradient(135deg,var(--am),#ff8800);
                -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">
      <?=$avgR ?: '—'?>
    </div>
    <div style="font-size:1.6rem;color:var(--am);letter-spacing:4px;margin:4px 0;
                text-shadow:0 0 20px rgba(255,171,0,.6)">
      <?=$avgR ? str_repeat('★', round($avgR)) . str_repeat('☆', 5-round($avgR)) : '☆☆☆☆☆'?>
    </div>
    <div style="font-size:.75rem;color:var(--tx3);font-weight:700;letter-spacing:1px"><?=$tot?> RATINGS</div>
  </div>
  <!-- Star bars -->
  <div style="display:flex;flex-direction:column;gap:8px;">
    <?php for($i=5;$i>=1;$i--): ?>
    <?php $pct = $tot > 0 ? round($dist[$i]/$tot*100) : 0; ?>
    <div style="display:flex;align-items:center;gap:10px;font-size:.8rem">
      <span style="color:var(--am);width:14px;text-align:right;font-weight:700"><?=$i?></span>
      <span style="color:var(--am);font-size:.75rem">★</span>
      <div style="flex:1;height:8px;background:var(--surf2);border-radius:99px;overflow:hidden">
        <div style="height:100%;width:<?=$pct?>%;
                    background:linear-gradient(90deg,var(--am),#ff8800);
                    border-radius:99px;transition:width 1s var(--ease);
                    box-shadow:0 0 8px rgba(255,171,0,.4)"></div>
      </div>
      <span style="color:var(--tx3);width:30px;font-size:.72rem"><?=$pct?>%</span>
    </div>
    <?php endfor; ?>
  </div>
</div>

<!-- MAIN GRID -->
<div style="display:grid;grid-template-columns:420px 1fr;gap:24px;align-items:start" class="fb-grid">

  <!-- FORM -->
  <div class="card" style="<?=$mine?'border-color:rgba(110,86,255,.35)':''?>">
    <div class="card-header" style="background:linear-gradient(90deg,rgba(110,86,255,.1),transparent)">
      <h3><?=$mine?'✏️ Update Your Rating':'⭐ Leave Feedback'?></h3>
      <?php if ($mine): ?>
      <span class="badge badge-primary">Rated <?=$mine['rating']?>/5</span>
      <?php endif; ?>
    </div>
    <div class="card-body">
      <form method="POST" action="<?=BASE_URL?>/application/feedback_handler.php" id="fbForm">
        <input type="hidden" name="action" value="submit">

        <div class="form-group">
          <label>Your Rating <span style="color:var(--pk)">*</span></label>
          <div class="star-rating" id="starRow">
            <?php for($i=1;$i<=5;$i++): ?>
            <button type="button" class="star-btn<?=($mine&&$mine['rating']>=$i)?' active':''?>"
                    data-v="<?=$i?>" onclick="setStars(<?=$i?>)">★</button>
            <?php endfor; ?>
          </div>
          <input type="hidden" name="rating" id="ratingVal" value="<?=$mine['rating']??0?>">
          <div id="ratingHint" style="font-size:.75rem;color:var(--tx3);margin-top:6px;min-height:18px">
            <?php
            $hints = ['','😕 Poor','😐 Fair','🙂 Good','😀 Great','🤩 Excellent!'];
            echo $mine ? ($hints[$mine['rating']]??'') : 'Click a star to rate';
            ?>
          </div>
        </div>

        <div class="form-group">
          <label>Comment <span style="color:var(--tx3);font-size:.7rem;text-transform:none;letter-spacing:0">(optional)</span></label>
          <textarea name="comment" class="form-control" rows="4"
                    placeholder="What do you love? What could improve?"
                    maxlength="1000"><?=htmlspecialchars($mine['comment']??'')?></textarea>
        </div>

        <?php if ($mine): ?>
        <div style="font-size:.76rem;color:var(--tx3);margin-bottom:14px;padding:10px 12px;
                    background:var(--vc);border-radius:var(--rsm);border:1px solid var(--rim2)">
          ✓ You submitted this <?=date('d M Y',strtotime($mine['created_at']))?>
        </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary btn-block" id="submitBtn"
                <?=!$mine?'disabled style="opacity:.5;cursor:not-allowed"':''?>>
          <?=$mine?'Update Feedback':'Submit Feedback'?>
        </button>

        <?php if ($mine): ?>
        <div style="text-align:center;margin-top:12px">
          <a href="<?=BASE_URL?>/application/feedback_handler.php?action=delete"
             onclick="return confirm('Remove your feedback?')"
             style="font-size:.78rem;color:var(--pk)">Remove my feedback</a>
        </div>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <!-- REVIEWS -->
  <div>
    <div style="font-size:.7rem;color:var(--tx3);text-transform:uppercase;letter-spacing:3px;
                font-weight:800;margin-bottom:16px">Community Reviews</div>
    <?php if (!$all): ?>
    <div class="empty-state">
      <div class="empty-icon">⭐</div>
      <h3>No reviews yet</h3>
      <p>Be the first to share your thoughts!</p>
    </div>
    <?php else: ?>
    <div style="max-height:70vh;overflow-y:auto;padding-right:6px;display:flex;flex-direction:column;gap:12px">
      <?php foreach ($all as $f):
        $isOwn = ($f['user_id'] == $uid);
        $init  = strtoupper($f['name'][0] ?? '?');
        $stars = str_repeat('★',$f['rating']).str_repeat('☆',5-$f['rating']);
      ?>
      <div class="feedback-card" style="<?=$isOwn?'border-color:rgba(110,86,255,.4)':''?>">
        <div class="feedback-header">
          <div class="fb-avatar"><?=$init?></div>
          <div style="flex:1;min-width:0">
            <div style="font-size:.86rem;font-weight:700;color:var(--tx);display:flex;align-items:center;gap:8px">
              <?=htmlspecialchars($f['name'])?>
              <?php if ($isOwn): ?><span class="badge badge-primary" style="font-size:.6rem">You</span><?php endif; ?>
            </div>
            <div style="font-size:.68rem;color:var(--tx3)"><?=$f['unique_id']?> · <?=$f['urole']?></div>
          </div>
          <div class="fb-stars"><?=$stars?></div>
        </div>
        <?php if ($f['comment']): ?>
        <div class="feedback-comment" style="margin-top:4px"><?=htmlspecialchars($f['comment'])?></div>
        <?php endif; ?>
        <div class="feedback-meta"><?=date('d M Y',strtotime($f['created_at']))?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

</div>

<style>
@media(max-width:900px){.fb-grid{grid-template-columns:1fr!important}}
</style>
<script>
const hints = ['','😕 Poor','😐 Fair','🙂 Good','😀 Great','🤩 Excellent!'];
function setStars(val){
    document.getElementById('ratingVal').value = val;
    document.querySelectorAll('.star-btn').forEach((b,i) => b.classList.toggle('active', i<val));
    document.getElementById('ratingHint').textContent = hints[val] || '';
    const sb = document.getElementById('submitBtn');
    sb.disabled = false; sb.style.opacity = '1'; sb.style.cursor = 'pointer';
}
/* hover preview */
document.querySelectorAll('.star-btn').forEach((b,i)=>{
    b.addEventListener('mouseenter', ()=>{
        document.getElementById('ratingHint').textContent = hints[i+1]||'';
    });
    b.addEventListener('mouseleave', ()=>{
        const v = parseInt(document.getElementById('ratingVal').value)||0;
        document.getElementById('ratingHint').textContent = v ? hints[v] : 'Click a star to rate';
    });
});
</script>
<?php renderFooter(); ?>
