<?php 
$title = 'Личный кабинет — VINYLWAVE'; 
require __DIR__ . '/partials/header.php'; 
?>

<section class="py-8">
  <!-- Profile Header Card -->
  <div class="rounded-3xl border border-white/10 bg-[#121216] p-6 sm:p-8 shadow-2xl mb-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
    <div class="flex items-center gap-4">
      <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-tr from-lime-400 to-emerald-400 text-2xl font-black text-black shadow-lg">
        <?=mb_strtoupper(mb_substr($user['name'], 0, 1))?>
      </div>
      <div>
        <div class="flex items-center gap-2.5">
          <h1 class="text-2xl font-black text-white"><?=htmlspecialchars($user['name'])?></h1>
          <span class="rounded-full bg-lime-400/15 border border-lime-400/30 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-lime-300">
            <?=htmlspecialchars($user['role'])?>
          </span>
        </div>
        <p class="text-xs text-zinc-400 font-mono mt-1"><?=htmlspecialchars($user['email'])?></p>
      </div>
    </div>
    <div class="flex items-center gap-3">
      <?php if ($user['role'] === 'admin'): ?>
        <a href="index.php?page=admin" class="rounded-xl border border-lime-400/40 bg-lime-400/10 px-4 py-2.5 text-xs font-bold text-lime-300 hover:bg-lime-400/20">
          ⚙ Перейти в админ-панель
        </a>
      <?php endif; ?>
      <form method="post" action="index.php?page=logout">
        <?=Csrf::input()?>
        <button type="submit" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs font-semibold text-zinc-300 hover:bg-white/10">Выйти из аккаунта</button>
      </form>
    </div>
  </div>

  <!-- Orders History -->
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="text-2xl font-black tracking-tight">История заказов</h2>
        <p class="text-xs text-zinc-400">Все ваши покупки и статус выполнения доставки</p>
      </div>
      <a href="index.php" class="text-xs font-semibold text-lime-300 hover:underline">
        В каталог за новинками →
      </a>
    </div>

    <?php if (empty($orders)): ?>
      <div class="rounded-3xl border border-white/5 bg-[#121216]/50 p-12 text-center">
        <div class="text-4xl mb-3">📦</div>
        <h3 class="text-lg font-bold text-zinc-300">У вас пока нет заказов</h3>
        <p class="text-xs text-zinc-500 mt-1 max-w-sm mx-auto">Выберите виниловые пластинки, редкие синглы или мерч ваших любимых исполнителей.</p>
        <a href="index.php" class="mt-5 inline-block rounded-xl bg-lime-300 px-6 py-2.5 text-xs font-bold text-black hover:bg-lime-400">
          Перейти к покупкам
        </a>
      </div>
    <?php else: ?>
      <div class="space-y-4">
        <?php foreach ($orders as $order): ?>
          <div class="rounded-2xl border border-white/10 bg-[#121216] p-5 glow-card">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-white/5 pb-4 mb-4">
              <div>
                <div class="flex items-center gap-3">
                  <span class="font-mono font-bold text-white text-sm">Заказ #<?=$order['id']?></span>
                  <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider 
                    <?php 
                      switch($order['status']) {
                        case 'paid': echo 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30'; break;
                        case 'shipped': echo 'bg-sky-500/15 text-sky-400 border border-sky-500/30'; break;
                        case 'cancelled': echo 'bg-red-500/15 text-red-400 border border-red-500/30'; break;
                        default: echo 'bg-amber-500/15 text-amber-400 border border-amber-500/30'; break;
                      }
                    ?>">
                    <?=$order['status']?>
                  </span>
                </div>
                <div class="text-xs text-zinc-500 mt-1">Дата: <?=date('d.m.Y H:i', strtotime($order['created_at']))?> · Адрес: <?=htmlspecialchars($order['city'])?>, <?=htmlspecialchars($order['address'])?></div>
              </div>
              <div class="text-right">
                <div class="text-xs text-zinc-400">Итого:</div>
                <div class="text-lg font-black font-mono text-lime-300">$<?=number_format((float)$order['total'], 2)?></div>
              </div>
            </div>

            <!-- Items -->
            <div class="space-y-2">
              <?php foreach ($order['items'] as $item): ?>
                <div class="flex items-center justify-between text-xs py-1">
                  <div class="flex items-center gap-2">
                    <span class="font-semibold text-zinc-200"><?=htmlspecialchars($item['product_name'])?></span>
                    <?php if (!empty($item['size'])): ?>
                      <span class="rounded bg-white/10 px-1.5 py-0.5 text-[10px] font-mono text-zinc-400">Размер: <?=$item['size']?></span>
                    <?php endif; ?>
                    <span class="text-zinc-500">× <?=$item['quantity']?></span>
                  </div>
                  <div class="font-mono text-zinc-300">$<?=number_format((float)$item['unit_price'] * (int)$item['quantity'], 2)?></div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
