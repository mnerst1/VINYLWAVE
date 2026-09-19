<?php 
$title = 'Регистрация — VINYLWAVE'; 
require __DIR__ . '/../partials/header.php'; 
?>

<div class="mx-auto max-w-md py-12">
  <div class="relative rounded-3xl border border-white/10 bg-[#121216] p-8 shadow-2xl overflow-hidden glow-card">
    
    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-lime-400 via-emerald-400 to-lime-300"></div>

    <div class="text-center mb-8">
      <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-lime-400/10 text-lime-300 mb-3 border border-lime-400/20">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
      </div>
      <h1 class="text-3xl font-black tracking-tight">Регистрация</h1>
      <p class="text-xs text-zinc-400 mt-2">Присоединяйтесь к комьюнити, оставляйте отзывы с фото и отслеживайте заказы</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="mb-5 rounded-2xl border border-red-500/30 bg-red-500/10 p-3.5 text-xs text-red-300 flex items-center gap-2">
        <span>⚠</span>
        <span><?=htmlspecialchars($error)?></span>
      </div>
    <?php endif; ?>

    <form method="post" action="index.php?page=register" class="space-y-4">
      <?=Csrf::input()?>
      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1.5">Ваше имя / Никнейм</label>
        <input type="text" name="name" required placeholder="Alexander / CactusJack" class="filter w-full text-sm">
      </div>

      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1.5">Email адрес</label>
        <input type="email" name="email" required placeholder="you@domain.com" class="filter w-full text-sm">
      </div>

      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1.5">Пароль (от 12 символов: Aa1)</label>
        <input type="password" name="password" required minlength="12" placeholder="••••••••" class="filter w-full text-sm">
      </div>

      <button type="submit" class="w-full rounded-2xl bg-lime-300 py-3.5 font-black text-black text-sm uppercase tracking-wider hover:bg-lime-400 hover:shadow-[0_0_20px_rgba(185,255,44,0.4)] transition-all">
        Зарегистрироваться →
      </button>
    </form>

    <div class="mt-6 flex flex-col gap-2 text-center text-xs text-zinc-400 border-t border-white/5 pt-5">
      <div>
        Уже есть аккаунт? <a href="index.php?page=login" class="text-lime-300 font-semibold hover:underline">Войти в аккаунт</a>
      </div>
      <div class="pt-2">
        <a href="index.php" class="text-zinc-500 hover:text-zinc-300">← Назад в магазин (просмотр как гость)</a>
      </div>
    </div>

  </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
