<?php
$expLabel = '';
if (!empty($post['expires_at'])) {
    $days = (int) ceil((strtotime($post['expires_at']) - time()) / 86400);
    if ($days > 0) $expLabel = 'Available for ' . $days . ' more day' . ($days === 1 ? '' : 's');
}
?>
<article class="nw-article">
  <a class="nw-back" href="/investor/news"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>Back to Market News</a>

  <div class="<?= news_photo_class($post) ?> nw-hero"><?= news_photo_inner($post) ?><span class="nw-chip"><?= htmlspecialchars($post['category']) ?></span></div>

  <div class="nw-meta">
    <?= news_source($post) ?>
    <span class="nw-dot"></span>
    <span class="nw-time"><?= fmt_date($post['published_at']) ?> · <?= time_ago($post['published_at']) ?></span>
    <?php if ($expLabel): ?>
      <span class="nw-expire"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg><?= htmlspecialchars($expLabel) ?></span>
    <?php endif; ?>
  </div>

  <h1><?= htmlspecialchars($post['title']) ?></h1>

  <div class="nw-body">
    <?php if (!empty($post['summary'])):
      foreach (preg_split('/\n\n+/', trim($post['summary'])) as $para):
        if (trim($para) === '') continue; ?>
        <p><?= nl2br(htmlspecialchars($para)) ?></p>
    <?php endforeach; else: ?>
      <p>Read the full story at the original source below.</p>
    <?php endif; ?>
  </div>

  <?php if (!empty($post['source_url'])): ?>
    <div class="nw-cta">
      <div class="l">This is a summary. <b>Read the full story</b> at the original source.</div>
      <a class="nw-btn" href="<?= htmlspecialchars($post['source_url']) ?>" target="_blank" rel="noopener noreferrer">
        Read full story<?= !empty($post['source_name']) ? ' at ' . htmlspecialchars($post['source_name']) : '' ?>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17L17 7M7 7h10v10"/></svg>
      </a>
    </div>
  <?php endif; ?>

  <?php if (!empty($more)): ?>
    <div style="margin-top:34px">
      <div class="nw-page-hd" style="margin-bottom:14px"><div class="eyebrow">Keep reading</div></div>
      <div class="nw-grid">
        <?php foreach (array_slice($more, 0, 3) as $p): ?>
          <a class="nw-acard" href="/investor/news/<?= (int)$p['id'] ?>">
            <div class="<?= news_photo_class($p) ?>" style="height:120px"><?= news_photo_inner($p) ?><span class="nw-chip" style="position:absolute;left:9px;top:9px"><?= htmlspecialchars($p['category']) ?></span></div>
            <div class="ab"><h4><?= htmlspecialchars($p['title']) ?></h4><div class="m"><?= news_source($p) ?></div></div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</article>
