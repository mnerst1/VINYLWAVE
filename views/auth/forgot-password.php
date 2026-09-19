<?php $title = 'Восстановление пароля — VINYLWAVE'; require __DIR__ . '/../partials/header.php'; ?>
<div class="mx-auto max-w-md py-12"><div class="rounded-3xl border border-white/10 bg-[#121216] p-8 shadow-2xl">
  <h1 class="text-3xl font-black">Восстановление пароля</h1>
  <p class="mt-2 text-xs text-zinc-400">Укажите email — если аккаунт существует, мы отправим одноразовую ссылку.</p>
  <?php if (!empty($success)): ?><p class="mt-5 rounded-xl bg-lime-400/10 p-3 text-xs text-lime-300"><?=htmlspecialchars($success)?></p><?php endif; ?>
  <form method="post" action="index.php?page=forgot-password" class="mt-6 space-y-4">
    <?=Csrf::input()?>
    <input type="email" name="email" required placeholder="you@example.com" class="filter w-full text-sm">
    <button class="w-full rounded-2xl bg-lime-300 py-3 font-black text-black">Отправить ссылку</button>
  </form>
  <a class="mt-5 block text-center text-xs text-zinc-400 hover:text-lime-300" href="index.php?page=login">← Вернуться ко входу</a>
</div></div>
<?php require __DIR__ . '/../partials/footer.php'; ?>