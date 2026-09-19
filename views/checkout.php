<?php 
$title = 'Оформление заказа — VINYLWAVE'; 
require __DIR__ . '/partials/header.php'; 
$subtotal = array_reduce($items, fn($s, $i) => $s + $i['price'] * $i['qty'], 0); 
$currentUser = User::current();

// Calculate shipping if city provided
$shipping = null;
$selectedShipping = $_POST['shipping_option'] ?? $_SESSION['shipping_option'] ?? 'standard';
$city = $_POST['city'] ?? $_SESSION['shipping_city'] ?? '';
if ($city) {
    $shippingCalc = new ShippingCalculator(Database::connection());
    $shipping = $shippingCalc->calculate($items, $city);
}
?>

<section class="grid gap-10 py-8 lg:grid-cols-[1fr_380px] max-w-5xl mx-auto">
  <div>
    <h1 class="text-3xl sm:text-4xl font-black tracking-tight">Оформление заказа</h1>
    <p class="text-xs text-zinc-400 mt-1">
      <?=$currentUser ? "Оформление для аккаунта: <strong class='text-lime-300'>{$currentUser['name']}</strong>" : "Гостевой заказ (авторизация не требуется)"?>
    </p>

    <?php if (!empty($error)): ?>
      <div class="mt-5 rounded-2xl border border-red-500/30 bg-red-500/10 p-3.5 text-xs text-red-300 flex items-center gap-2">
        <span>⚠</span>
        <span><?=htmlspecialchars($error)?></span>
      </div>
    <?php endif; ?>

    <form method="post" action="index.php?page=checkout" class="mt-8 space-y-4">
      <?=Csrf::input()?>
      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1.5">Имя и фамилия получателя *</label>
        <input name="name" value="<?=htmlspecialchars($currentUser['name'] ?? '')?>" placeholder="Иван Иванов" class="filter w-full text-sm" required>
      </div>

      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1.5">Email для трек-номера *</label>
        <input name="email" type="email" value="<?=htmlspecialchars($currentUser['email'] ?? '')?>" placeholder="name@domain.com" class="filter w-full text-sm" required>
      </div>

      <div class="grid gap-4 sm:grid-cols-2">
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1.5">Город доставки *</label>
          <input name="city" placeholder="Алматы" class="filter w-full text-sm" required>
        </div>
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1.5">Адрес / Пункт выдачи *</label>
          <input name="address" placeholder="Улица Каныша Сатпаева, дом 37" class="filter w-full text-sm" required>
        </div>
      </div>

      <div class="rounded-2xl border border-white/5 bg-white/[0.02] p-4">
        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1.5">Промокод на скидку:</label>
        <div class="flex gap-2">
          <input name="promo" placeholder="TRAVIS10" class="filter flex-1 text-sm font-mono uppercase">
          <span class="self-center text-xs text-lime-300 font-mono">TRAVIS10 = -10%</span>
        </div>
      </div>

      <button type="submit" class="w-full rounded-2xl bg-lime-300 py-4 font-black text-black text-xs uppercase tracking-wider hover:bg-lime-400 hover:shadow-[0_0_25px_rgba(185,255,44,0.4)] transition-all">
        Подтвердить и оплатить заказ →
      </button>
      <div class="text-center text-[11px] text-zinc-500">
        Симуляция мгновенной оплаты и резервирования тиража
      </div>
    </form>
  </div>

  <!-- Order Summary Sidebar -->
  <aside class="h-fit rounded-3xl border border-white/10 bg-[#121216] p-6 glow-card">
    <h2 class="font-black text-base text-white border-b border-white/10 pb-3 mb-4">Ваш заказ</h2>
    <div class="space-y-3">
      <?php foreach ($items as $i): ?>
        <div class="flex items-center justify-between text-xs py-1">
          <div class="pr-3">
            <span class="font-semibold text-zinc-200 block"><?=htmlspecialchars($i['name'])?></span>
            <span class="text-zinc-500">
              <?=$i['qty']?> шт.
              <?php if (!empty($i['size'])): ?>
                · Размер <?=$i['size']?>
              <?php endif; ?>
            </span>
          </div>
          <span class="font-mono text-zinc-300 flex-shrink-0">$<?=number_format((float)$i['price'] * (int)$i['qty'], 2)?></span>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="mt-6 border-t border-white/10 pt-4 flex items-center justify-between">
      <span class="text-xs text-zinc-400">Сумма без скидки:</span>
      <span class="font-mono font-bold text-white text-sm">$<?=number_format((float)$subtotal, 2)?></span>
    </div>
  </aside>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>

<script>
(function() {
    // Extend inventory reservation every 5 minutes
    const EXTEND_INTERVAL = 5 * 60 * 1000; // 5 minutes
    
    function extendReservation() {
        fetch('index.php?page=checkout-extend-reservation', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'csrf_token=' + encodeURIComponent(document.querySelector('input[name="csrf_token"]').value)
        }).then(response => response.json())
        .then(data => {
            if (!data.success) {
                console.warn('Failed to extend inventory reservation');
            }
        }).catch(err => {
            console.error('Error extending reservation:', err);
        });
    }
    
    // Extend immediately on page load
    extendReservation();
    
    // Set interval
    setInterval(extendReservation, EXTEND_INTERVAL);
    

})();
</script>
