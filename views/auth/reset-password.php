<?php $title = 'Новый пароль — VINYLWAVE'; require __DIR__ . '/../partials/header.php'; ?>
<div class="mx-auto max-w-md py-12"><div class="rounded-3xl border border-white/10 bg-[#121216] p-8 shadow-2xl">
  <h1 class="text-3xl font-black">Новый пароль</h1>
  <p class="mt-2 text-xs text-zinc-400">Минимум 12 символов, строчная и заглавная буква, цифра.</p>
  <form method="post" action="index.php?page=reset-password" class="mt-6 space-y-4">
    <?=Csrf::input()?>
    <input type="hidden" name="token" value="<?=htmlspecialchars($token ?? '')?>">
    <input type="password" name="password" required minlength="12" autocomplete="new-password" class="filter w-full text-sm" placeholder="Новый пароль">
    <button class="w-full rounded-2xl bg-lime-300 py-3 font-black text-black">Сохранить пароль</button>
  </form>
</div></div>
<?php require __DIR__ . '/../partials/footer.php'; ?>