<?php 
$title = 'Вход — VINYLWAVE'; 
require __DIR__ . '/../partials/header.php'; 
?>

<div class="mx-auto max-w-md py-12">
  <div class="relative rounded-3xl border border-white/10 bg-[#121216] p-8 shadow-2xl overflow-hidden glow-card">
    
    <!-- Neon accent top border -->
    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-lime-400 via-emerald-400 to-lime-300"></div>

    <div class="text-center mb-8">
      <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-lime-400/10 text-lime-300 mb-3 border border-lime-400/20">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
      </div>
      <h1 class="text-3xl font-black tracking-tight">Авторизация</h1>
      <p class="text-xs text-zinc-400 mt-2">Войдите как клиент для отзывов с фото или как администратор</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="mb-5 rounded-2xl border border-red-500/30 bg-red-500/10 p-3.5 text-xs text-red-300 flex items-center gap-2">
        <span>⚠</span>
        <span><?=htmlspecialchars($error)?></span>
      </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
      <div class="mb-5 rounded-2xl border border-lime-500/30 bg-lime-500/10 p-3.5 text-xs text-lime-300 flex items-center gap-2">
        <span>✓</span>
        <span><?=htmlspecialchars($success)?></span>
      </div>
    <?php endif; ?>

    <form method="post" action="index.php?page=login" class="space-y-4">
      <input type="hidden" name="return_to" value="<?=htmlspecialchars($_GET['return_to'] ?? '')?>">
      <?=Csrf::input()?>
      
      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1.5">Email адрес</label>
        <input id="loginEmail" type="email" name="email" required placeholder="name@domain.com" class="filter w-full text-sm">
      </div>

      <div>
        <div class="flex justify-between items-center mb-1.5">
          <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400">Пароль</label>
        </div>
        <input id="loginPassword" type="password" name="password" required placeholder="••••••••" class="filter w-full text-sm">
      </div>

      <button type="submit" class="w-full rounded-2xl bg-lime-300 py-3.5 font-black text-black text-sm uppercase tracking-wider hover:bg-lime-400 hover:shadow-[0_0_20px_rgba(185,255,44,0.4)] transition-all">
        Войти в аккаунт →
      </button>
    </form>


    <!-- Links -->
    <div class="mt-6 flex flex-col gap-2 text-center text-xs text-zinc-400">
      <div>
        Нет аккаунта? <a href="index.php?page=register" class="text-lime-300 font-semibold hover:underline">Создать аккаунт</a>
      </div>
      <div>
        <a href="index.php?page=forgot-password" class="text-lime-300 font-semibold hover:underline">Забыли пароль?</a>
      </div>
      <div class="pt-2">
        <a href="index.php" class="text-zinc-500 hover:text-zinc-300">← Продолжить просмотр как гость (без входа)</a>
      </div>
    </div>

  </div>
</div>


<?php require __DIR__ . '/../partials/footer.php'; ?>
