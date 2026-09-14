<div class="nw-page-hd">
  <div class="eyebrow">Global real estate</div>
  <h1 class="serif">Market News</h1>
  <p>The world's top real-estate headlines, refreshed daily.</p>
</div>

<?php if (empty($posts)): ?>
  <div class="nw-empty">No news right now — fresh headlines land here every day.</div>
<?php else:
  $lead = $posts[0];
  $rest = array_slice($posts, 1);
?>
  <a class="nw-feature" href="/investor/news/<?= (int)$lead['id'] ?>">
    <div class="<?= news_photo_class($lead) ?>"><?= news_photo_inner($lead) ?><span class="nw-chip" style="position:absolute;left:14px;top:14px"><?= htmlspecialchars($lead['category']) ?></span></div>
    <div class="fb">
      <span class="nw-chip solid">Top story</span>
      <h3><?= htmlspecialchars($lead['title']) ?></h3>
      <?php if (!empty($lead['summary'])): ?><p><?= htmlspecialchars(mb_strimwidth(preg_replace('/\s+/', ' ', $lead['summary']), 0, 180, '…')) ?></p><?php endif; ?>
      <div class="m"><?= news_source($lead) ?><span class="nw-dot"></span><span class="nw-time"><?= time_ago($lead['published_at']) ?></span></div>
    </div>
  </a>

  <?php if ($rest): ?>
  <div class="nw-grid">
    <?php foreach ($rest as $p): ?>
      <a class="nw-acard" href="/investor/news/<?= (int)$p['id'] ?>">
        <div class="<?= news_photo_class($p) ?>" style="height:140px"><?= news_photo_inner($p) ?><span class="nw-chip" style="position:absolute;left:9px;top:9px"><?= htmlspecialchars($p['category']) ?></span></div>
        <div class="ab">
          <h4><?= htmlspecialchars($p['title']) ?></h4>
          <div class="m"><?= news_source($p) ?><span class="nw-dot"></span><span class="nw-time"><?= time_ago($p['published_at']) ?></span></div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
<?php endif; ?>
