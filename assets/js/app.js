(() => {
  'use strict';

  // --- 0. THEME TOGGLE ---
  function initTheme() {
    const toggleBtn = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    if (!toggleBtn || !themeIcon) return;

    const saved = localStorage.getItem('vw-theme');
    const isLight = saved === 'light';
    if (isLight) document.documentElement.classList.add('light-theme');
    themeIcon.textContent = isLight ? '☀️' : '🌙';

    toggleBtn.addEventListener('click', () => {
      const nowLight = document.documentElement.classList.toggle('light-theme');
      localStorage.setItem('vw-theme', nowLight ? 'light' : 'dark');
      themeIcon.textContent = nowLight ? '☀️' : '🌙';
    });
  }

  // Run early (before DOMContentLoaded to avoid flash)
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTheme);
  } else {
    initTheme();
  }

  // --- 1. TOAST NOTIFICATIONS ---
  const toastContainer = document.createElement('div');
  toastContainer.id = 'toast-container';
  document.body.appendChild(toastContainer);

  window.showToast = function(message, type = 'success') {
    if (!message) return;
    const toast = document.createElement('div');
    toast.className = `toast-msg toast-${type}`;

    let icon = '✦';
    if (type === 'error') icon = '⚠';
    else if (type === 'info') icon = 'ℹ';

    toast.innerHTML = `<span class="text-lime-300 font-bold">${icon}</span><span class="flex-1">${message}</span>`;
    toastContainer.appendChild(toast);

    setTimeout(() => {
      toast.style.animation = 'toastOut 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards';
      setTimeout(() => toast.remove(), 350);
    }, 4000);
  };

  // Check for session toast in dataset or global
  const serverToast = document.querySelector('#server-toast');
  if (serverToast && serverToast.dataset.message) {
    window.showToast(serverToast.dataset.message, serverToast.dataset.type || 'success');
  }

  // --- 2. AUDIO PLAYER & EQUALIZER ---
  let audio = null;
  let timer = null;
  let playing = false;
  let started = 0;
  let pausedAt = 0;
  let currentBtn = null;

  const player = document.querySelector('#player');
  const toggle = document.querySelector('#playerToggle');
  const playerCover = document.querySelector('#playerCover');
  const playerDisk = document.querySelector('#playerDisk');
  const playerTitle = document.querySelector('#playerTitle');
  const playerArtist = document.querySelector('#playerArtist');
  const playerProgress = document.querySelector('#playerProgress');
  const playerEq = document.querySelector('#playerEq');

  function setPlayingState(isPlaying) {
    playing = isPlaying;
    if (toggle) toggle.innerHTML = isPlaying ? '❚❚' : '▶';

    if (playerDisk) {
      if (isPlaying) playerDisk.classList.add('vinyl-spin');
      else playerDisk.classList.remove('vinyl-spin');
    }
    if (playerEq) {
      if (isPlaying) playerEq.classList.add('eq-active');
      else playerEq.classList.remove('eq-active');
    }
    if (currentBtn) {
      const eq = currentBtn.querySelector('.track-eq');
      if (eq) {
        if (isPlaying) eq.classList.add('eq-active');
        else eq.classList.remove('eq-active');
      }
      const icon = currentBtn.querySelector('.track-play-icon');
      if (icon) icon.textContent = isPlaying ? '❚❚' : '▶';
    }
  }

  function updateProgress() {
    if (!audio || !playerProgress) return;
    const elapsed = (Date.now() - started) / 1000;
    playerProgress.style.width = Math.min(100, (elapsed / 15) * 100) + '%';
    if (elapsed >= 15) {
      audio.pause();
      setPlayingState(false);
      clearInterval(timer);
      timer = null;
      // Auto-hide player after preview ends
      setTimeout(() => {
        if (player) player.classList.add('hidden');
      }, 500);
    }
  }

  function playPreview(url, title, cover, artist, triggerBtn) {
    if (!url) {
      window.showToast('Для этого трека превью еще загружается администратором', 'info');
      return;
    }

    if (audio) {
      audio.pause();
      clearInterval(timer);
      timer = null;
      if (currentBtn) {
        const eq = currentBtn.querySelector('.track-eq');
        if (eq) eq.classList.remove('eq-active');
        const icon = currentBtn.querySelector('.track-play-icon');
        if (icon) icon.textContent = '▶';
      }
    }

    currentBtn = triggerBtn || null;
    audio = new Audio(url);
    audio.currentTime = 0;
    started = Date.now();
    pausedAt = 0;
    audio.volume = 0.8;

    if (playerTitle) playerTitle.textContent = title || 'Preview';
    if (playerArtist) playerArtist.textContent = artist || 'Vinylwave Sound';
    if (playerCover) playerCover.src = cover || '';
    if (player) player.classList.remove('hidden');

    setPlayingState(true);
    audio.play().catch(e => {
      console.warn("Audio playback error:", e);
      setPlayingState(false);
    });

    timer = setInterval(updateProgress, 100);
  }

  document.querySelectorAll('.preview-btn, .track-btn').forEach(b => {
    b.addEventListener('click', (e) => {
      e.stopPropagation();
      playPreview(b.dataset.preview, b.dataset.title, b.dataset.cover, b.dataset.artist, b);
    });
  });

  toggle?.addEventListener('click', () => {
    if (!audio) return;
    if (playing) {
      audio.pause();
      pausedAt = audio.currentTime;
      clearInterval(timer);
      timer = null;
      setPlayingState(false);
    } else {
      // Resume from paused position
      audio.currentTime = pausedAt;
      started = Date.now() - (pausedAt * 1000);
      audio.play();
      setPlayingState(true);
      timer = setInterval(updateProgress, 100);
    }
  });

  // --- 3. VIDEO MODAL ---
  const videoModal = document.querySelector('#videoModal');
  const videoContainer = document.querySelector('#videoContainer');
  const videoCloseBtn = document.querySelector('#videoCloseBtn');

  function openVideo(url) {
    if (!videoModal || !videoContainer || !url) return;

    let embedHtml = '';
    // YouTube
    const ytMatch = url.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/);
    if (ytMatch && ytMatch[1]) {
      embedHtml = `<iframe class="w-full h-full rounded-2xl" src="https://www.youtube.com/embed/${ytMatch[1]}?autoplay=1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`;
    } else {
      // Direct MP4 / HTML5 Video
      embedHtml = `<video class="w-full h-full rounded-2xl" src="${url}" controls autoplay></video>`;
    }

    videoContainer.innerHTML = embedHtml;
    videoModal.classList.remove('hidden');
    videoModal.classList.add('active', 'flex');
    document.body.style.overflow = 'hidden';
  }

  function closeVideo() {
    if (!videoModal) return;
    videoModal.classList.remove('active', 'flex');
    videoModal.classList.add('hidden');
    if (videoContainer) videoContainer.innerHTML = '';
    document.body.style.overflow = '';
  }

  document.querySelectorAll('[data-video-trigger]').forEach(btn => {
    btn.addEventListener('click', () => openVideo(btn.dataset.videoTrigger));
  });
  videoCloseBtn?.addEventListener('click', closeVideo);
  videoModal?.addEventListener('click', (e) => {
    if (e.target === videoModal) closeVideo();
  });

  // --- 4. REVIEW PHOTO LIGHTBOX ---
  const lightboxModal = document.querySelector('#lightboxModal');
  const lightboxImg = document.querySelector('#lightboxImg');
  const lightboxCloseBtn = document.querySelector('#lightboxCloseBtn');

  function openLightbox(src) {
    if (!lightboxModal || !lightboxImg || !src) return;
    lightboxImg.src = src;
    lightboxModal.classList.remove('hidden');
    lightboxModal.classList.add('active', 'flex');
    document.body.style.overflow = 'hidden';
  }

  function closeLightbox() {
    if (!lightboxModal) return;
    lightboxModal.classList.remove('active', 'flex');
    lightboxModal.classList.add('hidden');
    document.body.style.overflow = '';
  }

  document.querySelectorAll('.review-photo-zoom').forEach(img => {
    img.addEventListener('click', () => openLightbox(img.dataset.fullSrc || img.src));
  });
  lightboxCloseBtn?.addEventListener('click', closeLightbox);
  lightboxModal?.addEventListener('click', (e) => {
    if (e.target === lightboxModal) closeLightbox();
  });

  // Close modals on Escape key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeVideo();
      closeLightbox();
    }
  });

  // --- 5. IMAGE UPLOAD LIVE PREVIEW ---
  document.querySelectorAll('input[type="file"][data-preview-target]').forEach(input => {
    input.addEventListener('change', () => {
      const targetId = input.dataset.previewTarget;
      const targetImg = document.querySelector(targetId);
      const targetContainer = document.querySelector(targetId + 'Container');

      if (input.files && input.files[0] && targetImg) {
        const reader = new FileReader();
        reader.onload = (e) => {
          targetImg.src = e.target.result;
          if (targetContainer) targetContainer.classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
      }
    });
  });

  // --- 6. INTERACTIVE STAR RATING PICKER ---
  const ratingPicker = document.querySelector('#interactiveStarPicker');
  const ratingLabel = document.querySelector('#starRatingLabel');
  if (ratingPicker) {
    const labels = {
      1: '1 звезда — Очень плохо',
      2: '2 звезды — Слабо',
      3: '3 звезды — Нормально',
      4: '4 звезды — Отличный релиз',
      5: '5 звёзд — Настоящий шедевр!'
    };
    ratingPicker.querySelectorAll('input[type="radio"]').forEach(radio => {
      radio.addEventListener('change', () => {
        if (ratingLabel) {
          ratingLabel.textContent = labels[radio.value] || `${radio.value} звезд`;
        }
      });
    });
  }

  // --- 7. ADD TO CART MICRO-INTERACTIONS ---
  document.querySelectorAll('.add-to-cart-form').forEach(form => {
    form.addEventListener('submit', (e) => {
      const submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn) {
        submitBtn.innerHTML = '<span class="inline-block animate-bounce">✓</span> Добавлено!';
        submitBtn.classList.add('bg-lime-400', 'text-black');
      }
      const cartBadge = document.querySelector('#headerCartBadge');
      if (cartBadge) {
        cartBadge.classList.add('badge-pop');
        setTimeout(() => cartBadge.classList.remove('badge-pop'), 400);
      }
    });
  });

  // --- 8. LIMITED DROP COUNTDOWN ---
  document.querySelectorAll('[data-countdown]').forEach(el => {
    const dateStr = el.dataset.countdown.replace(' ', 'T');
    const end = new Date(dateStr).getTime();
    const tick = () => {
      const d = Math.max(0, end - Date.now());
      const days = Math.floor(d / (1000 * 60 * 60 * 24));
      const hours = Math.floor((d % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      const mins = Math.floor((d % (1000 * 60 * 60)) / (1000 * 60));
      const secs = Math.floor((d % (1000 * 60)) / 1000);

      if (d <= 0) {
        el.textContent = 'Дроп завершен';
        return;
      }

      if (days > 0) {
        el.textContent = `${days}д ${String(hours).padStart(2, '0')}ч ${String(mins).padStart(2, '0')}м ${String(secs).padStart(2, '0')}с`;
      } else {
        el.textContent = `${String(hours).padStart(2, '0')}ч ${String(mins).padStart(2, '0')}м ${String(secs).padStart(2, '0')}с`;
      }
    };
    tick();
    setInterval(tick, 1000);
  });

  // --- 9. THEME TOGGLE (persisted in localStorage) ---
  const themeToggle = document.querySelector('#themeToggle');
  const themeIcon = document.querySelector('#themeIcon');
  function applyTheme(theme) {
    document.documentElement.classList.toggle('light-theme', theme === 'light');
    if (themeIcon) themeIcon.textContent = theme === 'light' ? '☀️' : '🌙';
    try { localStorage.setItem('vw-theme', theme); } catch (e) {}
  }
  applyTheme(document.documentElement.classList.contains('light-theme') ? 'light' : 'dark');
  themeToggle?.addEventListener('click', () => {
    applyTheme(document.documentElement.classList.contains('light-theme') ? 'dark' : 'light');
  });

  // --- 10. WISHLIST (guest: localStorage, user: DB via toggle endpoint) ---
  function getWishlist() {
    try { return JSON.parse(localStorage.getItem('vw-wishlist') || '[]'); } catch (e) { return []; }
  }
  function setWishlist(ids) {
    try { localStorage.setItem('vw-wishlist', JSON.stringify(ids)); } catch (e) {}
  }
  function updateWishlistBadges() {
    const badge = document.querySelector('#headerWishlistBadge');
    if (!badge) return;
    if (!(window.vinylwaveUser && window.vinylwaveUser.id)) {
      badge.textContent = getWishlist().length;
    }
  }
  window.vinylwaveWishlistIds = window.vinylwaveWishlistIds || getWishlist();

  function bindWishlistButtons(root = document) {
    root.querySelectorAll('.wishlist-btn:not([data-bound])').forEach(btn => {
      btn.dataset.bound = '1';
      btn.addEventListener('click', async (e) => {
        e.preventDefault();
        e.stopPropagation();
        const pid = parseInt(btn.dataset.productId, 10);
        const isUser = window.vinylwaveUser && window.vinylwaveUser.id;
        let active;

        if (isUser) {
          try {
            const res = await fetch('index.php?page=wishlist-toggle', {
              method: 'POST',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: new URLSearchParams({ csrf_token: window.vinylwaveCsrf || '', product_id: pid })
            });
            const data = await res.json();
            active = data.active;
            if (data.guest) active = null;
          } catch (err) {
            window.showToast('Ошибка связи с сервером', 'error');
            return;
          }
        }

        if (active === null || active === undefined) {
          // Guest mode: localStorage only
          const ids = getWishlist();
          const idx = ids.indexOf(pid);
          if (idx >= 0) { ids.splice(idx, 1); active = false; } else { ids.push(pid); active = true; }
          setWishlist(ids);
          window.vinylwaveWishlistIds = ids;
          window.showToast(active ? 'Добавлено в избранное' : 'Удалено из избранного', 'info');
        } else {
          window.showToast(active ? 'Добавлено в избранное' : 'Удалено из избранного', 'info');
        }

        document.querySelectorAll(`.wishlist-btn[data-product-id="${pid}"]`).forEach(b => {
          b.dataset.active = active ? '1' : '0';
          b.classList.toggle('border-red-400/60', active);
          b.classList.toggle('bg-red-500/20', active);
          b.classList.toggle('text-red-300', active);
          b.classList.toggle('border-white/15', !active);
          b.classList.toggle('bg-black/70', !active);
          b.classList.toggle('text-zinc-300', !active);
        });
        updateWishlistBadges();
      });
    });
  }
  bindWishlistButtons();
  window.bindWishlistButtons = bindWishlistButtons;

  // --- 11. COMPARE (localStorage, max 4) ---
  function getCompare() {
    try { return JSON.parse(localStorage.getItem('vw-compare') || '[]'); } catch (e) { return []; }
  }
  function setCompare(ids) {
    try { localStorage.setItem('vw-compare', JSON.stringify(ids.slice(0, 4))); } catch (e) {}
  }
  function updateCompareBadge() {
    const badge = document.querySelector('#headerCompareBadge');
    if (badge) badge.textContent = getCompare().length;
  }
  updateCompareBadge();

  document.querySelectorAll('.compare-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      const pid = parseInt(btn.dataset.productId, 10);
      let ids = getCompare();
      const idx = ids.indexOf(pid);
      if (idx >= 0) {
        ids.splice(idx, 1);
        window.showToast('Удалено из сравнения', 'info');
      } else if (ids.length >= 4) {
        window.showToast('Максимум 4 товара для сравнения', 'error');
        return;
      } else {
        ids.push(pid);
        window.showToast('Добавлено к сравнению', 'info');
      }
      setCompare(ids);
      updateCompareBadge();
    });
  });

  // --- 12. RECENTLY VIEWED (localStorage, product pages) ---
  (function () {
    const el = document.querySelector('[data-recent-product]');
    if (!el) return;
    const pid = parseInt(el.dataset.recentProduct, 10);
    if (!pid) return;
    let ids = [];
    try { ids = JSON.parse(localStorage.getItem('vw-recent') || '[]'); } catch (e) {}
    ids = [pid, ...ids.filter(x => x !== pid)].slice(0, 8);
    try { localStorage.setItem('vw-recent', JSON.stringify(ids)); } catch (e) {}
  })();

  // Recently viewed strip on catalog page
  (async function () {
    const row = document.querySelector('#recentlyViewedRow');
    if (!row) return;
    let ids = [];
    try { ids = JSON.parse(localStorage.getItem('vw-recent') || '[]'); } catch (e) {}
    if (!ids.length) return;
    try {
      const res = await fetch('index.php?page=recent-api&ids=' + ids.join(','));
      const products = await res.json();
      if (!Array.isArray(products) || !products.length) return;
      row.innerHTML = products.map(p => `
        <a href="index.php?page=product&id=${p.id}" class="group flex-shrink-0 w-36 rounded-2xl border border-white/10 bg-[#121216] p-2 hover:border-lime-300/40 transition-all">
          <img src="${p.cover_thumb_url || p.cover_url}" class="aspect-square w-full rounded-xl object-cover mb-1.5" loading="lazy" alt="">
          <div class="truncate text-[11px] font-bold text-white group-hover:text-lime-300">${p.name}</div>
          <div class="truncate text-[10px] text-zinc-500">${p.artist_name}</div>
          <div class="text-[11px] font-mono font-bold text-lime-300">$${parseFloat(p.price).toFixed(2)}</div>
        </a>
      `).join('');
      document.querySelector('#recently-viewed-section')?.classList.remove('hidden');
      document.querySelector('#recentClearBtn')?.addEventListener('click', () => {
        localStorage.removeItem('vw-recent');
        document.querySelector('#recently-viewed-section')?.classList.add('hidden');
      });
    } catch (e) { /* strip is optional */ }
  })();

  // --- 13. LOAD MORE / INFINITE SCROLL ---
  (function () {
    const btn = document.querySelector('#loadMoreBtn');
    if (!btn) return;
    const grid = document.querySelector('#catalogGrid');
    const sentinel = document.querySelector('#catalogSentinel');
    let loading = false;
    let offset = parseInt(btn.dataset.offset, 10) || 0;
    const filterQuery = btn.dataset.total || '';

    async function loadMore() {
      if (loading) return;
      loading = true;
      btn.disabled = true;
      const original = btn.innerHTML;
      btn.innerHTML = 'Загрузка…';
      try {
        const res = await fetch('index.php?page=load-more&offset=' + offset + '&' + filterQuery);
        const data = await res.json();
        if (data.html) {
          grid.insertAdjacentHTML('beforeend', data.html);
          bindWishlistButtons(grid);
          // Highlight freshly loaded cards' wishlist state from localStorage (guests)
          if (!(window.vinylwaveUser && window.vinylwaveUser.id)) {
            grid.querySelectorAll('.wishlist-btn[data-active="0"]').forEach(b => {
              const pid = parseInt(b.dataset.productId, 10);
              if (getWishlist().includes(pid)) {
                b.dataset.active = '1';
                b.classList.add('border-red-400/60', 'bg-red-500/20', 'text-red-300');
                b.classList.remove('border-white/15', 'bg-black/70', 'text-zinc-300');
              }
            });
          }
          offset = data.nextOffset;
        }
        if (!data.hasMore) {
          btn.remove();
          if (sentinel) observer.disconnect();
        } else {
          btn.innerHTML = original;
          btn.disabled = false;
        }
      } catch (e) {
        btn.innerHTML = original;
        btn.disabled = false;
        window.showToast('Не удалось загрузить товары', 'error');
      }
      loading = false;
    }

    btn.addEventListener('click', loadMore);

    // Infinite scroll: auto-load when sentinel becomes visible
    if (sentinel && 'IntersectionObserver' in window) {
      const observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && document.contains(btn)) loadMore();
      }, { rootMargin: '400px' });
      observer.observe(sentinel);
    }
  })();

  // --- 14. DRAG-AND-DROP TRACK REORDERING (admin product edit) ---
  window.vinylwaveInitDrag = function () {
    const container = document.querySelector('#tracksContainer');
    if (!container) return;
    let dragged = null;

    container.querySelectorAll('.track-row[draggable="true"]').forEach(row => {
      if (row.dataset.dragBound) return;
      row.dataset.dragBound = '1';

      row.addEventListener('dragstart', (e) => {
        dragged = row;
        row.style.opacity = '0.4';
        e.dataTransfer.effectAllowed = 'move';
      });
      row.addEventListener('dragend', () => {
        row.style.opacity = '';
      });
      row.addEventListener('dragover', (e) => {
        e.preventDefault();
        if (dragged && dragged !== row) {
          const rect = row.getBoundingClientRect();
          const after = (e.clientY - rect.top) > rect.height / 2;
          container.insertBefore(dragged, after ? row.nextSibling : row);
        }
      });
    });
  };
  window.vinylwaveInitDrag();

  // --- 14b. BULK SELECTION UI (admin products) ---
  (function () {
    const checkAll = document.querySelector('#checkAllProducts');
    if (!checkAll) return;
    const countEl = document.querySelector('#bulkCount');
    const bulkBox = document.querySelector('#bulkForm');

    const updateCount = () => {
      const n = bulkBox ? bulkBox.querySelectorAll('.bulk-check:checked').length : 0;
      if (countEl) countEl.textContent = n;
    };

    checkAll.addEventListener('change', () => {
      document.querySelectorAll('.bulk-check').forEach(cb => { cb.checked = checkAll.checked; });
      updateCount();
    });
    document.querySelectorAll('.bulk-check').forEach(cb => cb.addEventListener('change', updateCount));

    // Submit the bulk action by building a standalone form (rows live outside it)
    document.querySelector('#bulkApplyBtn')?.addEventListener('click', () => {
      if (!bulkBox) return;
      const action = bulkBox.querySelector('[name=bulk_action]')?.value || '';
      const ids = Array.from(bulkBox.querySelectorAll('.bulk-check:checked')).map(cb => cb.value);
      if (!action || ids.length === 0) {
        window.showToast('Выберите товары и действие', 'error');
        return;
      }
      if (action === 'delete' && !confirm('Удалить выбранные товары (' + ids.length + ')? Действие необратимо.')) {
        return;
      }
      const form = document.createElement('form');
      form.method = 'post';
      form.action = 'index.php?page=admin-product-bulk';
      const add = (name, value) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        form.appendChild(input);
      };
      add('csrf_token', bulkBox.dataset.csrf || '');
      add('bulk_action', action);
      if (action === 'stock') add('bulk_stock', bulkBox.querySelector('[name=bulk_stock]')?.value || '0');
      ids.forEach(id => add('product_ids[]', id));
      document.body.appendChild(form);
      form.submit();
    });
  })();

  // --- 15. WaveSurfer waveform on product page (visual only; audio via sticky player) ---
  (function () {
    const waveEl = document.querySelector('#waveformContainer');
    if (!waveEl || typeof WaveSurfer === 'undefined') return;
    let ws = null;

    function ensureWave(url) {
      if (ws) { ws.destroy(); }
      waveEl.classList.add('opacity-40');
      ws = WaveSurfer.create({
        container: '#waveformContainer',
        waveColor: '#3f3f46',
        progressColor: '#b9ff2c',
        cursorColor: '#b9ff2c',
        barWidth: 2,
        barGap: 1,
        barRadius: 2,
        height: 64,
        url: url
      });
      ws.on('ready', () => {
        waveEl.classList.remove('opacity-40');
        ws.setTime(0);
      });
    }

    // Mirror the sticky player's progress onto the waveform
    document.querySelectorAll('.track-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const url = btn.dataset.preview;
        if (url) ensureWave(url);
      });
    });
  })();

})();