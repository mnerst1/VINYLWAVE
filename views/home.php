<?php 
$title = 'Культовый винил, мерч, синглы и DVD — VINYLWAVE'; 
require __DIR__ . '/partials/header.php'; 

$currentCategory = $_GET['category'] ?? '';
$searchQuery = $_GET['q'] ?? '';
$onlyInStock = !empty($_GET['in_stock']);
?>

<!-- Hero Banner -->
<section class="py-8">
  <div>
    <div class="inline-flex items-center gap-2 rounded-full border border-lime-400/30 bg-lime-400/10 px-3 py-1 text-[11px] font-black uppercase tracking-[.25em] text-lime-300 mb-4">
      <span class="h-1.5 w-1.5 rounded-full bg-lime-400 animate-pulse"></span>
      Physical culture & Rare pressings
    </div>
    <h1 class="text-4xl sm:text-7xl font-black tracking-[-.05em] leading-none text-white">
      Звук на виниле.<br>
      <span class="text-zinc-500">Культура ночи.</span>
    </h1>
    <p class="mt-4 max-w-xl text-sm text-zinc-400 leading-relaxed">
      Эксклюзивные виниловые издания, 7-дюймовые синглы, коллекционные DVD и официальный дроп-мерч современных артистов.
    </p>
  </div>
</section>

<!-- Recently Viewed Strip (localStorage) -->
<section id="recently-viewed-section" class="hidden mb-6">
  <div class="flex items-center justify-between mb-2">
    <h2 class="text-xs font-bold uppercase tracking-widest text-zinc-400">👀 Вы недавно смотрели</h2>
    <button type="button" id="recentClearBtn" class="text-[11px] text-zinc-600 hover:text-red-400">Очистить</button>
  </div>
  <div id="recentlyViewedRow" class="flex gap-3 overflow-x-auto pb-2 scrollbar-none"></div>
</section>

<!-- Advanced Filter Bar -->
<form method="get" action="index.php" class="mb-8 rounded-3xl border border-white/10 bg-[#121216] p-4 shadow-xl">
  <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
    
    <!-- Search Query Input -->
    <div>
      <input type="text" name="q" value="<?=htmlspecialchars($searchQuery)?>" placeholder="Поиск по названию..." class="filter w-full text-xs">
    </div>

    <!-- Category -->
    <div>
      <select name="category" class="filter w-full text-xs">
        <option value="">Все категории</option>
        <option value="vinyl" <?=$currentCategory==='vinyl'?'selected':''?>>Винил (Vinyl)</option>
        <option value="single" <?=$currentCategory==='single'?'selected':''?>>Синглы (Singles)</option>
        <option value="dvd" <?=$currentCategory==='dvd'?'selected':''?>>Концертные DVD</option>
        <option value="merch" <?=$currentCategory==='merch'?'selected':''?>>Официальный мерч</option>
        <option value="cd" <?=$currentCategory==='cd'?'selected':''?>>Компакт-диски (CD)</option>
      </select>
    </div>

    <!-- Artist -->
    <div>
      <select name="artist" class="filter w-full text-xs">
        <option value="">Все артисты</option>
        <?php foreach ($artists as $a): ?>
          <option value="<?=$a['id']?>" <?=($_GET['artist'] ?? '') == $a['id'] ? 'selected' : ''?>>
            <?=htmlspecialchars($a['name'])?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Genre -->
    <div>
      <select name="genre" class="filter w-full text-xs">
        <option value="">Все жанры</option>
        <?php foreach ($genres as $g): ?>
          <option value="<?=htmlspecialchars($g)?>" <?=($g === ($_GET['genre'] ?? '')) ? 'selected' : ''?>>
            <?=htmlspecialchars($g)?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Vinyl Variant -->
    <div>
      <select name="variant" class="filter w-full text-xs">
        <option value="">Цвет винила</option>
        <?php foreach ($variants as $v): ?>
          <option value="<?=htmlspecialchars($v)?>" <?=($v === ($_GET['variant'] ?? '')) ? 'selected' : ''?>>
            <?=htmlspecialchars($v)?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

  </div>

  <div class="mt-3 flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-white/5 pt-3">
    <label class="flex items-center gap-2 text-xs text-zinc-400 cursor-pointer">
      <input type="checkbox" name="in_stock" value="1" <?=$onlyInStock?'checked':''?> class="h-4 w-4 rounded accent-lime-400">
      <span>Только товары в наличии</span>
    </label>

    <div class="flex items-center gap-3 w-full sm:w-auto">
      <?php if (!empty($searchQuery) || !empty($currentCategory) || !empty($_GET['artist']) || !empty($_GET['genre']) || !empty($_GET['variant']) || $onlyInStock): ?>
        <a href="index.php" class="text-xs text-zinc-500 hover:text-white">Сбросить</a>
      <?php endif; ?>
      <button type="submit" class="w-full sm:w-auto rounded-xl bg-lime-300 px-6 py-2.5 text-xs font-black uppercase tracking-wider text-black hover:bg-lime-400 transition-all">
        Применить фильтры
      </button>
    </div>
  </div>
</form>

<!-- Products Catalog Grid -->
<?php if (empty($products)): ?>
  <div class="rounded-3xl border border-white/5 bg-[#121216]/50 p-16 text-center">
    <div class="text-4xl mb-3">🔍</div>
    <h3 class="text-lg font-bold text-white">Ничего не найдено</h3>
    <p class="text-xs text-zinc-500 mt-1 max-w-sm mx-auto">Попробуйте изменить параметры фильтра или сбросить поисковый запрос.</p>
    <a href="index.php" class="mt-4 inline-block rounded-xl bg-white/10 px-5 py-2 text-xs font-bold text-white hover:bg-white/20">
      Показать все товары
    </a>
  </div>
<?php else: ?>
  <div id="catalogGrid" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
    <?php foreach ($products as $p): ?>
      <?php include __DIR__ . '/partials/product-card.php'; ?>
    <?php endforeach; ?>
  </div>

  <!-- Load more / infinite scroll -->
  <div class="mt-10 text-center">
    <?php if ($hasMore): ?>
      <button type="button" id="loadMoreBtn"
              class="rounded-xl border border-lime-400/40 bg-lime-400/10 px-8 py-3 text-xs font-black uppercase tracking-wider text-lime-300 hover:bg-lime-400/20 transition-all"
              data-offset="<?=count($products)?>"
              data-total="<?=htmlspecialchars(http_build_query(array_filter([
                  'category' => $currentCategory,
                  'artist' => $_GET['artist'] ?? '',
                  'genre' => $_GET['genre'] ?? '',
                  'variant' => $_GET['variant'] ?? '',
                  'q' => $searchQuery,
                  'in_stock' => $onlyInStock ? '1' : '',
              ], fn($v) => $v !== '')))?>">
        Показать ещё <span class="font-mono text-[10px] text-zinc-500">(<?=$hasMore ? $totalCount - count($products) : 0?> осталось)</span>
      </button>
      <div id="catalogSentinel" class="h-1"></div>
    <?php else: ?>
      <p class="text-xs text-zinc-600">Показаны все товары (<?=$totalCount?>)</p>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/partials/footer.php'; ?>
