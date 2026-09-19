<?php
/**
 * Reusable product card (catalog grid, load-more, wishlist page).
 * Expects: $p (product row), optional $wishlistIds (array<int>), $currentUser.
 */
$cardStock = (int)$p['stock'];
$cardRating = (float)($p['avg_rating'] ?? 5.0);
$cardRevCount = (int)($p['reviews_count'] ?? 0);
$cardId = (int)$p['id'];

$cardPreview = '';
if (!empty($p['tracks'])) {
    $cardPreview = $p['tracks'][0]['preview_url'] ?? '';
}
$cardPreview = $cardPreview ?: 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-' . (($cardId % 8) + 1) . '.mp3';

$inUserWishlist = isset($wishlistIds) && in_array($cardId, $wishlistIds, true);
$coverImg = !empty($p['cover_thumb_url']) ? $p['cover_thumb_url'] : $p['cover_url'];
?>
<article class="product-card group relative rounded-3xl border border-white/10 bg-[#121216] p-3.5 shadow-xl glow-card flex flex-col justify-between"
         data-product-id="<?=$cardId?>"
         data-preview="<?=htmlspecialchars($cardPreview)?>"
         data-title="<?=htmlspecialchars($p['name'])?>"
         data-artist="<?=htmlspecialchars($p['artist_name'])?>"
         data-cover="<?=htmlspecialchars($coverImg)?>">

  <div>
    <a href="index.php?page=product&id=<?=$cardId?>" class="block">
      <div class="record-stage relative mb-4 aspect-square overflow-hidden rounded-2xl bg-zinc-950">
        <img src="<?=htmlspecialchars($coverImg)?>" class="sleeve absolute inset-0 z-10 h-full w-full object-cover rounded-2xl" alt="<?=htmlspecialchars($p['name'])?>" loading="lazy">
        <div class="record"></div>

        <?php if ($p['is_limited']): ?>
          <span class="absolute left-3 top-3 z-20 rounded-full bg-lime-300 px-3 py-1 text-[10px] font-black uppercase text-black shadow-md shimmer-badge">
            Limited Drop
          </span>
        <?php endif; ?>

        <span class="absolute right-3 top-3 z-20 rounded-full bg-black/70 backdrop-blur-md border border-white/10 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">
          <?=htmlspecialchars($p['category'])?>
        </span>

        <?php if (!empty($p['video_url'])): ?>
          <span class="absolute right-3 bottom-3 z-20 flex h-7 w-7 items-center justify-center rounded-full bg-black/80 text-lime-300 border border-lime-400/30 text-xs shadow-lg" title="Есть видео-клип">🎬</span>
        <?php endif; ?>

        <!-- Wishlist & Compare buttons -->
        <div class="absolute left-3 bottom-3 z-20 flex gap-1.5">
          <button type="button"
                  class="wishlist-btn flex h-8 w-8 items-center justify-center rounded-full border <?=$inUserWishlist ? 'border-red-400/60 bg-red-500/20 text-red-300' : 'border-white/15 bg-black/70 text-zinc-300'?> backdrop-blur-md text-sm transition-all hover:scale-110"
                  data-product-id="<?=$cardId?>"
                  data-active="<?=$inUserWishlist ? '1' : '0'?>"
                  title="В избранное (Wishlist)">♥</button>
          <button type="button"
                  class="compare-btn flex h-8 w-8 items-center justify-center rounded-full border border-white/15 bg-black/70 backdrop-blur-md text-sm text-zinc-300 transition-all hover:scale-110"
                  data-product-id="<?=$cardId?>"
                  data-product-name="<?=htmlspecialchars($p['name'])?>"
                  title="Сравнить">⇄</button>
        </div>
      </div>
    </a>

    <div class="px-2">
      <div class="mb-1 flex items-start justify-between gap-3">
        <h2 class="font-bold text-sm text-white group-hover:text-lime-300 transition-colors line-clamp-1">
          <a href="index.php?page=product&id=<?=$cardId?>"><?=htmlspecialchars($p['name'])?></a>
        </h2>
        <span class="font-mono font-black text-sm text-lime-300 flex-shrink-0">
          $<?=number_format((float)$p['price'], 2)?>
        </span>
      </div>

      <div class="flex items-center justify-between text-xs text-zinc-400 mt-1">
        <span><?=htmlspecialchars($p['artist_name'])?></span>
        <?php if (!empty($p['color_variant'])): ?>
          <span class="text-[10px] font-mono text-zinc-500 truncate max-w-[120px]"><?=htmlspecialchars($p['color_variant'])?></span>
        <?php endif; ?>
      </div>

      <div class="mt-3 flex items-center justify-between text-[11px]">
        <div class="flex items-center gap-1 text-amber-400">
          <span>★</span>
          <span class="font-mono font-bold text-zinc-300"><?=number_format($cardRating, 1)?></span>
          <?php if ($cardRevCount > 0): ?>
            <span class="text-zinc-500">(<?=$cardRevCount?>)</span>
          <?php endif; ?>
        </div>

        <div class="flex items-center gap-1.5">
          <?php if ($cardStock > 5): ?>
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
            <span class="text-emerald-400 text-[10px]">В наличии</span>
          <?php elseif ($cardStock > 0): ?>
            <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
            <span class="text-amber-400 text-[10px]"><?=$cardStock?> шт.</span>
          <?php else: ?>
            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
            <span class="text-red-400 text-[10px]">Распродано</span>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="mt-4 flex gap-2 px-2 pb-1">
    <a href="index.php?page=product&id=<?=$cardId?>" class="flex-1 rounded-xl bg-white py-2.5 text-center text-xs font-bold text-black hover:bg-lime-300 transition-colors">
      Подробнее
    </a>
    <?php if ($currentUser && $currentUser['role'] === 'admin'): ?>
      <a href="index.php?page=admin&tab=product-edit&edit_product_id=<?=$cardId?>" class="rounded-xl border border-lime-400/40 bg-lime-400/10 px-3 py-2.5 text-xs font-bold text-lime-300 hover:bg-lime-400/20 flex items-center gap-1" title="Изменить товар">
        <span>✏</span>
        <span class="hidden sm:inline">Изменить</span>
      </a>
    <?php endif; ?>
    <button type="button" class="preview-btn rounded-xl border border-white/10 bg-white/5 px-3.5 text-xs text-zinc-300 hover:border-lime-300/40 hover:text-lime-300 flex items-center gap-1"
            data-preview="<?=htmlspecialchars($cardPreview)?>"
            data-title="<?=htmlspecialchars($p['name'])?>"
            data-artist="<?=htmlspecialchars($p['artist_name'])?>"
            data-cover="<?=htmlspecialchars($coverImg)?>"
            title="Слушать 15-сек превью">
      ▶ 15s
    </button>
  </div>
</article>
