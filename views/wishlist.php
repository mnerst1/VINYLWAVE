<?php
$title = 'Избранное — VINYLWAVE';
require __DIR__ . '/partials/header.php';
?>

<section class="py-6">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-3xl font-black tracking-tight">♥ Избранное</h1>
      <p class="text-xs text-zinc-400 mt-1">
        <?=$currentUser ? 'Товары, сохраненные в вашем аккаунте' : 'Гостевое избранное хранится в этом браузере — войдите, чтобы сохранить его навсегда'?>
      </p>
    </div>
    <a href="index.php" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs font-bold text-white hover:bg-white/10">← В каталог</a>
  </div>

  <?php if (!$currentUser): ?>
    <!-- Guest wishlist: rendered client-side from localStorage -->
    <div id="guestWishlistGrid" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3"></div>
    <div id="guestWishlistEmpty" class="rounded-3xl border border-white/5 bg-[#121216]/50 p-16 text-center">
      <div class="text-4xl mb-3">♥</div>
      <h3 class="text-lg font-bold text-white">В избранном пока пусто</h3>
      <p class="text-xs text-zinc-500 mt-1">Нажимайте на ♥ на карточках товаров, чтобы добавить их сюда.</p>
      <a href="index.php" class="mt-4 inline-block rounded-xl bg-white/10 px-5 py-2 text-xs font-bold text-white hover:bg-white/20">Перейти в каталог</a>
    </div>
    <noscript>
      <p class="text-xs text-zinc-500">Для гостевого избранного требуется включенный JavaScript. <a href="index.php?page=login" class="text-lime-300 underline">Войдите</a>, чтобы использовать синхронизированное избранное.</p>
    </noscript>
    <script>
    (async function () {
      let ids = [];
      try { ids = JSON.parse(localStorage.getItem('vw-wishlist') || '[]'); } catch (e) {}
      const emptyBox = document.querySelector('#guestWishlistEmpty');
      if (!ids.length) return;
      try {
        const res = await fetch('index.php?page=recent-api&ids=' + ids.join(','));
        const products = await res.json();
        const grid = document.querySelector('#guestWishlistGrid');
        emptyBox.classList.add('hidden');
        grid.innerHTML = products.map(p => `
          <article class="product-card group relative rounded-3xl border border-white/10 bg-[#121216] p-3.5 shadow-xl glow-card flex flex-col justify-between" data-product-id="${p.id}">
            <div>
              <a href="index.php?page=product&id=${p.id}" class="block">
                <div class="record-stage relative mb-4 aspect-square overflow-hidden rounded-2xl bg-zinc-950">
                  <img src="${p.cover_thumb_url || p.cover_url}" class="sleeve absolute inset-0 z-10 h-full w-full object-cover rounded-2xl" loading="lazy" alt="">
                  <div class="record"></div>
                  <span class="absolute right-3 top-3 z-20 rounded-full bg-black/70 border border-white/10 px-2.5 py-0.5 text-[10px] font-bold uppercase text-white">${p.category}</span>
                </div>
              </a>
              <div class="px-2">
                <div class="mb-1 flex items-start justify-between gap-3">
                  <h2 class="font-bold text-sm text-white line-clamp-1"><a href="index.php?page=product&id=${p.id}">${p.name}</a></h2>
                  <span class="font-mono font-black text-sm text-lime-300">$${parseFloat(p.price).toFixed(2)}</span>
                </div>
                <div class="text-xs text-zinc-400">${p.artist_name}</div>
              </div>
            </div>
            <div class="mt-4 flex gap-2 px-2 pb-1">
              <a href="index.php?page=product&id=${p.id}" class="flex-1 rounded-xl bg-white py-2.5 text-center text-xs font-bold text-black hover:bg-lime-300">Подробнее</a>
              <button type="button" class="wishlist-btn rounded-xl border border-red-400/60 bg-red-500/20 px-3.5 text-xs text-red-300" data-product-id="${p.id}" data-active="1" title="Убрать из избранного">♥</button>
            </div>
          </article>
        `).join('');
        window.bindWishlistButtons && window.bindWishlistButtons(grid);
        grid.addEventListener('click', async (e) => {
          const btn = e.target.closest('.wishlist-btn');
          if (!btn) return;
          const pid = parseInt(btn.dataset.productId, 10);
          let ids2 = [];
          try { ids2 = JSON.parse(localStorage.getItem('vw-wishlist') || '[]'); } catch (err) {}
          ids2 = ids2.filter(x => x !== pid);
          localStorage.setItem('vw-wishlist', JSON.stringify(ids2));
          btn.closest('article')?.remove();
          if (!grid.children.length) emptyBox.classList.remove('hidden');
          const badge = document.querySelector('#headerWishlistBadge');
          if (badge) badge.textContent = ids2.length;
          window.showToast && window.showToast('Удалено из избранного', 'info');
        });
      } catch (e) {}
    })();
    </script>
  <?php elseif (empty($items)): ?>
    <div class="rounded-3xl border border-white/5 bg-[#121216]/50 p-16 text-center">
      <div class="text-4xl mb-3">♥</div>
      <h3 class="text-lg font-bold text-white">В избранном пока пусто</h3>
      <p class="text-xs text-zinc-500 mt-1">Нажимайте на ♥ на карточках товаров, чтобы добавить их сюда.</p>
      <a href="index.php" class="mt-4 inline-block rounded-xl bg-lime-300 px-5 py-2.5 text-xs font-black text-black hover:bg-lime-400">Перейти в каталог</a>
    </div>
  <?php else: ?>
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <?php foreach ($items as $p): ?>
        <?php $p['id'] = (int)$p['id']; include __DIR__ . '/partials/product-card.php'; ?>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
