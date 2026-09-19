<?php 
$title = 'Корзина покупок — VINYLWAVE'; 
require __DIR__ . '/partials/header.php'; 
?>

<section class="py-8 max-w-4xl mx-auto">
  <div class="flex items-center justify-between border-b border-white/10 pb-4 mb-6">
    <div>
      <h1 class="text-3xl sm:text-4xl font-black tracking-tight">Ваша корзина</h1>
      <p class="text-xs text-zinc-400">Проверьте выбранные виниловые издания и мерч перед оформлением</p>
    </div>
    <a href="index.php" class="text-xs font-bold text-lime-300 hover:underline">
      ← Продолжить покупки
    </a>
  </div>

  <?php if (empty($items)): ?>
    <div class="rounded-3xl border border-white/5 bg-[#121216]/50 p-16 text-center shadow-xl">
      <div class="text-5xl mb-4">🛒</div>
      <h2 class="text-xl font-black text-white">В вашей корзине пока пусто</h2>
      <p class="text-xs text-zinc-500 mt-2 max-w-md mx-auto">
        Откройте для себя редкие виниловые прессинги, 7-дюймовые синглы, DVD и эксклюзивный мерч в нашем каталоге.
      </p>
      <a href="index.php" class="mt-6 inline-block rounded-xl bg-lime-300 px-8 py-3 text-xs font-black uppercase tracking-wider text-black hover:bg-lime-400 shadow-[0_0_20px_rgba(185,255,44,0.3)] transition-all">
        Перейти к витрине →
      </a>
    </div>
  <?php else: ?>
    <form action="index.php?page=cart" method="post" class="space-y-4">
      <?=Csrf::input()?>
      <?php $total = 0; foreach ($items as $i): 
        $sub = $i['price'] * $i['qty'];
        $total += $sub;
      ?>
        <div class="rounded-2xl border border-white/10 bg-[#121216] p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 glow-card">
          <div class="flex items-center gap-4 min-w-0">
            <img src="<?=htmlspecialchars($i['cover_url'])?>" class="h-20 w-20 rounded-xl object-cover border border-white/10 flex-shrink-0" alt="<?=htmlspecialchars($i['name'])?>">
            <div class="min-w-0">
              <a href="index.php?page=product&id=<?=$i['id']?>" class="font-bold text-sm text-white hover:text-lime-300 truncate block">
                <?=htmlspecialchars($i['name'])?>
              </a>
              <div class="text-xs text-zinc-500 mt-0.5">
                <?=htmlspecialchars($i['artist_name'])?>
                <?php if (!empty($i['size'])): ?>
                  · <span class="text-lime-300 font-mono font-bold">Размер: <?=$i['size']?></span>
                <?php endif; ?>
              </div>
              <div class="mt-2 text-xs font-mono text-zinc-400">
                $<?=number_format((float)$i['price'], 2)?> / шт.
              </div>
            </div>
          </div>

          <div class="flex items-center gap-4 self-end sm:self-center">
            <div class="flex items-center gap-2">
              <span class="text-xs text-zinc-500">Кол-во:</span>
              <input class="filter h-10 w-20 text-center font-mono font-bold text-sm" type="number" min="0" max="<?=$i['stock']?>" name="qty[<?=$i['id']?>]" value="<?=$i['qty']?>">
            </div>
            <div class="w-24 text-right font-mono font-black text-sm text-white">
              $<?=number_format((float)$sub, 2)?>
            </div>
            <button type="submit" name="qty[<?=$i['id']?>]" value="0" class="text-zinc-600 hover:text-red-400 text-xs px-2 py-1" title="Удалить из корзины">
              ✕
            </button>
          </div>
        </div>
      <?php endforeach; ?>

      <!-- Summary & Actions -->
      <div class="rounded-3xl border border-white/10 bg-[#121216] p-6 mt-6">
        <div class="flex items-center justify-between pb-4 border-b border-white/5">
          <span class="text-sm text-zinc-400">Сумма заказа:</span>
          <strong class="text-3xl font-black font-mono text-lime-300">$<?=number_format((float)$total, 2)?></strong>
        </div>
        <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-4">
          <button type="submit" class="w-full sm:w-auto rounded-xl border border-white/10 bg-white/5 px-6 py-3 text-xs font-bold text-zinc-300 hover:bg-white/10">
            ↻ Обновить корзину
          </button>
          <a href="index.php?page=checkout" class="w-full sm:w-auto rounded-xl bg-lime-300 px-8 py-3.5 text-center text-xs font-black uppercase tracking-wider text-black hover:bg-lime-400 hover:shadow-[0_0_25px_rgba(185,255,44,0.4)] transition-all">
            Перейти к оформлению заказа →
          </a>
        </div>
      </div>
    </form>
  <?php endif; ?>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
