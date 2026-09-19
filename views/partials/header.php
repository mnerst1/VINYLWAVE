<?php
$currentUser = User::current();
$cartCount = array_sum(array_map(fn($x) => (int)$x['qty'], $_SESSION['cart'] ?? []));
$currentCategory = $_GET['category'] ?? '';
$searchQuery = $_GET['q'] ?? '';
$wishlistCount = 0;
if ($currentUser) {
    $wl = new Wishlist(Database::connection());
    $wishlistCount = $wl->countForUser((int)$currentUser['id']);
}
?>
<!doctype html>
<html lang="ru" class="scroll-smooth">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#0b0b0d">
  <title><?=htmlspecialchars($title ?? 'VINYLWAVE — Культовый винил, мерч, синглы и DVD')?></title>
  <link rel="manifest" href="manifest.json">
  <script>
    // Restore theme before first paint to avoid flash
    (function () {
      try {
        var t = localStorage.getItem('vw-theme');
        if (t === 'light') document.documentElement.classList.add('light-theme');
      } catch (e) {}
    })();
  </script>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: {
              lime: '#b9ff2c',
              dark: '#0b0b0d',
              card: '#131317',
              border: 'rgba(255,255,255,0.08)'
            }
          },
          boxShadow: {
            glow: '0 0 40px rgba(185,255,44,0.2)',
            neon: '0 0 15px rgba(185,255,44,0.3)'
          }
        }
      }
    };
  </script>
  <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="bg-[#0b0b0d] text-zinc-100 antialiased min-h-screen flex flex-col selection:bg-lime-400 selection:text-black">

<?php if (!empty($_SESSION['toast'])): ?>
  <div id="server-toast" data-message="<?=htmlspecialchars($_SESSION['toast']['message'])?>" data-type="<?=htmlspecialchars($_SESSION['toast']['type'])?>" class="hidden"></div>
  <?php unset($_SESSION['toast']); ?>
<?php endif; ?>

<!-- Top Notification Bar -->
<div class="bg-gradient-to-r from-lime-400 via-emerald-400 to-lime-300 px-4 py-1.5 text-center text-xs font-black uppercase tracking-wider text-black">
  <span>✦ Лимитированные прессинги, официальный мерч, редкие синглы и DVD артистов. Доставка по всему миру. ✦</span>
</div>

<!-- Impersonation banner -->
<?php if (User::isImpersonating()): ?>
  <div class="bg-sky-500 px-4 py-1.5 text-center text-xs font-black text-black">
    🔎 Вы вошли как <?=htmlspecialchars($currentUser['name'] ?? 'пользователь')?>.
    <form method="post" action="index.php?page=admin-stop-impersonate" class="inline ml-2">
      <button type="submit" class="rounded bg-black/30 px-2 py-0.5 font-bold text-white hover:bg-black/50">Вернуться в админку</button>
    </form>
  </div>
<?php endif; ?>

<header class="sticky top-0 z-40 border-b border-white/10 bg-[#0b0b0d]/90 backdrop-blur-xl transition-all">
  <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
    
    <!-- Logo -->
    <a href="index.php" class="group flex items-center gap-2 text-xl font-black tracking-tighter sm:text-2xl">
      <span class="inline-block h-6 w-6 rounded-full bg-lime-300 transition-transform group-hover:rotate-180 group-hover:scale-110 shadow-[0_0_12px_rgba(185,255,44,0.6)]"></span>
      <span>VINYL<span class="text-lime-300">WAVE</span></span>
    </a>

    <!-- Search Form (Desktop) -->
    <form action="index.php" method="get" class="relative hidden max-w-xs flex-1 lg:block">
      <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-500">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      </span>
      <input type="search" name="q" value="<?=htmlspecialchars($searchQuery)?>" placeholder="Поиск альбома, сингла, мерча..." class="filter w-full pl-9 text-xs placeholder:text-zinc-500">
    </form>

    <!-- Navigation categories -->
    <nav class="hidden items-center gap-1 text-xs font-semibold uppercase tracking-wider md:flex">
      <a href="index.php" class="rounded-full px-3 py-1.5 transition-colors <?=$currentCategory==='' && empty($searchQuery)?'bg-white/10 text-lime-300':'text-zinc-400 hover:text-white'?>">Все</a>
      <a href="index.php?category=vinyl" class="rounded-full px-3 py-1.5 transition-colors <?=$currentCategory==='vinyl'?'bg-white/10 text-lime-300':'text-zinc-400 hover:text-white'?>">Винил</a>
      <a href="index.php?category=single" class="rounded-full px-3 py-1.5 transition-colors <?=$currentCategory==='single'?'bg-white/10 text-lime-300':'text-zinc-400 hover:text-white'?>">Синглы</a>
      <a href="index.php?category=dvd" class="rounded-full px-3 py-1.5 transition-colors <?=$currentCategory==='dvd'?'bg-white/10 text-lime-300':'text-zinc-400 hover:text-white'?>">DVD</a>
      <a href="index.php?category=merch" class="rounded-full px-3 py-1.5 transition-colors <?=$currentCategory==='merch'?'bg-white/10 text-lime-300':'text-zinc-400 hover:text-white'?>">Мерч</a>
      <a href="index.php?category=cd" class="rounded-full px-3 py-1.5 transition-colors <?=$currentCategory==='cd'?'bg-white/10 text-lime-300':'text-zinc-400 hover:text-white'?>">CD</a>
    </nav>

    <!-- User / Auth / Cart Controls -->
    <div class="flex items-center gap-3">
      <?php if ($currentUser): ?>
        <?php if ($currentUser['role'] === 'admin'): ?>
          <a href="index.php?page=admin" class="flex items-center gap-1.5 rounded-full border border-lime-400/40 bg-lime-400/10 px-3 py-1.5 text-xs font-bold text-lime-300 hover:bg-lime-400/20 hover:border-lime-400">
            <span class="inline-block h-2 w-2 rounded-full bg-lime-300 animate-pulse"></span>
            ⚙ Админ-панель
          </a>
        <?php else: ?>
          <a href="index.php?page=profile" class="flex items-center gap-1.5 rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-xs font-medium text-zinc-300 hover:bg-white/10">
            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-zinc-800 text-[10px] font-bold text-lime-300">
              <?=mb_strtoupper(mb_substr($currentUser['name'], 0, 1))?>
            </span>
            <span class="max-w-[100px] truncate"><?=htmlspecialchars($currentUser['name'])?></span>
          </a>
        <?php endif; ?>
        <form method="post" action="index.php?page=logout" class="inline">
          <?=Csrf::input()?>
          <button type="submit" title="Выйти из аккаунта" class="text-xs text-zinc-500 hover:text-zinc-300 px-1 py-1">Выход</button>
        </form>
      <?php else: ?>
        <a href="index.php?page=login" class="flex items-center gap-1.5 rounded-full border border-white/10 px-3.5 py-1.5 text-xs font-medium text-zinc-300 hover:border-lime-300/40 hover:text-lime-300">
          <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          Войти
        </a>
      <?php endif; ?>

      <!-- Wishlist / Compare / Theme -->
      <a href="index.php?page=wishlist" id="headerWishlistBtn" class="relative flex items-center gap-1.5 rounded-full border border-white/10 bg-white/[0.04] px-3 py-1.5 text-xs font-semibold text-zinc-200 hover:border-red-300/50 hover:bg-white/10" title="Избранное">
        <span class="text-red-400">♥</span>
        <span id="headerWishlistBadge" class="inline-flex items-center justify-center rounded-full bg-red-400/20 px-2 py-0.5 text-[11px] font-black text-red-300"><?=$wishlistCount?></span>
      </a>
      <a href="index.php?page=compare" id="headerCompareBtn" class="relative hidden sm:flex items-center gap-1.5 rounded-full border border-white/10 bg-white/[0.04] px-3 py-1.5 text-xs font-semibold text-zinc-200 hover:border-sky-300/50 hover:bg-white/10" title="Сравнение товаров">
        <span class="text-sky-300">⇄</span>
        <span id="headerCompareBadge" class="inline-flex items-center justify-center rounded-full bg-sky-400/20 px-2 py-0.5 text-[11px] font-black text-sky-300">0</span>
      </a>
      <button type="button" id="themeToggle" class="flex h-8 w-8 items-center justify-center rounded-full border border-white/10 bg-white/[0.04] text-sm text-zinc-300 hover:border-lime-300/40 hover:text-lime-300" title="Светлая / темная тема">
        <span id="themeIcon">🌙</span>
      </button>

      <!-- Cart Button (Hidden for Admin) -->
      <?php if (!$currentUser || $currentUser['role'] !== 'admin'): ?>
        <a href="index.php?page=cart" id="headerCartBtn" class="relative flex items-center gap-1.5 rounded-full border border-white/10 bg-white/[0.04] px-3.5 py-1.5 text-xs font-semibold text-zinc-200 hover:border-lime-300/50 hover:bg-white/10">
          <svg class="h-4 w-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
          <span class="hidden sm:inline">Корзина</span>
          <span id="headerCartBadge" class="ml-0.5 inline-flex items-center justify-center rounded-full bg-lime-300 px-2 py-0.5 text-[11px] font-black text-black">
            <?=$cartCount?>
          </span>
        </a>
      <?php endif; ?>
    </div>

  </div>

  <!-- Mobile Categories Bar -->
  <div class="flex overflow-x-auto border-t border-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-wider md:hidden scrollbar-none gap-2">
    <a href="index.php" class="whitespace-nowrap rounded-full px-3 py-1 <?=$currentCategory===''?'bg-white/10 text-lime-300':'text-zinc-400'?>">Все</a>
    <a href="index.php?category=vinyl" class="whitespace-nowrap rounded-full px-3 py-1 <?=$currentCategory==='vinyl'?'bg-white/10 text-lime-300':'text-zinc-400'?>">Винил</a>
    <a href="index.php?category=single" class="whitespace-nowrap rounded-full px-3 py-1 <?=$currentCategory==='single'?'bg-white/10 text-lime-300':'text-zinc-400'?>">Синглы</a>
    <a href="index.php?category=dvd" class="whitespace-nowrap rounded-full px-3 py-1 <?=$currentCategory==='dvd'?'bg-white/10 text-lime-300':'text-zinc-400'?>">DVD</a>
    <a href="index.php?category=merch" class="whitespace-nowrap rounded-full px-3 py-1 <?=$currentCategory==='merch'?'bg-white/10 text-lime-300':'text-zinc-400'?>">Мерч</a>
    <a href="index.php?category=cd" class="whitespace-nowrap rounded-full px-3 py-1 <?=$currentCategory==='cd'?'bg-white/10 text-lime-300':'text-zinc-400'?>">CD</a>
  </div>
</header>

<main class="flex-1 mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 pb-40">
