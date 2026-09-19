<?php 
$title = 'Заказ подтвержден — VINYLWAVE'; 
require __DIR__ . '/partials/header.php'; 
?>

<section class="flex min-h-[60vh] flex-col items-center justify-center text-center py-12">
  <div class="relative mb-6">
    <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-lime-400 text-black text-3xl font-black shadow-[0_0_40px_rgba(185,255,44,0.6)] animate-bounce">
      ✓
    </div>
  </div>

  <p class="text-xs font-bold uppercase tracking-[.3em] text-lime-300">Оплата подтверждена</p>
  <h1 class="mt-3 text-4xl sm:text-6xl font-black tracking-tight text-white">Вы в игре.</h1>
<p class="mt-4 max-w-md text-xs sm:text-sm text-zinc-400 leading-relaxed">
    Заказ <strong class="text-lime-300 font-mono">#<?=$orderIdForView?></strong> успешно оформлен и передан на сборку. Прессинг зарезервирован за вами.
</p>

  <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
    <a href="index.php" class="rounded-2xl bg-white px-8 py-3.5 text-xs font-black uppercase tracking-wider text-black hover:bg-lime-300 hover:shadow-[0_0_20px_rgba(185,255,44,0.4)] transition-all">
      Вернуться в магазин →
    </a>
    <?php if (User::current()): ?>
      <a href="index.php?page=profile" class="rounded-2xl border border-white/10 bg-white/5 px-6 py-3.5 text-xs font-bold text-zinc-300 hover:bg-white/10">
        Посмотреть в моих заказах
      </a>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
