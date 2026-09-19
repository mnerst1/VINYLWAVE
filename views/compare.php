<?php
$title = 'Сравнение товаров — VINYLWAVE';
require __DIR__ . '/partials/header.php';
?>

<section class="py-6">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-3xl font-black tracking-tight">⇄ Сравнение товаров</h1>
      <p class="text-xs text-zinc-400 mt-1">Характеристики выбранных релизов рядом — до 4 товаров</p>
    </div>
    <a href="index.php" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs font-bold text-white hover:bg-white/10">← В каталог</a>
  </div>

  <!-- Populated by JS from localStorage compare list -->
  <div id="compareTableWrap" class="overflow-x-auto rounded-3xl border border-white/10 bg-[#121216] hidden"></div>

  <script>
  (async function () {
    let ids = [];
    try { ids = JSON.parse(localStorage.getItem('vw-compare') || '[]'); } catch (e) {}
    const emptyBox = document.querySelector('#compareEmpty');
    const wrap = document.querySelector('#compareTableWrap');
    if (!ids.length) return;

    try {
      const res = await fetch('index.php?page=recent-api&ids=' + ids.join(','));
      const products = await res.json();
      if (!Array.isArray(products) || !products.length) return;
      emptyBox.classList.add('hidden');
      wrap.classList.remove('hidden');

      const rows = [
        { label: 'Обложка', render: p => `<img src="${p.cover_thumb_url || p.cover_url}" class="h-28 w-28 rounded-2xl object-cover mx-auto">` },
        { label: 'Название', render: p => `<a href="index.php?page=product&id=${p.id}" class="font-bold text-white hover:text-lime-300">${p.name}</a>` },
        { label: 'Артист', render: p => p.artist_name },
        { label: 'Категория', render: p => `<span class="rounded-full bg-white/10 px-2.5 py-0.5 text-[10px] uppercase font-bold text-lime-300">${p.category}</span>` },
        { label: 'Цена', render: p => `<span class="font-mono font-black text-lime-300">$${parseFloat(p.price).toFixed(2)}</span>` },
        { label: 'Жанр', render: p => p.genre || '—' },
        { label: 'Цвет прессинга', render: p => p.color_variant || '—' },
        { label: 'Наличие', render: p => p.stock > 0 ? `<span class="text-emerald-400">В наличии (${p.stock})</span>` : '<span class="text-red-400">Распродано</span>' },
        { label: 'Рейтинг', render: p => `<span class="text-amber-400">★</span> <span class="font-mono text-zinc-300">${parseFloat(p.avg_rating || 5).toFixed(1)}</span>` },
        { label: '', render: p => `<button class="compare-remove rounded-xl border border-red-500/20 bg-red-500/10 px-3 py-1.5 text-[11px] font-bold text-red-400 hover:bg-red-500/20" data-id="${p.id}">Убрать</button>` }
      ];

      wrap.innerHTML = `<table class="w-full text-xs"><thead><tr>
        <th class="p-3 text-left text-[11px] uppercase text-zinc-500">Характеристика</th>
        ${products.map(p => `<th class="p-3 min-w-[180px]"><img src="${p.cover_thumb_url || p.cover_url}" class="h-16 w-16 rounded-xl object-cover mx-auto mb-2"><div class="text-white text-[11px] font-bold">${p.name}</div></th>`).join('')}
      </tr></thead><tbody>
        ${rows.map(r => `<tr class="border-t border-white/5">
          <td class="p-3 text-[11px] uppercase tracking-wider text-zinc-500 font-bold">${r.label}</td>
          ${products.map(p => `<td class="p-3 text-center text-zinc-200">${r.render(p)}</td>`).join('')}
        </tr>`).join('')}
      </tbody></table>`;

      wrap.addEventListener('click', (e) => {
        const btn = e.target.closest('.compare-remove');
        if (!btn) return;
        const pid = parseInt(btn.dataset.id, 10);
        ids = ids.filter(x => x !== pid);
        localStorage.setItem('vw-compare', JSON.stringify(ids));
        location.reload();
      });

      // Sync header badge
      const badge = document.querySelector('#headerCompareBadge');
      if (badge) badge.textContent = ids.length;
    } catch (e) {}
  })();
  </script>

  <div id="compareEmpty" class="rounded-3xl border border-white/5 bg-[#121216]/50 p-16 text-center">
    <div class="text-4xl mb-3">⇄</div>
    <h3 class="text-lg font-bold text-white">Нечего сравнивать</h3>
    <p class="text-xs text-zinc-500 mt-1 max-w-sm mx-auto">Добавьте товары кнопкой ⇄ на карточке в каталоге — они появятся здесь для сравнения характеристик.</p>
    <a href="index.php" class="mt-4 inline-block rounded-xl bg-white/10 px-5 py-2 text-xs font-bold text-white hover:bg-white/20">Перейти в каталог</a>
  </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
