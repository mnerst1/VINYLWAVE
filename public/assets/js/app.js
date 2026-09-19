(() => {
  'use strict';

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

  function playPreview(url, title, cover, artist, triggerBtn) {
    if (!url) {
      window.showToast('Для этого трека превью еще загружается администратором', 'info');
      return;
    }

    if (audio) {
      audio.pause();
      clearInterval(timer);
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

    timer = setInterval(() => {
      const elapsed = (Date.now() - started) / 1000;
      if (playerProgress) {
        playerProgress.style.width = Math.min(100, (elapsed / 15) * 100) + '%';
      }
      if (elapsed >= 15) {
        audio.pause();
        setPlayingState(false);
        clearInterval(timer);
      }
    }, 100);
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
      setPlayingState(false);
    } else {
      audio.play();
      setPlayingState(true);
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

})();