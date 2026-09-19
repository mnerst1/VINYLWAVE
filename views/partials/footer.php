</main>

<!-- Footer -->
<footer class="border-t border-white/10 bg-[#08080a] text-zinc-400 text-xs mt-auto">
  <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6">
    <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
      <div>
        <div class="flex items-center gap-2 text-base font-black text-white tracking-tight mb-3">
          <span class="h-4 w-4 rounded-full bg-lime-300"></span>
          VINYL<span class="text-lime-300">WAVE</span>
        </div>
        <p class="leading-relaxed text-zinc-500">
          Культовая платформа виниловой культуры, коллекционных синглов, концертных DVD и эксклюзивного мерча современных артистов.
        </p>
      </div>
      <div>
        <h4 class="font-bold uppercase tracking-wider text-white mb-3">Каталог</h4>
        <ul class="space-y-2">
          <li><a href="index.php?category=vinyl" class="hover:text-lime-300">Виниловые альбомы</a></li>
          <li><a href="index.php?category=single" class="hover:text-lime-300">7" и 12" синглы</a></li>
          <li><a href="index.php?category=dvd" class="hover:text-lime-300">Концертные DVD & Фильмы</a></li>
          <li><a href="index.php?category=merch" class="hover:text-lime-300">Официальный мерч</a></li>
          <li><a href="index.php?category=cd" class="hover:text-lime-300">Коллекционные CD</a></li>
        </ul>
      </div>
      <div>
        <h4 class="font-bold uppercase tracking-wider text-white mb-3">Для покупателей</h4>
        <ul class="space-y-2">
          <li><a href="index.php?page=cart" class="hover:text-lime-300">Корзина покупок</a></li>
          <li><a href="index.php?page=profile" class="hover:text-lime-300">Личный кабинет и заказы</a></li>
          <li><a href="index.php?page=login" class="hover:text-lime-300">Авторизация / Регистрация</a></li>
          <li><span class="text-zinc-600">Промокод на скидку: TRAVIS10 (-10%)</span></li>
        </ul>
      </div>
      <div>
        <h4 class="font-bold uppercase tracking-wider text-white mb-3">Информация</h4>
        <p class="text-zinc-500 leading-relaxed mb-2">
          Все товары являются оригинальными прессингами и официальной атрибутикой.
        </p>
        <div class="inline-flex items-center gap-2 rounded-full border border-lime-400/20 bg-lime-400/5 px-3 py-1 text-[11px] text-lime-300 font-mono">
          <span class="h-1.5 w-1.5 rounded-full bg-lime-400 animate-pulse"></span>
          Система онлайн 24/7
        </div>
      </div>
    </div>
    <div class="mt-8 border-t border-white/5 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px] text-zinc-600">
      <div>© <?=date('Y')?> VINYLWAVE inc. Все права защищены.</div>
      <div class="flex gap-4">
        <span>Dark / Cyber Edition</span>
        <span>•</span>
        <span>SoundHelix Previews</span>
      </div>
    </div>
  </div>
</footer>

<!-- Sticky Audio Player with Equalizer & Spinning Vinyl Disk -->
<div id="player" class="fixed bottom-0 left-0 right-0 z-50 hidden border-t border-white/10 bg-[#0d0d10]/95 p-3 backdrop-blur-2xl shadow-2xl transition-all">
  <div class="mx-auto flex max-w-7xl items-center gap-4 px-2">
    
    <!-- Spinning Disc Artwork -->
    <div class="relative hidden h-12 w-12 flex-shrink-0 sm:block">
      <div id="playerDisk" class="h-full w-full rounded-full border border-white/20 bg-zinc-950 overflow-hidden shadow-md">
        <img id="playerCover" class="h-full w-full object-cover" src="" alt="Album Cover">
      </div>
      <div class="absolute inset-[38%] rounded-full bg-[#0b0b0d] border border-lime-400/50 shadow-sm pointer-events-none"></div>
    </div>

    <!-- Track & Artist Info -->
    <div class="min-w-0 flex-1">
      <div class="flex items-center gap-2">
        <div id="playerTitle" class="truncate text-sm font-bold text-white">Превью трека</div>
        <span class="hidden sm:inline-block rounded bg-lime-400/10 px-1.5 py-0.5 text-[10px] font-mono font-bold text-lime-300">15s SAMPLE</span>
      </div>
      <div id="playerArtist" class="truncate text-xs text-zinc-400">Vinylwave Sound</div>
    </div>

    <!-- Equalizer Animation Bars -->
    <div id="playerEq" class="hidden sm:flex items-end gap-1 h-5 px-3">
      <div class="eq-bar h-1"></div>
      <div class="eq-bar h-2"></div>
      <div class="eq-bar h-3"></div>
      <div class="eq-bar h-1"></div>
    </div>

    <!-- Play/Pause Button -->
    <button id="playerToggle" type="button" class="flex h-10 w-10 items-center justify-center rounded-full bg-lime-300 text-black font-black text-sm shadow-[0_0_15px_rgba(185,255,44,0.4)] hover:scale-105 active:scale-95 transition-transform" title="Play/Pause">
      ❚❚
    </button>

    <!-- Progress Bar -->
    <div class="w-28 sm:w-60">
      <div class="h-1.5 overflow-hidden rounded-full bg-white/10 cursor-pointer">
        <div id="playerProgress" class="h-full w-0 bg-lime-300 shadow-[0_0_8px_rgba(185,255,44,0.6)] transition-all duration-100"></div>
      </div>
    </div>

  </div>
</div>

<!-- Modal: Video Preview / Music Video Player -->
<div id="videoModal" class="modal-overlay fixed inset-0 z-50 hidden items-center justify-center bg-black/85 backdrop-blur-md p-4">
  <div class="modal-content relative w-full max-w-4xl aspect-video rounded-3xl border border-white/20 bg-zinc-950 p-2 shadow-2xl">
    <button id="videoCloseBtn" type="button" class="absolute -top-4 -right-4 z-20 flex h-9 w-9 items-center justify-center rounded-full bg-zinc-800 text-white font-bold border border-white/20 hover:bg-lime-400 hover:text-black transition-colors" title="Закрыть">
      ✕
    </button>
    <div id="videoContainer" class="w-full h-full rounded-2xl overflow-hidden bg-black flex items-center justify-center">
      <!-- Iframe or video element injected dynamically -->
    </div>
  </div>
</div>

<!-- Modal: Review Photo Lightbox -->
<div id="lightboxModal" class="modal-overlay fixed inset-0 z-50 hidden items-center justify-center bg-black/90 backdrop-blur-xl p-4">
  <div class="modal-content relative max-w-3xl max-h-[90vh] rounded-3xl border border-white/20 bg-zinc-950 p-3 shadow-2xl overflow-hidden">
    <button id="lightboxCloseBtn" type="button" class="absolute top-4 right-4 z-20 flex h-10 w-10 items-center justify-center rounded-full bg-black/80 text-white font-bold border border-white/20 hover:bg-lime-400 hover:text-black transition-colors" title="Закрыть">
      ✕
    </button>
    <img id="lightboxImg" src="" alt="Фото отзыва" class="max-h-[82vh] w-auto mx-auto rounded-2xl object-contain">
  </div>
</div>

<script>
  window.vinylwaveUser = <?=json_encode(User::current() ? ['id' => (int)User::current()['id'], 'name' => User::current()['name']] : null) ?: 'null'?>;
  window.vinylwaveCsrf = <?=json_encode(Csrf::getToken())?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/wavesurfer.js@7.8.6/dist/wavesurfer.min.js"></script>
<script src="assets/js/app.js"></script>
<script>
  // PWA service worker registration
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('service-worker.js').catch(() => {});
    });
  }

  // Pass guest wishlist to the server on login/register so it can be merged
  (function () {
    var forms = document.querySelectorAll('form[action*="page=login"], form[action*="page=register"]');
    if (!forms.length) return;
    var ids = [];
    try { ids = JSON.parse(localStorage.getItem('vw-wishlist') || '[]'); } catch (e) {}
    forms.forEach(function (form) {
      var input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'guest_wishlist';
      input.value = JSON.stringify(ids);
      form.appendChild(input);
    });
  })();
</script>
</body>
</html>
