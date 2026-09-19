<?php
$title = 'Панель администратора — VINYLWAVE';
require __DIR__ . '/../partials/header.php';
$activeTab = $tab ?? 'dashboard';
?>

<div class="py-6">
  <!-- Admin Header Bar -->
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-white/10 pb-6 mb-6">
    <div>
      <div class="flex items-center gap-2">
        <span class="inline-block h-3 w-3 rounded-full bg-lime-400 animate-pulse shadow-[0_0_10px_rgba(185,255,44,0.8)]"></span>
        <h1 class="text-3xl font-black tracking-tight">Панель управления</h1>
        <span class="rounded-full bg-lime-400/20 px-2.5 py-0.5 text-[11px] font-mono font-bold text-lime-300">ADMIN CONTROL</span>
      </div>
      <p class="text-xs text-zinc-400 mt-1">Добавление артистов, альбомов, синглов, DVD, мерча, загрузка аудио-превью, видео и модерация</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="index.php?page=admin&tab=product-edit" class="rounded-xl bg-lime-300 px-4 py-2.5 text-xs font-black text-black hover:bg-lime-400 hover:shadow-[0_0_20px_rgba(185,255,44,0.4)] flex items-center gap-1.5">
        <span>+</span> Добавить релиз / товар
      </a>
      <a href="index.php?page=admin&tab=artists" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs font-bold text-white hover:bg-white/10">
        + Артист
      </a>
      <a href="index.php" class="rounded-xl border border-white/10 px-3 py-2.5 text-xs text-zinc-400 hover:text-white" title="На главную магазина">
        Витрина ↗
      </a>
    </div>
  </div>

  <!-- Admin Tabs Navigation -->
  <div class="flex overflow-x-auto gap-2 border-b border-white/5 pb-3 mb-8 scrollbar-none text-xs font-bold uppercase tracking-wider">
    <a href="index.php?page=admin&tab=dashboard" class="whitespace-nowrap rounded-xl px-4 py-2.5 transition-all <?=$activeTab==='dashboard'?'bg-lime-400 text-black shadow-lg font-black':'bg-white/5 text-zinc-400 hover:text-white'?>">
      📊 Обзор
    </a>
    <a href="index.php?page=admin&tab=products" class="whitespace-nowrap rounded-xl px-4 py-2.5 transition-all <?=$activeTab==='products'?'bg-lime-400 text-black shadow-lg font-black':'bg-white/5 text-zinc-400 hover:text-white'?>">
      💿 Товары (<?=$stats['products']?>)
    </a>
    <a href="index.php?page=admin&tab=product-edit" class="whitespace-nowrap rounded-xl px-4 py-2.5 transition-all <?=$activeTab==='product-edit'?'bg-lime-400 text-black shadow-lg font-black':'bg-white/5 text-zinc-400 hover:text-white'?>">
      ✏ <?=$editProduct ? 'Редактировать товар' : '+ Новый товар'?>
    </a>
    <a href="index.php?page=admin&tab=artists" class="whitespace-nowrap rounded-xl px-4 py-2.5 transition-all <?=$activeTab==='artists'?'bg-lime-400 text-black shadow-lg font-black':'bg-white/5 text-zinc-400 hover:text-white'?>">
      🎤 Артисты (<?=$stats['artists']?>)
    </a>
    <a href="index.php?page=admin&tab=reviews" class="whitespace-nowrap rounded-xl px-4 py-2.5 transition-all <?=$activeTab==='reviews'?'bg-lime-400 text-black shadow-lg font-black':'bg-white/5 text-zinc-400 hover:text-white'?>">
      💬 Отзывы (<?=$stats['reviews']?>)
    </a>
    <a href="index.php?page=admin&tab=moderation" class="whitespace-nowrap rounded-xl px-4 py-2.5 transition-all <?=$activeTab==='moderation'?'bg-lime-400 text-black shadow-lg font-black':'bg-white/5 text-zinc-400 hover:text-white'?>">
      🕐 Модерация <?php if ($pendingReviewsCount > 0): ?><span class="ml-1 inline-flex items-center justify-center rounded-full bg-red-500 px-2 py-0.5 text-[10px] font-black text-white"><?=$pendingReviewsCount?></span><?php endif; ?>
    </a>
    <a href="index.php?page=admin&tab=orders" class="whitespace-nowrap rounded-xl px-4 py-2.5 transition-all <?=$activeTab==='orders'?'bg-lime-400 text-black shadow-lg font-black':'bg-white/5 text-zinc-400 hover:text-white'?>">
      📦 Заказы (<?=$stats['orders']?>)
    </a>
    <a href="index.php?page=admin&tab=users" class="whitespace-nowrap rounded-xl px-4 py-2.5 transition-all <?=$activeTab==='users'?'bg-lime-400 text-black shadow-lg font-black':'bg-white/5 text-zinc-400 hover:text-white'?>">
      👥 Пользователи
    </a>
    <a href="index.php?page=admin&tab=reports" class="whitespace-nowrap rounded-xl px-4 py-2.5 transition-all <?=$activeTab==='reports'?'bg-lime-400 text-black shadow-lg font-black':'bg-white/5 text-zinc-400 hover:text-white'?>">
      📈 Отчеты
    </a>
    <a href="index.php?page=admin&tab=import" class="whitespace-nowrap rounded-xl px-4 py-2.5 transition-all <?=$activeTab==='import'?'bg-lime-400 text-black shadow-lg font-black':'bg-white/5 text-zinc-400 hover:text-white'?>">
      📥 Импорт/Экспорт
    </a>
  </div>

  <!-- TAB 1: DASHBOARD OVERVIEW -->
  <?php if ($activeTab === 'dashboard'): ?>
    <div class="space-y-8">
      <!-- KPI Stats Grid -->
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-3xl border border-white/10 bg-[#121216] p-6 glow-card">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Всего релизов</span>
            <span class="text-lg">💿</span>
          </div>
          <div class="mt-4 text-3xl font-black text-white"><?=$stats['products']?></div>
          <div class="mt-2 text-xs text-zinc-400">Винилы, синглы, DVD и мерч</div>
        </div>

        <div class="rounded-3xl border border-white/10 bg-[#121216] p-6 glow-card">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Выручка</span>
            <span class="text-lg">💰</span>
          </div>
          <div class="mt-4 text-3xl font-black font-mono text-lime-300">$<?=number_format($stats['revenue'], 2)?></div>
          <div class="mt-2 text-xs text-zinc-400">По оплаченным заказам</div>
        </div>

        <div class="rounded-3xl border border-white/10 bg-[#121216] p-6 glow-card">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Заказов</span>
            <span class="text-lg">📦</span>
          </div>
          <div class="mt-4 text-3xl font-black text-white"><?=$stats['orders']?></div>
          <div class="mt-2 text-xs text-zinc-400">Покупки клиентов и гостей</div>
        </div>

        <div class="rounded-3xl border border-white/10 bg-[#121216] p-6 glow-card">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Отзывов с фото</span>
            <span class="text-lg">💬</span>
          </div>
          <div class="mt-4 text-3xl font-black text-white"><?=$stats['reviews']?></div>
          <div class="mt-2 text-xs text-zinc-400">Оставлено покупателями</div>
        </div>
      </div>

      <!-- Charts (Chart.js) -->
      <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-3xl border border-white/10 bg-[#121216] p-6 lg:col-span-2">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-black">Выручка и заказы — 30 дней</h3>
            <span class="rounded-full bg-lime-400/10 px-2.5 py-0.5 text-[10px] font-mono text-lime-300">REVENUE / ORDERS</span>
          </div>
          <div style="height:320px"><canvas id="chartRevenue"></canvas></div>
        </div>
        <div class="rounded-3xl border border-white/10 bg-[#121216] p-6">
          <h3 class="text-lg font-black mb-4">Выручка по категориям</h3>
          <div style="height:320px"><canvas id="chartCategory"></canvas></div>
        </div>
      </div>

      <div class="rounded-3xl border border-white/10 bg-[#121216] p-6">
        <h3 class="text-lg font-black mb-4">Топ товаров по выручке</h3>
        <div style="height:280px"><canvas id="chartTopProducts"></canvas></div>
      </div>

      <!-- Alerts and Recent Activity -->
      <div class="grid gap-6 lg:grid-cols-2">
        <!-- Low Stock Warning -->
        <div class="rounded-3xl border border-white/10 bg-[#121216] p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-black">Внимание: Остаток на складе</h3>
            <span class="rounded-full bg-amber-500/10 px-2 py-0.5 text-xs text-amber-400 font-bold">≤ 5 шт.</span>
          </div>
          <div class="space-y-3">
            <?php 
              $lowStockItems = array_filter($products, fn($p) => (int)$p['stock'] <= 5);
              if (empty($lowStockItems)): 
            ?>
              <div class="text-xs text-zinc-500 py-4">Все товары в достаточном количестве на складе.</div>
            <?php else: foreach ($lowStockItems as $p): ?>
              <div class="flex items-center justify-between rounded-xl border border-white/5 bg-white/[0.02] p-3 text-xs">
                <div class="flex items-center gap-3">
                  <img src="<?=htmlspecialchars($p['cover_url'])?>" class="h-10 w-10 rounded-lg object-cover">
                  <div>
                    <div class="font-bold text-white"><?=htmlspecialchars($p['name'])?></div>
                    <div class="text-[11px] text-zinc-500"><?=htmlspecialchars($p['artist_name'])?> · <?=htmlspecialchars($p['category'])?></div>
                  </div>
                </div>
                <div class="text-right">
                  <span class="rounded-full px-2 py-0.5 text-[10px] font-mono font-bold <?= (int)$p['stock'] === 0 ? 'bg-red-500/20 text-red-400' : 'bg-amber-500/20 text-amber-400' ?>">
                    <?= (int)$p['stock'] === 0 ? 'Распродано' : $p['stock'] . ' шт.' ?>
                  </span>
                  <a href="index.php?page=admin&tab=product-edit&edit_product_id=<?=$p['id']?>" class="block text-[10px] text-lime-300 mt-1 hover:underline">Пополнить</a>
                </div>
              </div>
            <?php endforeach; endif; ?>
          </div>
        </div>

        <!-- Quick Tips & Media Overview -->
        <div class="rounded-3xl border border-white/10 bg-[#121216] p-6">
          <h3 class="text-lg font-black mb-2">Обновление информации в реальном времени</h3>
          <p class="text-xs text-zinc-400 leading-relaxed mb-4">
            Когда вы добавляете или обновляете артиста, альбом, сингл, DVD или мерч, клиенты и гости моментально видят обновленный контент на витрине:
          </p>
          <ul class="space-y-2.5 text-xs text-zinc-300">
            <li class="flex items-start gap-2">
              <span class="text-lime-300 font-bold">✓</span>
              <span><strong>Загрузка фото и обложек:</strong> файлы сохраняются локально или принимаются прямые ссылки.</span>
            </li>
            <li class="flex items-start gap-2">
              <span class="text-lime-300 font-bold">✓</span>
              <span><strong>Видеоклипы:</strong> вставьте ссылку на YouTube или видеофайл — у товара появится кнопка просмотра клипа.</span>
            </li>
            <li class="flex items-start gap-2">
              <span class="text-lime-300 font-bold">✓</span>
              <span><strong>Аудио-превью:</strong> прикрепляйте 15-секундные MP3 сэмплы к каждому треку альбома или сингла.</span>
            </li>
            <li class="flex items-start gap-2">
              <span class="text-lime-300 font-bold">✓</span>
              <span><strong>Отзывы клиентов:</strong> клиенты оставляют отзывы и прикрепляют фото распаковки, доступен модераторский контроль.</span>
            </li>
          </ul>
        </div>
      </div>
      <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
      <script>
      (function () {
        if (typeof Chart === 'undefined') return;
        const grid = 'rgba(255,255,255,0.06)';
        const text = '#a1a1aa';
        Chart.defaults.color = text;
        Chart.defaults.font.family = 'Space Grotesk, sans-serif';

        // Revenue + orders over time
        const revEl = document.getElementById('chartRevenue');
        if (revEl) {
          const labels = <?=json_encode(array_map(fn($d) => date('d.m', strtotime($d['day'])), $chartDaily))?>;
          const revenue = <?=json_encode(array_map(fn($d) => round((float)$d['revenue'], 2), $chartDaily))?>;
          const orders = <?=json_encode(array_map(fn($d) => (int)$d['orders'], $chartDaily))?>;
          new Chart(revEl, {
            type: 'line',
            data: { labels, datasets: [
              { label: 'Выручка, $', data: revenue, borderColor: '#b9ff2c', backgroundColor: 'rgba(185,255,44,0.12)', fill: true, tension: 0.35, pointRadius: 2, yAxisID: 'y' },
              { label: 'Заказы', data: orders, borderColor: '#38bdf8', backgroundColor: 'rgba(56,189,248,0.10)', fill: true, tension: 0.35, pointRadius: 2, yAxisID: 'y1' }
            ]},
            options: { maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
              scales: {
                x: { grid: { color: grid } },
                y: { position: 'left', grid: { color: grid }, title: { display: true, text: '$' } },
                y1: { position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'заказы' } }
              }
            }
          });
        }

        // Revenue by category
        const catEl = document.getElementById('chartCategory');
        if (catEl) {
          const data = <?=json_encode(array_map(fn($c) => round((float)$c['revenue'], 2), $chartCategory))?>;
          const labels = <?=json_encode(array_map(fn($c) => $c['category'], $chartCategory))?>;
          new Chart(catEl, {
            type: 'doughnut',
            data: { labels, datasets: [{ data, backgroundColor: ['#b9ff2c', '#38bdf8', '#f472b6', '#fbbf24', '#a78bfa'], borderWidth: 0 }] },
            options: { maintainAspectRatio: false, cutout: '62%', plugins: { legend: { position: 'bottom' } } }
          });
        }

        // Top products (horizontal bars)
        const topEl = document.getElementById('chartTopProducts');
        if (topEl) {
          const rows = <?=json_encode($chartTop)?: '[]'?>;
          new Chart(topEl, {
            type: 'bar',
            data: { labels: rows.map(r => r.name), datasets: [{ label: 'Выручка, $', data: rows.map(r => Math.round(parseFloat(r.revenue))), backgroundColor: '#b9ff2c', borderRadius: 6 }] },
            options: { indexAxis: 'y', maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { color: grid } }, y: { grid: { display: false } } } }
          });
        }
      })();
      </script>
    </div>
  <?php endif; ?>

  <!-- TAB 2: PRODUCTS LIST -->
  <?php if ($activeTab === 'products'): ?>
    <div class="rounded-3xl border border-white/10 bg-[#121216] p-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
          <h2 class="text-xl font-black">Каталог товаров</h2>
          <p class="text-xs text-zinc-400">Управление винилами, синглами, DVD и мерчем</p>
        </div>
        <div class="flex gap-2">
          <a href="index.php?page=admin-products-export" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs font-bold text-white hover:bg-white/10 text-center">⬇ Экспорт CSV</a>
          <a href="index.php?page=admin&tab=import" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs font-bold text-white hover:bg-white/10 text-center">⬆ Импорт</a>
          <a href="index.php?page=admin&tab=product-edit" class="rounded-xl bg-lime-300 px-4 py-2 text-xs font-black text-black hover:bg-lime-400 text-center">+ Добавить релиз</a>
        </div>
      </div>

      <!-- Bulk actions bar (submitted via JS to avoid nested forms) -->
      <div id="bulkForm" data-csrf="<?=htmlspecialchars(Csrf::getToken())?>">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-4 rounded-2xl border border-white/5 bg-white/[0.02] p-3">
          <span class="text-[11px] uppercase tracking-wider font-bold text-zinc-400">Выбрано: <span id="bulkCount" class="text-lime-300">0</span></span>
          <select name="bulk_action" class="filter text-xs py-1.5">
            <option value="">— действие —</option>
            <option value="activate">Показать (активировать)</option>
            <option value="deactivate">Скрыть из каталога</option>
            <option value="stock">Установить остаток</option>
            <option value="delete">Удалить выбранные</option>
          </select>
          <input type="number" name="bulk_stock" min="0" placeholder="0" class="filter text-xs py-1.5 w-20" title="Значение для действия «Установить остаток»">
          <button type="button" id="bulkApplyBtn" class="rounded-xl bg-white px-4 py-1.5 text-xs font-bold text-black hover:bg-lime-300">Применить</button>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="border-b border-white/10 text-[11px] uppercase tracking-wider text-zinc-500">
              <tr>
                <th class="py-3 px-3"><input type="checkbox" id="checkAllProducts" class="h-4 w-4 rounded accent-lime-400"></th>
                <th class="py-3 px-3">Обложка</th>
              <th class="py-3 px-3">Артист</th>
              <th class="py-3 px-3">Категория</th>
              <th class="py-3 px-3">Цена</th>
              <th class="py-3 px-3">Склад</th>
              <th class="py-3 px-3">Медиа</th>
              <th class="py-3 px-3 text-right">Действия</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <?php foreach ($products as $p): ?>
              <tr class="hover:bg-white/[0.02] transition-colors <?=empty($p['is_active']) ? 'opacity-50' : ''?>">
                <td class="py-3 px-3">
                  <input type="checkbox" name="product_ids[]" value="<?=$p['id']?>" class="bulk-check h-4 w-4 rounded accent-lime-400">
                </td>
                <td class="py-3 px-3">
                  <img src="<?=htmlspecialchars($p['cover_url'])?>" class="h-12 w-12 rounded-xl object-cover border border-white/10">
                </td>
                <td class="py-3 px-3">
                  <a href="index.php?page=product&id=<?=$p['id']?>" target="_blank" class="font-bold text-white hover:text-lime-300">
                    <?=htmlspecialchars($p['name'])?>
                  </a>
                  <?php if (!empty($p['color_variant'])): ?>
                    <div class="text-[10px] text-zinc-500 font-mono"><?=htmlspecialchars($p['color_variant'])?></div>
                  <?php endif; ?>
                </td>
                <td class="py-3 px-3 text-zinc-300"><?=htmlspecialchars($p['artist_name'])?></td>
                <td class="py-3 px-3">
                  <span class="rounded-full bg-white/10 px-2.5 py-0.5 text-[10px] uppercase font-bold text-lime-300">
                    <?=htmlspecialchars($p['category'])?>
                  </span>
                </td>
                <td class="py-3 px-3 font-mono font-bold text-white">$<?=number_format((float)$p['price'], 2)?></td>
                <td class="py-3 px-3">
                  <span class="rounded-full px-2 py-0.5 text-[10px] font-mono font-bold <?= (int)$p['stock'] > 5 ? 'bg-emerald-500/15 text-emerald-400' : ((int)$p['stock'] > 0 ? 'bg-amber-500/15 text-amber-400' : 'bg-red-500/15 text-red-400') ?>">
                    <?=$p['stock']?> шт.
                  </span>
                </td>
                <td class="py-3 px-3 text-[11px] text-zinc-400">
                  <div class="flex items-center gap-1.5">
                    <?php if (!empty($p['video_url'])): ?>
                      <span class="text-lime-300" title="Есть видео-клип">🎬</span>
                    <?php endif; ?>
                    <?php if ($p['is_limited']): ?>
                      <span class="text-amber-300" title="Limited Drop">⏳</span>
                    <?php endif; ?>
                    <?php if (empty($p['is_active'])): ?>
                      <span class="rounded bg-zinc-800 px-1.5 py-0.5 text-[9px] font-bold uppercase text-zinc-400" title="Скрыт из каталога">Hidden</span>
                    <?php endif; ?>
                  </div>
                </td>
                <td class="py-3 px-3 text-right">
                  <div class="inline-flex items-center gap-2">
                    <a href="index.php?page=admin&tab=product-edit&edit_product_id=<?=$p['id']?>" class="rounded-lg border border-white/10 bg-white/5 px-2.5 py-1 text-[11px] font-bold text-zinc-300 hover:text-white hover:bg-white/10">
                      Изменить
                    </a>
                    <form method="post" action="index.php?page=admin-product-delete" onsubmit="return confirm('Удалить товар «<?=htmlspecialchars(addslashes($p['name']))?>» из каталога?');" class="inline">
                      <input type="hidden" name="product_id" value="<?=$p['id']?>">
                      <?=Csrf::input()?>
                      <button type="submit" class="rounded-lg border border-red-500/20 bg-red-500/10 px-2.5 py-1 text-[11px] font-bold text-red-400 hover:bg-red-500/20">
                        ✕
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          </table>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- TAB 3: PRODUCT ADD / EDIT FORM -->
  <?php if ($activeTab === 'product-edit'): ?>
    <div class="rounded-3xl border border-white/10 bg-[#121216] p-6 sm:p-8 max-w-4xl mx-auto shadow-2xl">
      <div class="flex items-center justify-between border-b border-white/10 pb-4 mb-6">
        <div>
          <h2 class="text-2xl font-black">
            <?=$editProduct ? 'Редактирование: ' . htmlspecialchars($editProduct['name']) : 'Добавление нового релиза / товара'?>
          </h2>
          <p class="text-xs text-zinc-400">Винил, сингл, CD, DVD или мерч с загрузкой медиа и аудио-превью</p>
        </div>
        <a href="index.php?page=admin&tab=products" class="text-xs text-zinc-400 hover:text-white">← Назад к списку</a>
      </div>

      <form method="post" action="index.php?page=admin-product-save" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" name="product_id" value="<?=$editProduct['id'] ?? 0?>">
        <?=Csrf::input()?>

        <div class="grid gap-4 sm:grid-cols-2">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1.5">Название релиза / товара *</label>
            <input type="text" name="name" required value="<?=htmlspecialchars($editProduct['name'] ?? '')?>" placeholder="Например: UTOPIA — Collector Edition" class="filter w-full text-sm">
          </div>
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1.5">Артист *</label>
            <select name="artist_id" required class="filter w-full text-sm">
              <option value="">Выберите артиста</option>
              <?php foreach ($artists as $a): ?>
                <option value="<?=$a['id']?>" <?=($editProduct['artist_id'] ?? 0) == $a['id'] ? 'selected' : ''?>>
                  <?=htmlspecialchars($a['name'])?> (<?=htmlspecialchars($a['genre'])?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1.5">Категория *</label>
            <select name="category" required class="filter w-full text-sm">
              <?php foreach (['vinyl' => 'Винил (Vinyl)', 'single' => 'Сингл (7"/12" Single)', 'dvd' => 'Концертный DVD', 'merch' => 'Официальный мерч', 'cd' => 'Компакт-диск (CD)'] as $catKey => $catLabel): ?>
                <option value="<?=$catKey?>" <?=($editProduct['category'] ?? 'vinyl') === $catKey ? 'selected' : ''?>>
                  <?=$catLabel?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1.5">Цена ($) *</label>
            <input type="number" step="0.01" min="0" name="price" required value="<?=$editProduct['price'] ?? '39.99'?>" class="filter w-full text-sm font-mono">
          </div>
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1.5">Количество на складе *</label>
            <input type="number" min="0" name="stock" required value="<?=$editProduct['stock'] ?? 20?>" class="filter w-full text-sm font-mono">
          </div>
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1.5">Цвет винила / Вариант</label>
            <input type="text" name="color_variant" value="<?=htmlspecialchars($editProduct['color_variant'] ?? '')?>" placeholder="Splatter / Clear / Black" class="filter w-full text-sm">
          </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1.5">Жанр музыки</label>
            <input type="text" name="genre" value="<?=htmlspecialchars($editProduct['genre'] ?? 'Hip-Hop')?>" placeholder="Hip-Hop / R&B / Rock" class="filter w-full text-sm">
          </div>
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1.5">Видео-превью / Клип (YouTube или MP4 URL)</label>
            <input type="url" name="video_url" value="<?=htmlspecialchars($editProduct['video_url'] ?? '')?>" placeholder="https://www.youtube.com/watch?v=..." class="filter w-full text-sm">
            <span class="text-[10px] text-zinc-500">Клиент сможет посмотреть клип или видео распаковки прямо на странице товара.</span>
            <div class="mt-2">
              <span class="text-[11px] text-zinc-400 block mb-1">Или загрузите MP4-файл (превью-обложка сгенерируется через ffmpeg, если он установлен):</span>
              <input type="file" name="video_file" accept="video/mp4" class="text-xs text-zinc-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-white/10 file:text-white">
            </div>
          </div>
        </div>

        <!-- Cover Image Upload & Preview -->
        <div class="rounded-2xl border border-white/5 bg-white/[0.02] p-4">
          <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-2">Обложка товара (Загрузка файла или URL)</label>
          <div class="grid gap-4 sm:grid-cols-[120px_1fr] items-center">
            <div id="coverPreviewContainer" class="h-28 w-28 rounded-2xl border border-white/10 bg-zinc-900 overflow-hidden flex items-center justify-center">
              <img id="coverPreview" src="<?=htmlspecialchars($editProduct['cover_url'] ?? 'https://images.unsplash.com/photo-1539375665275-f9de415ef9ac?auto=format&fit=crop&w=400&q=80')?>" class="h-full w-full object-cover">
            </div>
            <div class="space-y-3">
              <div>
                <span class="text-xs text-zinc-400 block mb-1">Загрузить файл обложки с диска:</span>
                <input type="file" name="cover" accept="image/*" data-preview-target="#coverPreview" class="text-xs text-zinc-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-lime-400 file:text-black hover:file:bg-lime-300">
              </div>
              <div>
                <span class="text-xs text-zinc-400 block mb-1">Или указать прямой URL изображения:</span>
                <input type="url" name="cover_url" value="<?=htmlspecialchars($editProduct['cover_url'] ?? '')?>" placeholder="https://..." class="filter w-full text-xs">
              </div>
            </div>
          </div>
        </div>

        <!-- Limited Drop Section -->
        <div class="rounded-2xl border border-white/5 bg-white/[0.02] p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
          <label class="flex items-center gap-3 cursor-pointer">
            <input type="checkbox" name="is_limited" value="1" <?=!empty($editProduct['is_limited']) ? 'checked' : ''?> class="h-4 w-4 rounded accent-lime-400">
            <div>
              <span class="text-xs font-bold text-white uppercase tracking-wider block">Лимитированный дроп (Limited Drop)</span>
              <span class="text-[11px] text-zinc-500">Показывать бейдж и таймер обратного отсчета</span>
            </div>
          </label>
          <div class="flex items-center gap-2">
            <span class="text-xs text-zinc-400">Окончание:</span>
            <input type="datetime-local" name="drop_ends_at" value="<?=!empty($editProduct['drop_ends_at']) ? date('Y-m-d\TH:i', strtotime($editProduct['drop_ends_at'])) : ''?>" class="filter text-xs">
          </div>
        </div>

        <!-- Publication toggle -->
        <label class="rounded-2xl border border-white/5 bg-white/[0.02] p-4 flex items-center gap-3 cursor-pointer">
          <input type="checkbox" name="is_active" value="1" <?=($editProduct && empty($editProduct['is_active'])) ? '' : 'checked'?> class="h-4 w-4 rounded accent-lime-400">
          <div>
            <span class="text-xs font-bold text-white uppercase tracking-wider block">Товар опубликован</span>
            <span class="text-[11px] text-zinc-500">Скрытые товары не видны в каталоге (массовое скрытие через список товаров)</span>
          </div>
        </label>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1.5">Описание релиза</label>
          <textarea name="description" rows="3" placeholder="Подробная информация о прессинге, плотности винила, комплектации..." class="filter w-full text-sm leading-relaxed"><?=htmlspecialchars($editProduct['description'] ?? '')?></textarea>
        </div>

        <!-- Tracks & Audio Previews Builder -->
        <div class="border-t border-white/10 pt-6">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h3 class="text-base font-bold text-white uppercase tracking-wider">Треклист и аудио-превью (MP3)</h3>
              <p class="text-xs text-zinc-400">Добавьте треки с аудио-файлами для 15-секундного плеера</p>
            </div>
            <button type="button" onclick="addTrackRow()" class="rounded-xl border border-lime-400/40 bg-lime-400/10 px-3 py-1.5 text-xs font-bold text-lime-300 hover:bg-lime-400/20">
              + Добавить трек
            </button>
          </div>

          <div id="tracksContainer" class="space-y-3">
            <?php 
              $existingTracks = !empty($editTracks) ? $editTracks : [
                ['title' => 'Track 1 (Title)', 'preview_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3', 'duration_seconds' => 15]
              ];
              foreach ($existingTracks as $i => $track): 
            ?>
              <div class="track-row grid gap-2 sm:grid-cols-[24px_1fr_1fr_80px_56px] items-center rounded-xl border border-white/5 bg-white/[0.02] p-2.5 cursor-grab" draggable="true">
                <span class="track-drag-handle text-zinc-600 hover:text-lime-300 text-center text-sm" title="Перетащите для изменения порядка">⠿</span>
                <input type="text" name="track_titles[]" value="<?=htmlspecialchars($track['title'] ?? '')?>" placeholder="Название трека" class="filter text-xs">
                <div>
                  <input type="text" name="track_previews[]" value="<?=htmlspecialchars($track['preview_url'] ?? '')?>" placeholder="URL превью (SoundHelix или MP3)" class="filter w-full text-xs">
                  <div class="mt-1 flex items-center gap-1">
                    <span class="text-[10px] text-zinc-500">Файл MP3:</span>
                    <input type="file" name="track_files[]" accept="audio/*" class="text-[10px] text-zinc-500 file:py-0.5 file:px-2 file:rounded file:border-0 file:text-[10px] file:bg-white/10 file:text-zinc-200">
                  </div>
                </div>
                <input type="number" name="track_durations[]" value="<?=$track['duration_seconds'] ?? 15?>" placeholder="15s" class="filter text-xs font-mono text-center">
                <button type="button" onclick="this.closest('.track-row').remove()" class="h-8 w-8 rounded-lg text-zinc-500 hover:text-red-400 hover:bg-red-500/10 flex items-center justify-center text-sm" title="Удалить трек">
                  ✕
                </button>
              </div>
            <?php endforeach; ?>
          </div>
          <p class="text-[11px] text-zinc-500 mt-2">Порядок треков сохраняется как есть: перетаскивайте строки за handle ⠿, чтобы менять нумерацию.</p>
        </div>

        <div class="border-t border-white/10 pt-6 flex justify-end gap-3">
          <a href="index.php?page=admin&tab=products" class="rounded-xl border border-white/10 px-5 py-3 text-xs font-bold text-zinc-300 hover:bg-white/5">
            Отмена
          </a>
          <button type="submit" class="rounded-xl bg-lime-300 px-6 py-3 text-xs font-black text-black hover:bg-lime-400 hover:shadow-[0_0_20px_rgba(185,255,44,0.5)] transition-all">
            <?=$editProduct ? '✓ Сохранить изменения' : '✓ Добавить релиз в каталог'?>
          </button>
        </div>
      </form>
    </div>

    <script>
    function addTrackRow() {
      const container = document.querySelector('#tracksContainer');
      const row = document.createElement('div');
      row.className = 'track-row grid gap-2 sm:grid-cols-[24px_1fr_1fr_80px_56px] items-center rounded-xl border border-white/5 bg-white/[0.02] p-2.5 cursor-grab';
      row.draggable = true;
      row.innerHTML = `
        <span class="track-drag-handle text-zinc-600 hover:text-lime-300 text-center text-sm" title="Перетащите для изменения порядка">⠿</span>
        <input type="text" name="track_titles[]" placeholder="Название трека" class="filter text-xs">
        <div>
          <input type="text" name="track_previews[]" value="https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3" placeholder="URL превью" class="filter w-full text-xs">
          <div class="mt-1 flex items-center gap-1">
            <span class="text-[10px] text-zinc-500">Файл MP3:</span>
            <input type="file" name="track_files[]" accept="audio/*" class="text-[10px] text-zinc-500 file:py-0.5 file:px-2 file:rounded file:border-0 file:text-[10px] file:bg-white/10 file:text-zinc-200">
          </div>
        </div>
        <input type="number" name="track_durations[]" value="15" class="filter text-xs font-mono text-center">
        <button type="button" onclick="this.closest('.track-row').remove()" class="h-8 w-8 rounded-lg text-zinc-500 hover:text-red-400 hover:bg-red-500/10 flex items-center justify-center text-sm">✕</button>
      `;
      container.appendChild(row);
      window.vinylwaveInitDrag && window.vinylwaveInitDrag();
    }
    </script>
  <?php endif; ?>

  <!-- TAB 4: ARTISTS -->
  <?php if ($activeTab === 'artists'): ?>
    <div class="grid gap-8 lg:grid-cols-[380px_1fr]">
      <!-- Add / Edit Artist Card -->
      <div class="rounded-3xl border border-white/10 bg-[#121216] p-6 h-fit glow-card">
        <h2 class="text-xl font-black mb-1">
          <?=$editArtist ? 'Редактировать артиста' : 'Добавить артиста'?>
        </h2>
        <p class="text-xs text-zinc-400 mb-5">Управление карточками музыкантов и групп</p>

        <form method="post" action="index.php?page=admin-artist-save" enctype="multipart/form-data" class="space-y-4">
          <input type="hidden" name="artist_id" value="<?=$editArtist['id'] ?? 0?>">
          <?=Csrf::input()?>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1.5">Имя артиста *</label>
            <input type="text" name="name" required value="<?=htmlspecialchars($editArtist['name'] ?? '')?>" placeholder="Travis Scott" class="filter w-full text-sm">
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1.5">Основной жанр</label>
            <input type="text" name="genre" value="<?=htmlspecialchars($editArtist['genre'] ?? 'Hip-Hop')?>" placeholder="Hip-Hop / R&B" class="filter w-full text-sm">
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1.5">Фото артиста</label>
            <div class="h-28 w-28 rounded-2xl border border-white/10 bg-zinc-900 overflow-hidden mb-2">
              <img id="artistPreview" src="<?=htmlspecialchars($editArtist['image_url'] ?? 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?auto=format&fit=crop&w=400&q=80')?>" class="h-full w-full object-cover">
            </div>
            <input type="file" name="image" accept="image/*" data-preview-target="#artistPreview" class="text-xs text-zinc-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-white/10 file:text-white mb-2">
            <input type="url" name="image_url" value="<?=htmlspecialchars($editArtist['image_url'] ?? '')?>" placeholder="Или URL: https://..." class="filter w-full text-xs">
          </div>

          <button type="submit" class="w-full rounded-xl bg-lime-300 py-3 text-xs font-black text-black hover:bg-lime-400">
            <?=$editArtist ? 'Сохранить изменения' : '+ Добавить артиста'?>
          </button>
          <?php if ($editArtist): ?>
            <a href="index.php?page=admin&tab=artists" class="block text-center text-xs text-zinc-400 hover:text-white mt-2">Отмена</a>
          <?php endif; ?>
        </form>
      </div>

      <!-- Artists List Grid -->
      <div class="space-y-4">
        <h2 class="text-xl font-black">Список артистов в базе</h2>
        <div class="grid gap-4 sm:grid-cols-2">
          <?php foreach ($artists as $a): ?>
            <div class="rounded-2xl border border-white/10 bg-[#121216] p-4 flex items-center justify-between gap-4 glow-card">
              <div class="flex items-center gap-3">
                <img src="<?=htmlspecialchars($a['image_url'])?>" class="h-14 w-14 rounded-2xl object-cover border border-white/10">
                <div>
                  <h3 class="font-bold text-white text-sm"><?=htmlspecialchars($a['name'])?></h3>
                  <div class="text-xs text-lime-300"><?=htmlspecialchars($a['genre'])?></div>
                  <div class="text-[11px] text-zinc-500 mt-0.5"><?=$a['product_count'] ?? 0?> релизов в каталоге</div>
                </div>
              </div>
              <div class="flex flex-col gap-1.5">
                <a href="index.php?page=admin&tab=artists&edit_artist_id=<?=$a['id']?>" class="rounded-lg border border-white/10 bg-white/5 px-2.5 py-1 text-[11px] text-center font-bold text-zinc-300 hover:text-white">
                  Изменить
                </a>
                <form method="post" action="index.php?page=admin-artist-delete" onsubmit="return confirm('Удалить артиста «<?=htmlspecialchars(addslashes($a['name']))?>»? Все его релизы также будут удалены.');">
                  <input type="hidden" name="artist_id" value="<?=$a['id']?>">
                  <?=Csrf::input()?>
                  <button type="submit" class="w-full rounded-lg border border-red-500/20 bg-red-500/10 px-2.5 py-1 text-[11px] font-bold text-red-400 hover:bg-red-500/20">
                    Удалить
                  </button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- TAB 5: REVIEWS MODERATION -->
  <?php if ($activeTab === 'reviews'): ?>
    <div class="rounded-3xl border border-white/10 bg-[#121216] p-6">
      <div class="mb-6">
        <h2 class="text-xl font-black">Модерация отзывов клиентов</h2>
        <p class="text-xs text-zinc-400">Просмотр опубликованных отзывов, проверка прикрепленных фото покупателей и удаление нежелательных</p>
      </div>

      <?php if (empty($reviews)): ?>
        <div class="py-12 text-center text-zinc-500 text-xs">Отзывов пока нет.</div>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="border-b border-white/10 text-[11px] uppercase tracking-wider text-zinc-500">
              <tr>
                <th class="py-3 px-3">Товар</th>
                <th class="py-3 px-3">Покупатель</th>
                <th class="py-3 px-3">Оценка</th>
                <th class="py-3 px-3">Отзыв</th>
                <th class="py-3 px-3">Фото</th>
                <th class="py-3 px-3">Дата</th>
                <th class="py-3 px-3 text-right">Действие</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
              <?php foreach ($reviews as $rev): ?>
                <tr class="hover:bg-white/[0.02]">
                  <td class="py-3 px-3">
                    <div class="flex items-center gap-2 max-w-[200px]">
                      <img src="<?=htmlspecialchars($rev['cover_url'])?>" class="h-9 w-9 rounded-lg object-cover">
                      <span class="truncate font-semibold text-white"><?=htmlspecialchars($rev['product_name'])?></span>
                    </div>
                  </td>
                  <td class="py-3 px-3">
                    <div class="font-bold text-white"><?=htmlspecialchars($rev['user_name'])?></div>
                    <div class="text-[10px] text-zinc-500 font-mono"><?=htmlspecialchars($rev['user_email'])?></div>
                  </td>
                  <td class="py-3 px-3">
                    <span class="text-amber-400 font-bold"><?=str_repeat('★', (int)$rev['rating'])?></span>
                  </td>
                  <td class="py-3 px-3 max-w-sm">
                    <div class="font-semibold text-zinc-200"><?=htmlspecialchars($rev['title'])?></div>
                    <div class="text-[11px] text-zinc-400 line-clamp-2"><?=htmlspecialchars($rev['comment'])?></div>
                  </td>
                  <td class="py-3 px-3">
                    <?php if (!empty($rev['photo_url'])): ?>
                      <img src="<?=htmlspecialchars($rev['photo_url'])?>" class="review-photo-zoom h-12 w-12 rounded-xl object-cover cursor-pointer hover:scale-105 border border-white/10" title="Кликните для просмотра в полном размере">
                    <?php else: ?>
                      <span class="text-[10px] text-zinc-600">—</span>
                    <?php endif; ?>
                  </td>
                  <td class="py-3 px-3 text-[11px] text-zinc-500 whitespace-nowrap">
                    <?=date('d.m.Y H:i', strtotime($rev['created_at']))?>
                  </td>
                  <td class="py-3 px-3 text-right">
                    <form method="post" action="index.php?page=admin-review-delete" onsubmit="return confirm('Удалить этот отзыв?');">
                      <input type="hidden" name="review_id" value="<?=$rev['id']?>">
                      <?=Csrf::input()?>
                      <button type="submit" class="rounded-lg border border-red-500/20 bg-red-500/10 px-2.5 py-1 text-[11px] font-bold text-red-400 hover:bg-red-500/20">
                        Удалить
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- TAB 6: ORDERS -->
  <?php if ($activeTab === 'orders'): ?>
    <div class="rounded-3xl border border-white/10 bg-[#121216] p-6">
      <div class="mb-6">
        <h2 class="text-xl font-black">Управление заказами</h2>
        <p class="text-xs text-zinc-400">Просмотр заказов клиентов и гостей, изменение статуса доставки</p>
      </div>

      <?php if (empty($orders)): ?>
        <div class="py-12 text-center text-zinc-500 text-xs">Заказов пока не поступало.</div>
      <?php else: ?>
        <div class="space-y-4">
          <?php foreach ($orders as $ord): ?>
            <div class="rounded-2xl border border-white/10 bg-[#141419] p-5">
              <div class="flex flex-wrap items-center justify-between gap-4 border-b border-white/5 pb-4 mb-4">
                <div>
                  <div class="flex items-center gap-3">
                    <span class="font-mono font-bold text-white text-base">Заказ #<?=$ord['id']?></span>
                    <?php if (!empty($ord['user_account_name'])): ?>
                      <span class="rounded-full bg-lime-400/10 px-2 py-0.5 text-[10px] font-bold text-lime-300">
                        Клиент: <?=htmlspecialchars($ord['user_account_name'])?>
                      </span>
                    <?php else: ?>
                      <span class="rounded-full bg-zinc-800 px-2 py-0.5 text-[10px] text-zinc-400">
                        Гостевой заказ
                      </span>
                    <?php endif; ?>
                  </div>
                  <div class="text-xs text-zinc-400 mt-1">
                    Получатель: <strong class="text-zinc-200"><?=htmlspecialchars($ord['customer_name'])?></strong> (<?=htmlspecialchars($ord['email'])?>) · 
                    <?=htmlspecialchars($ord['city'])?>, <?=htmlspecialchars($ord['address'])?>
                  </div>
                </div>

                <!-- Status Update Form -->
                <form method="post" action="index.php?page=admin-order-status" class="flex items-center gap-2">
                  <input type="hidden" name="order_id" value="<?=$ord['id']?>">
                  <?=Csrf::input()?>
                  <select name="status" class="filter text-xs py-1.5">
                    <option value="pending" <?=$ord['status']==='pending'?'selected':''?>>Ожидает (Pending)</option>
                    <option value="paid" <?=$ord['status']==='paid'?'selected':''?>>Оплачен (Paid)</option>
                    <option value="shipped" <?=$ord['status']==='shipped'?'selected':''?>>Отправлен (Shipped)</option>
                    <option value="cancelled" <?=$ord['status']==='cancelled'?'selected':''?>>Отменен (Cancelled)</option>
                  </select>
                  <button type="submit" class="rounded-xl bg-white px-3 py-1.5 text-xs font-bold text-black hover:bg-lime-300">
                    OK
                  </button>
                </form>
              </div>

              <!-- Order Items List -->
              <div class="grid gap-2 sm:grid-cols-2">
                <?php foreach ($ord['items'] as $item): ?>
                  <div class="flex items-center justify-between text-xs rounded-xl bg-white/[0.02] p-2.5">
                    <div class="font-medium text-zinc-200">
                      <?=htmlspecialchars($item['product_name'])?>
                      <?php if (!empty($item['size'])): ?>
                        <span class="text-[10px] text-lime-300 font-mono ml-1">[<?=$item['size']?>]</span>
                      <?php endif; ?>
                      <span class="text-zinc-500">× <?=$item['quantity']?></span>
                    </div>
                    <div class="font-mono text-zinc-300">$<?=number_format((float)$item['unit_price'] * (int)$item['quantity'], 2)?></div>
                  </div>
                <?php endforeach; ?>
              </div>

              <div class="mt-4 flex items-center justify-between border-t border-white/5 pt-3 text-xs">
                <span class="text-zinc-500">Дата оформления: <?=date('d.m.Y H:i', strtotime($ord['created_at']))?></span>
                <div>
                  <span class="text-zinc-400 mr-2">Итого к оплате:</span>
                  <span class="font-mono font-black text-lime-300 text-sm">$<?=number_format((float)$ord['total'], 2)?></span>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- TAB 7: REVIEW MODERATION QUEUE -->
  <?php if ($activeTab === 'moderation'): ?>
    <div class="rounded-3xl border border-white/10 bg-[#121216] p-6">
      <div class="mb-6">
        <h2 class="text-xl font-black">Очередь модерации отзывов</h2>
        <p class="text-xs text-zinc-400">Новые отзывы попадают сюда и не видны покупателям, пока вы их не одобрите</p>
      </div>

      <?php if (empty($pendingReviews)): ?>
        <div class="py-12 text-center">
          <div class="text-4xl mb-2">✓</div>
          <div class="text-xs text-zinc-500">Очередь пуста — все отзывы обработаны.</div>
        </div>
      <?php else: ?>
        <form method="post" action="index.php?page=admin-review-bulk-moderate">
          <?=Csrf::input()?>
          <div class="flex items-center gap-2 mb-4">
            <button type="submit" name="decision" value="approved" class="rounded-xl bg-lime-300 px-4 py-1.5 text-xs font-black text-black hover:bg-lime-400">✓ Одобрить выбранные</button>
            <button type="submit" name="decision" value="rejected" class="rounded-xl border border-red-500/20 bg-red-500/10 px-4 py-1.5 text-xs font-bold text-red-400 hover:bg-red-500/20">✕ Отклонить выбранные</button>
          </div>
          <div class="space-y-3">
            <?php foreach ($pendingReviews as $rev): ?>
              <div class="rounded-2xl border border-amber-500/20 bg-[#141419] p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                  <div class="flex items-start gap-3 min-w-0">
                    <input type="checkbox" name="review_ids[]" value="<?=$rev['id']?>" class="mt-1 h-4 w-4 rounded accent-lime-400">
                    <img src="<?=htmlspecialchars($rev['cover_url'])?>" class="h-10 w-10 rounded-lg object-cover">
                    <div class="min-w-0">
                      <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-bold text-sm text-white"><?=htmlspecialchars($rev['title'])?></span>
                        <span class="text-amber-400 font-bold text-xs"><?=str_repeat('★', (int)$rev['rating'])?></span>
                      </div>
                      <div class="text-[11px] text-zinc-500 mb-1">
                        <?=htmlspecialchars($rev['product_name'])?> · <?=htmlspecialchars($rev['user_name'])?> (<?=htmlspecialchars($rev['user_email'])?>) · <?=date('d.m.Y H:i', strtotime($rev['created_at']))?>
                      </div>
                      <p class="text-xs text-zinc-300 leading-relaxed"><?=htmlspecialchars($rev['comment'])?></p>
                      <?php if (!empty($rev['photo_url'])): ?>
                        <img src="<?=htmlspecialchars($rev['photo_url'])?>" class="review-photo-zoom mt-2 h-14 w-14 rounded-xl object-cover cursor-pointer border border-white/10" title="Фото отзыва">
                      <?php endif; ?>
                    </div>
                  </div>
                  <div class="flex gap-2">
                    <form method="post" action="index.php?page=admin-review-moderate">
                      <input type="hidden" name="review_id" value="<?=$rev['id']?>">
                      <input type="hidden" name="decision" value="approved">
                      <?=Csrf::input()?>
                      <button type="submit" class="rounded-lg bg-lime-300 px-3 py-1 text-[11px] font-black text-black hover:bg-lime-400">✓ Одобрить</button>
                    </form>
                    <form method="post" action="index.php?page=admin-review-moderate">
                      <input type="hidden" name="review_id" value="<?=$rev['id']?>">
                      <input type="hidden" name="decision" value="rejected">
                      <?=Csrf::input()?>
                      <button type="submit" class="rounded-lg border border-red-500/20 bg-red-500/10 px-3 py-1 text-[11px] font-bold text-red-400 hover:bg-red-500/20">✕ Отклонить</button>
                    </form>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </form>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- TAB 8: USERS -->
  <?php if ($activeTab === 'users'): ?>
    <div class="rounded-3xl border border-white/10 bg-[#121216] p-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
          <h2 class="text-xl font-black">Пользователи</h2>
          <p class="text-xs text-zinc-400">Роли, блокировки и вход от имени пользователя</p>
        </div>
        <form method="get" action="index.php" class="flex gap-2">
          <input type="hidden" name="page" value="admin">
          <input type="hidden" name="tab" value="users">
          <input type="text" name="user_search" value="<?=htmlspecialchars($_GET['user_search'] ?? '')?>" placeholder="Поиск по имени или email..." class="filter text-xs w-56">
          <button type="submit" class="rounded-xl bg-white/10 px-3 py-1.5 text-xs font-bold text-white hover:bg-white/20">Найти</button>
        </form>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="border-b border-white/10 text-[11px] uppercase tracking-wider text-zinc-500">
            <tr>
              <th class="py-3 px-3">Пользователь</th>
              <th class="py-3 px-3">Роль</th>
              <th class="py-3 px-3">Заказы</th>
              <th class="py-3 px-3">Потрачено</th>
              <th class="py-3 px-3">Статус</th>
              <th class="py-3 px-3">Регистрация</th>
              <th class="py-3 px-3 text-right">Действия</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <?php foreach ($users as $u): ?>
              <tr class="hover:bg-white/[0.02] <?=$u['is_banned'] ? 'opacity-60' : ''?>">
                <td class="py-3 px-3">
                  <div class="font-bold text-white"><?=htmlspecialchars($u['name'])?></div>
                  <div class="text-[10px] text-zinc-500 font-mono"><?=htmlspecialchars($u['email'])?></div>
                </td>
                <td class="py-3 px-3">
                  <form method="post" action="index.php?page=admin-user-role" class="flex items-center gap-1.5">
                    <input type="hidden" name="user_id" value="<?=$u['id']?>">
                    <?=Csrf::input()?>
                    <select name="role" class="filter text-xs py-1" <?=((int)$u['id'] === (int)User::current()['id']) ? 'disabled' : ''?>>
                      <option value="customer" <?=$u['role']==='customer'?'selected':''?>>Покупатель</option>
                      <option value="moderator" <?=$u['role']==='moderator'?'selected':''?>>Модератор</option>
                      <option value="admin" <?=$u['role']==='admin'?'selected':''?>>Админ</option>
                    </select>
                    <button type="submit" class="rounded bg-white/10 px-2 py-1 text-[10px] font-bold text-white hover:bg-white/20" <?=((int)$u['id'] === (int)User::current()['id']) ? 'hidden' : ''?>>OK</button>
                  </form>
                </td>
                <td class="py-3 px-3 font-mono text-zinc-300"><?=$u['orders_count']?></td>
                <td class="py-3 px-3 font-mono text-lime-300">$<?=number_format((float)$u['total_spent'], 2)?></td>
                <td class="py-3 px-3">
                  <?php if (!empty($u['is_banned'])): ?>
                    <span class="rounded-full bg-red-500/15 px-2 py-0.5 text-[10px] font-bold text-red-400" title="<?=htmlspecialchars($u['ban_reason'] ?? '')?>">Заблокирован</span>
                  <?php else: ?>
                    <span class="rounded-full bg-emerald-500/15 px-2 py-0.5 text-[10px] font-bold text-emerald-400">Активен</span>
                  <?php endif; ?>
                </td>
                <td class="py-3 px-3 text-[11px] text-zinc-500"><?=date('d.m.Y', strtotime($u['created_at']))?></td>
                <td class="py-3 px-3 text-right">
                  <?php if ((int)$u['id'] !== (int)User::current()['id']): ?>
                    <div class="inline-flex items-center gap-1.5">
                      <form method="post" action="index.php?page=admin-user-impersonate">
                        <input type="hidden" name="user_id" value="<?=$u['id']?>">
                        <?=Csrf::input()?>
                        <button type="submit" class="rounded-lg border border-sky-500/20 bg-sky-500/10 px-2.5 py-1 text-[11px] font-bold text-sky-300 hover:bg-sky-500/20" title="Войти от имени пользователя">Войти</button>
                      </form>
                      <?php if (empty($u['is_banned'])): ?>
                        <form method="post" action="index.php?page=admin-user-ban" onsubmit="const r = prompt('Причина блокировки (необязательно):'); if (r === null) return false; this.querySelector('[name=reason]').value = r;">
                          <input type="hidden" name="user_id" value="<?=$u['id']?>">
                          <input type="hidden" name="ban" value="1">
                          <input type="hidden" name="reason" value="">
                          <?=Csrf::input()?>
                          <button type="submit" class="rounded-lg border border-amber-500/20 bg-amber-500/10 px-2.5 py-1 text-[11px] font-bold text-amber-400 hover:bg-amber-500/20">Бан</button>
                        </form>
                      <?php else: ?>
                        <form method="post" action="index.php?page=admin-user-ban">
                          <input type="hidden" name="user_id" value="<?=$u['id']?>">
                          <input type="hidden" name="ban" value="0">
                          <?=Csrf::input()?>
                          <button type="submit" class="rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-1 text-[11px] font-bold text-emerald-400 hover:bg-emerald-500/20">Разбан</button>
                        </form>
                      <?php endif; ?>
                    </div>
                  <?php else: ?>
                    <span class="text-[10px] text-zinc-600">это вы</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>

  <!-- TAB 9: SALES REPORTS -->
  <?php if ($activeTab === 'reports'): ?>
    <div class="space-y-6">
      <div class="rounded-3xl border border-white/10 bg-[#121216] p-6">
        <h2 class="text-xl font-black mb-4">Отчет по продажам</h2>
        <form method="get" action="index.php" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6 items-end">
          <input type="hidden" name="page" value="admin">
          <input type="hidden" name="tab" value="reports">
          <div>
            <label class="block text-[10px] font-bold uppercase tracking-wider text-zinc-500 mb-1">С даты</label>
            <input type="date" name="report_from" value="<?=htmlspecialchars($reportFrom)?>" class="filter w-full text-xs">
          </div>
          <div>
            <label class="block text-[10px] font-bold uppercase tracking-wider text-zinc-500 mb-1">По дату</label>
            <input type="date" name="report_to" value="<?=htmlspecialchars($reportTo)?>" class="filter w-full text-xs">
          </div>
          <div>
            <label class="block text-[10px] font-bold uppercase tracking-wider text-zinc-500 mb-1">Категория</label>
            <select name="report_category" class="filter w-full text-xs">
              <option value="">Все</option>
              <?php foreach (['vinyl','single','cd','dvd','merch'] as $cat): ?>
                <option value="<?=$cat?>" <?=$reportCategory===$cat?'selected':''?>><?=$cat?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="block text-[10px] font-bold uppercase tracking-wider text-zinc-500 mb-1">Артист</label>
            <select name="report_artist" class="filter w-full text-xs">
              <option value="0">Все</option>
              <?php foreach ($artists as $a): ?>
                <option value="<?=$a['id']?>" <?=$reportArtist===(int)$a['id']?'selected':''?>><?=htmlspecialchars($a['name'])?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="rounded-xl bg-lime-300 px-4 py-2.5 text-xs font-black text-black hover:bg-lime-400">Построить</button>
          <a href="index.php?page=admin-report-export&<?=htmlspecialchars(http_build_query(['report_from' => $reportFrom, 'report_to' => $reportTo, 'report_category' => $reportCategory, 'report_artist' => $reportArtist]))?>" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs font-bold text-white hover:bg-white/10 text-center">⬇ CSV</a>
        </form>
      </div>

      <!-- Summary cards -->
      <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-3xl border border-white/10 bg-[#121216] p-6 glow-card">
          <div class="text-xs font-bold uppercase tracking-wider text-zinc-500">Выручка за период</div>
          <div class="mt-3 text-3xl font-black font-mono text-lime-300">$<?=number_format((float)$report['summary']['revenue'], 2)?></div>
        </div>
        <div class="rounded-3xl border border-white/10 bg-[#121216] p-6 glow-card">
          <div class="text-xs font-bold uppercase tracking-wider text-zinc-500">Заказов</div>
          <div class="mt-3 text-3xl font-black text-white"><?=$report['summary']['orders']?></div>
        </div>
        <div class="rounded-3xl border border-white/10 bg-[#121216] p-6 glow-card">
          <div class="text-xs font-bold uppercase tracking-wider text-zinc-500">Единиц продано</div>
          <div class="mt-3 text-3xl font-black text-white"><?=$report['summary']['units']?></div>
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-3">
        <!-- By category -->
        <div class="rounded-3xl border border-white/10 bg-[#121216] p-6">
          <h3 class="text-sm font-black uppercase tracking-wider mb-4">По категориям</h3>
          <div class="space-y-2 text-xs">
            <?php foreach ($report['by_category'] as $c): ?>
              <div class="flex items-center justify-between rounded-xl bg-white/[0.02] px-3 py-2">
                <span class="font-bold text-white uppercase"><?=htmlspecialchars($c['category'])?></span>
                <span class="text-zinc-400"><?=$c['units']?> шт.</span>
                <span class="font-mono text-lime-300">$<?=number_format((float)$c['revenue'], 2)?></span>
              </div>
            <?php endforeach; ?>
            <?php if (empty($report['by_category'])): ?><div class="text-zinc-600">Нет данных за период.</div><?php endif; ?>
          </div>
        </div>

        <!-- By artist -->
        <div class="rounded-3xl border border-white/10 bg-[#121216] p-6">
          <h3 class="text-sm font-black uppercase tracking-wider mb-4">По артистам</h3>
          <div class="space-y-2 text-xs">
            <?php foreach ($report['by_artist'] as $a): ?>
              <div class="flex items-center justify-between rounded-xl bg-white/[0.02] px-3 py-2">
                <span class="font-bold text-white truncate max-w-[140px]"><?=htmlspecialchars($a['artist'])?></span>
                <span class="text-zinc-400"><?=$a['units']?> шт.</span>
                <span class="font-mono text-lime-300">$<?=number_format((float)$a['revenue'], 2)?></span>
              </div>
            <?php endforeach; ?>
            <?php if (empty($report['by_artist'])): ?><div class="text-zinc-600">Нет данных за период.</div><?php endif; ?>
          </div>
        </div>

        <!-- Top products -->
        <div class="rounded-3xl border border-white/10 bg-[#121216] p-6">
          <h3 class="text-sm font-black uppercase tracking-wider mb-4">Топ-10 товаров</h3>
          <div class="space-y-2 text-xs">
            <?php foreach ($report['top_products'] as $i => $tp): ?>
              <div class="flex items-center justify-between rounded-xl bg-white/[0.02] px-3 py-2">
                <span class="flex items-center gap-2 min-w-0">
                  <span class="font-mono text-zinc-600">#<?=$i+1?></span>
                  <span class="font-bold text-white truncate max-w-[140px]"><?=htmlspecialchars($tp['name'])?></span>
                </span>
                <span class="text-zinc-400"><?=$tp['units']?> шт.</span>
                <span class="font-mono text-lime-300">$<?=number_format((float)$tp['revenue'], 2)?></span>
              </div>
            <?php endforeach; ?>
            <?php if (empty($report['top_products'])): ?><div class="text-zinc-600">Нет данных за период.</div><?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- TAB 10: IMPORT / EXPORT -->
  <?php if ($activeTab === 'import'): ?>
    <div class="grid gap-6 lg:grid-cols-2">
      <div class="rounded-3xl border border-white/10 bg-[#121216] p-6">
        <h2 class="text-xl font-black mb-2">Импорт товаров (CSV)</h2>
        <p class="text-xs text-zinc-400 mb-4">Загрузите CSV с колонками: name, artist, category, price, stock, genre, color_variant, description, cover_url, is_limited, is_active. Существующие товары (совпадение по названию + артисту) будут обновлены, новые — добавлены. Артисты создаются автоматически.</p>
        <form method="post" action="index.php?page=admin-products-import" enctype="multipart/form-data" class="space-y-4">
          <?=Csrf::input()?>
          <input type="file" name="csv_file" accept=".csv,text/csv" required class="text-xs text-zinc-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-lime-400 file:text-black hover:file:bg-lime-300">
          <div>
            <button type="submit" class="rounded-xl bg-lime-300 px-6 py-3 text-xs font-black text-black hover:bg-lime-400">⬆ Импортировать</button>
          </div>
        </form>
      </div>

      <div class="rounded-3xl border border-white/10 bg-[#121216] p-6">
        <h2 class="text-xl font-black mb-2">Экспорт</h2>
        <p class="text-xs text-zinc-400 mb-4">Выгрузите весь каталог в CSV для редактирования в Excel / Google Sheets (поддерживается импорт обратно).</p>
        <a href="index.php?page=admin-products-export" class="inline-block rounded-xl bg-white px-6 py-3 text-xs font-black text-black hover:bg-lime-300">⬇ Скачать CSV каталога</a>

        <div class="mt-8 rounded-2xl border border-white/5 bg-white/[0.02] p-4">
          <h3 class="text-xs font-black uppercase tracking-wider text-zinc-300 mb-2">Пример строки CSV</h3>
          <code class="block text-[10px] text-zinc-400 leading-relaxed break-all">UTOPIA Collector Edition,Travis Scott,vinyl,59.99,20,Hip-Hop,Red Splatter,Описание...,https://...,1,1</code>
        </div>
      </div>
    </div>
  <?php endif; ?>

</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
