<div class="page-head" style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap">
  <div>
    <h1 class="page-title">Market News</h1>
    <p class="page-sub">Auto-pulls one top real-estate story a day and removes posts after 7 days. Edit, delete, or add your own below.</p>
  </div>
  <div style="display:flex;gap:.6rem">
    <button type="button" class="btn btn-outline" id="nw-fetch"><?= svgIcon('swap',13) ?>Fetch now</button>
    <button type="button" class="btn btn-primary" onclick="nwOpen()"><?= svgIcon('file',13,'#fff') ?>Add post</button>
  </div>
</div>

<div id="nw-alert"></div>

<!-- Feed settings -->
<div class="section">
  <div class="section-head"><span class="section-title">News feed</span><span class="section-meta">The RSS source posts are pulled from</span></div>
  <div class="section-body">
    <div class="fg">
      <label class="fl">RSS feed URL</label>
      <input class="fi" id="nw-url" style="font-family:monospace;font-size:12px" value="<?= htmlspecialchars($feedUrl) ?>"/>
      <p class="fl-opt" style="margin-top:.4rem">Default is a curated Google News query limited to top global outlets (Bloomberg, Reuters, FT, WSJ, CNBC, The Guardian, SCMP, Knight Frank). Adjust the <code>site:</code> list to change sources.</p>
    </div>
    <label style="display:flex;align-items:center;gap:.6rem;font-size:13px;cursor:pointer;margin:.3rem 0 1rem">
      <input type="checkbox" id="nw-enabled" <?= $newsEnabled ? 'checked' : '' ?> style="width:16px;height:16px;accent-color:#C0392B;cursor:pointer"/>
      Enabled — pull a new story automatically each day
    </label>
    <button type="button" class="btn btn-primary" id="nw-save-settings"><?= svgIcon('check',13,'#fff') ?>Save feed settings</button>
  </div>
</div>

<!-- Posts -->
<div class="section" style="margin-top:1.5rem">
  <div class="section-head"><span class="section-title">Posts</span><span class="section-meta"><?= count($posts) ?> live</span></div>
  <div class="section-body" style="padding:0">
    <?php if (empty($posts)): ?>
      <p style="padding:1.6rem;color:var(--text3);font-size:13px">No posts yet. The daily cron will add one, or click <strong>Fetch now</strong> / <strong>Add post</strong>.</p>
    <?php else: ?>
    <table class="tbl" style="width:100%;border-collapse:collapse">
      <thead><tr><th style="text-align:left">Headline</th><th>Source</th><th>Status</th><th style="text-align:right">Actions</th></tr></thead>
      <tbody>
        <?php foreach ($posts as $p):
          $exp = $p['expires_at'] ? (int) ceil((strtotime($p['expires_at']) - time()) / 86400) : null;
          $soon = $exp !== null && $exp <= 1;
        ?>
          <tr>
            <td style="text-align:left">
              <div style="font-weight:600;line-height:1.35"><?= htmlspecialchars(mb_strimwidth($p['title'],0,80,'…')) ?></div>
              <div style="font-size:11px;color:var(--text3);margin-top:2px"><?= htmlspecialchars($p['category']) ?> · <?= $p['is_manual'] ? 'Added by you' : 'Auto' ?> · <?= date('M j, g:i A', strtotime($p['published_at'])) ?></div>
            </td>
            <td style="text-align:center;font-size:12.5px"><?= htmlspecialchars($p['source_name'] ?: '—') ?></td>
            <td style="text-align:center">
              <?php if ($exp === null): ?><span class="nbadge keep">Permanent</span>
              <?php elseif ($soon): ?><span class="nbadge soon">Removes in <?= max(0,$exp) ?>d</span>
              <?php else: ?><span class="nbadge live"><?= $exp ?>d left</span><?php endif; ?>
            </td>
            <td style="text-align:right;white-space:nowrap">
              <button type="button" class="iconbtn" title="Edit" onclick='nwOpen(<?= json_encode($p, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'><?= svgIcon('doc',14) ?></button>
              <button type="button" class="iconbtn del" title="Delete" onclick="nwDelete(<?= (int)$p['id'] ?>)">&times;</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<!-- Add / edit modal -->
<div id="nw-modal" class="modal-overlay" style="display:none">
  <div class="modal" style="max-width:600px">
    <div class="modal-head"><h3 class="modal-title" id="nw-title">Add news post</h3><button class="modal-close" onclick="nwClose()">&times;</button></div>
    <div class="modal-body">
      <div id="nw-form-result"></div>
      <div class="fg"><label class="fl">Headline <span style="color:var(--red)">*</span></label><input class="fi" id="nw-f-title"/></div>
      <div class="fg"><label class="fl">Summary</label><textarea class="fi" id="nw-f-summary" rows="4" style="resize:vertical"></textarea></div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem">
        <div class="fg"><label class="fl">Source name</label><input class="fi" id="nw-f-source_name" placeholder="e.g. Bloomberg"/></div>
        <div class="fg"><label class="fl">Region</label>
          <select class="fi" id="nw-f-category">
            <?php foreach (['Global','Middle East','Europe','Asia','United States','Africa','Australia'] as $c): ?><option><?= $c ?></option><?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="fg"><label class="fl">Source URL <span class="fl-opt">(the "Read full story" link)</span></label><input class="fi" id="nw-f-source_url" placeholder="https://…"/></div>
      <div class="fg" style="max-width:260px"><label class="fl">Auto-remove after</label>
        <select class="fi" id="nw-f-expiry_days">
          <option value="7">7 days (default)</option><option value="3">3 days</option><option value="14">14 days</option><option value="30">30 days</option><option value="0">Never (permanent)</option>
        </select>
      </div>
      <div style="display:flex;gap:.65rem;margin-top:1rem">
        <button type="button" class="btn btn-primary" id="nw-save" onclick="nwSave()">Save post</button>
        <button type="button" class="btn btn-outline" onclick="nwClose()">Cancel</button>
      </div>
    </div>
  </div>
</div>

<style>
  .tbl th{font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:#9CA3AF;font-weight:600;padding:12px 16px;border-bottom:1px solid #F0F2F7}
  .tbl td{font-size:13px;color:#374151;padding:12px 16px;border-bottom:1px solid #F5F6F8;vertical-align:middle}
  .tbl tbody tr:last-child td{border-bottom:none}
  .nbadge{font-size:10px;font-weight:700;padding:2px 9px;border-radius:99px;text-transform:uppercase;letter-spacing:.03em}
  .nbadge.live{background:#E7F3EC;color:#0f7a4a}.nbadge.soon{background:#FEF3C7;color:#B45309}.nbadge.keep{background:#EEF2F7;color:#5B6472}
  .iconbtn{width:32px;height:32px;border-radius:8px;border:1px solid var(--border);background:var(--surface);color:var(--text3);cursor:pointer;font-size:18px;line-height:1;vertical-align:middle}
  .iconbtn.del:hover{color:#C0392B;border-color:#f0c4c0}
</style>

<script>
const nwF = ['title','summary','source_name','category','source_url','expiry_days'];
let nwId = 0;
function nwOpen(p){
  document.getElementById('nw-form-result').innerHTML='';
  if (p && typeof p==='object'){ nwId=p.id; document.getElementById('nw-title').textContent='Edit news post';
    nwF.forEach(f=>{ const el=document.getElementById('nw-f-'+f); if(el) el.value = f==='expiry_days' ? '7' : (p[f]??''); });
    document.getElementById('nw-f-category').value = p.category || 'Global';
  } else { nwId=0; document.getElementById('nw-title').textContent='Add news post';
    nwF.forEach(f=>{ const el=document.getElementById('nw-f-'+f); if(el) el.value=''; });
    document.getElementById('nw-f-category').value='Global'; document.getElementById('nw-f-expiry_days').value='7';
  }
  document.getElementById('nw-modal').style.display='flex';
}
function nwClose(){ document.getElementById('nw-modal').style.display='none'; }
async function nwSave(){
  const btn=document.getElementById('nw-save'); const data={};
  nwF.forEach(f=>data[f]=document.getElementById('nw-f-'+f).value);
  if(!data.title.trim()){ document.getElementById('nw-form-result').innerHTML='<div class="alert alert-err">A headline is required.</div>'; return; }
  setLoading(btn,true,'Saving…');
  const res=await post(nwId?('/admin/news/'+nwId):'/admin/news', data);
  setLoading(btn,false);
  if(res.success) location.reload(); else document.getElementById('nw-form-result').innerHTML='<div class="alert alert-err">'+(res.error||'Failed.')+'</div>';
}
async function nwDelete(id){
  if(!confirm('Delete this post? This cannot be undone.')) return;
  const res=await post('/admin/news/'+id+'/delete',{});
  if(res.success) location.reload();
}
document.getElementById('nw-save-settings').addEventListener('click', async function(){
  setLoading(this,true,'Saving…');
  const res=await post('/admin/news/settings',{news_rss_url:document.getElementById('nw-url').value.trim(),news_enabled:document.getElementById('nw-enabled').checked?'1':'0'});
  setLoading(this,false);
  document.getElementById('nw-alert').innerHTML=res.success?'<div class="alert alert-ok">'+res.message+'</div>':'<div class="alert alert-err">'+(res.error||'Failed.')+'</div>';
  window.scrollTo({top:0,behavior:'smooth'});
});
document.getElementById('nw-fetch').addEventListener('click', async function(){
  setLoading(this,true,'Fetching…');
  const res=await post('/admin/news/fetch',{});
  setLoading(this,false);
  document.getElementById('nw-alert').innerHTML=res.success?'<div class="alert alert-ok">'+res.message+'</div>':'<div class="alert alert-err">'+(res.error||'Failed.')+'</div>';
  if(res.success && /Pulled/.test(res.message)) setTimeout(()=>location.reload(),1200);
  window.scrollTo({top:0,behavior:'smooth'});
});
</script>
