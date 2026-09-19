<?php 
$title = $product['name'] . ' — VINYLWAVE'; 
require __DIR__ . '/partials/header.php'; 

$stock = (int)$product['stock'];
$hasVideo = !empty($product['video_url']);
$hasReviews = !empty($reviews);
$avgRating = $reviewSummary['average'] ?? 5.0;
$revCount = $reviewSummary['count'] ?? 0;
?>

<!-- Recently-viewed tracker + waveform anchor -->
<div data-recent-product="<?=$product['id']?>" class="hidden"></div>

<!-- Breadcrumbs -->
<div class="flex items-center gap-2 text-xs text-zinc-500 py-3 mb-4">
  <a href="index.php" class="hover:text-zinc-300">Каталог</a>
  <span>/</span>
  <a href="index.php?category=<?=$product['category']?>" class="uppercase hover:text-zinc-300"><?=htmlspecialchars($product['category'])?></a>
  <span>/</span>
  <span class="text-zinc-300 truncate max-w-xs sm:max-w-md"><?=htmlspecialchars($product['name'])?></span>
</div>

<div class="grid gap-12 lg:grid-cols-2 py-4">
  
  <!-- Left Column: Artwork, Vinyl Simulation & Media Trigger -->
  <div class="space-y-4">
    <div class="relative rounded-3xl border border-white/10 bg-[#131318] p-6 shadow-2xl overflow-hidden group">
      
      <!-- Stage with Vinyl Slide Effect -->
      <div class="record-stage relative aspect-square w-full rounded-2xl bg-zinc-950 overflow-hidden shadow-2xl">
        <img src="<?=htmlspecialchars($product['cover_url'])?>" class="sleeve absolute inset-0 z-10 h-full w-full object-cover rounded-2xl" alt="<?=htmlspecialchars($product['name'])?>">
        <div class="record"></div>

        <?php if ($product['is_limited']): ?>
          <span class="absolute left-4 top-4 z-20 rounded-full bg-lime-300 px-3 py-1 text-[11px] font-black uppercase tracking-wider text-black shadow-lg shimmer-badge">
            Limited Drop
          </span>
        <?php endif; ?>

        <!-- Category Badge -->
        <span class="absolute right-4 top-4 z-20 rounded-full bg-black/70 backdrop-blur-md border border-white/10 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-white">
          <?=htmlspecialchars($product['category'])?>
        </span>
      </div>

      <!-- Color variant pill -->
      <?php if (!empty($product['color_variant'])): ?>
        <div class="mt-4 flex items-center justify-between text-xs text-zinc-400 border-t border-white/5 pt-3">
          <span>Цветовой вариант прессинга:</span>
          <span class="font-bold text-lime-300 font-mono"><?=htmlspecialchars($product['color_variant'])?></span>
        </div>
      <?php endif; ?>
    </div>

    <!-- Media Video Clip Button (If Available) -->
    <?php if ($hasVideo): ?>
      <button type="button" data-video-trigger="<?=htmlspecialchars($product['video_url'])?>" class="w-full flex items-center justify-center gap-3 rounded-2xl border border-lime-400/40 bg-lime-400/10 p-4 text-xs font-black uppercase tracking-wider text-lime-300 hover:bg-lime-400/20 hover:border-lime-400 transition-all glow-card">
        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-lime-300 text-black text-xs font-black">▶</span>
        <span>Смотреть видео-клип / превью релиза</span>
        <span class="rounded bg-lime-400/20 px-1.5 py-0.5 text-[10px] font-mono">HD</span>
      </button>
    <?php endif; ?>
  </div>

  <!-- Right Column: Details, Audio Previews & Purchase Form -->
  <div class="space-y-6">
    <div>
      <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-lime-300 mb-2">
        <span><?=htmlspecialchars($product['artist_name'])?></span>
        <span>•</span>
        <span class="text-zinc-400"><?=htmlspecialchars($product['genre'])?></span>
      </div>
      <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white"><?=htmlspecialchars($product['name'])?></h1>
      
      <!-- Rating Summary Snippet -->
      <div class="mt-3 flex items-center gap-3">
        <a href="#reviews" class="flex items-center gap-1 text-sm font-bold text-amber-400 hover:underline">
          <span><?=str_repeat('★', (int)round($avgRating))?></span>
          <span class="text-white font-mono text-xs ml-1"><?=number_format($avgRating, 1)?></span>
        </a>
        <span class="text-xs text-zinc-500">·</span>
        <a href="#reviews" class="text-xs text-zinc-400 hover:text-white underline">
          <?=$revCount?> <?= ($revCount == 1 ? 'отзыв' : ($revCount < 5 ? 'отзыва' : 'отзывов')) ?> с фото
        </a>
      </div>

      <!-- Price & Stock Status -->
      <div class="mt-5 flex flex-wrap items-center gap-4">
        <div class="text-3xl sm:text-4xl font-black font-mono text-white">
          $<?=number_format((float)$product['price'], 2)?>
        </div>

        <!-- Wishlist heart -->
        <button type="button" class="wishlist-btn flex items-center gap-2 rounded-full border <?=$inWishlist ? 'border-red-400/60 bg-red-500/20 text-red-300' : 'border-white/10 bg-white/5 text-zinc-300'?> px-4 py-2 text-xs font-bold transition-all hover:scale-105"
                data-product-id="<?=$product['id']?>"
                data-active="<?=$inWishlist ? '1' : '0'?>">
          <span>♥</span>
          <span><?=$inWishlist ? 'В избранном' : 'В избранное'?></span>
        </button>

        <!-- Stock Indicator with Pulsing Microanimation -->
        <div class="flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3.5 py-1.5 text-xs">
          <?php if ($stock > 5): ?>
            <span class="h-2.5 w-2.5 rounded-full bg-emerald-400 pulse-dot-green"></span>
            <span class="text-emerald-300 font-semibold">В наличии (<?=$stock?> шт.)</span>
          <?php elseif ($stock > 0): ?>
            <span class="h-2.5 w-2.5 rounded-full bg-amber-400 pulse-dot-orange"></span>
            <span class="text-amber-300 font-semibold">Осталось мало: <?=$stock?> шт.!</span>
          <?php else: ?>
            <span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>
            <span class="text-red-400 font-semibold">Распродано (Sold Out)</span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <p class="leading-relaxed text-zinc-400 text-sm border-t border-white/5 pt-4">
      <?=htmlspecialchars($product['description'])?>
    </p>

    <!-- Limited Drop Countdown Box -->
    <?php if ($product['is_limited'] && $product['drop_ends_at']): ?>
      <div class="rounded-2xl border border-lime-400/30 bg-lime-400/5 p-4">
        <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-lime-300 mb-1">
          <span>⏳ Ограниченный тираж — до конца дропа:</span>
          <span class="font-mono text-[10px] text-zinc-400">LIMITED</span>
        </div>
        <div class="text-2xl font-black font-mono text-white" data-countdown="<?=htmlspecialchars($product['drop_ends_at'])?>">
          Загрузка таймера…
        </div>
      </div>
    <?php endif; ?>

    <!-- Purchase or Admin Edit Block -->
    <?php if ($currentUser && $currentUser['role'] === 'admin'): ?>
      <div class="rounded-2xl border border-lime-400/40 bg-lime-400/10 p-5 space-y-3 border-t">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="inline-block h-2.5 w-2.5 rounded-full bg-lime-400 animate-pulse"></span>
            <span class="text-xs font-black uppercase tracking-wider text-lime-300">Режим администратора</span>
          </div>
          <span class="rounded bg-black/40 px-2 py-0.5 text-[10px] font-mono text-zinc-400">Покупка отключена для админа</span>
        </div>
        <p class="text-xs text-zinc-400 leading-relaxed">
          Вы вошли как администратор. Вы можете изменить название, обложку, видео, треклист или скорректировать остатки на складе для этого товара:
        </p>
        <div class="pt-1">
          <a href="index.php?page=admin&tab=product-edit&edit_product_id=<?=$product['id']?>" class="flex w-full items-center justify-center gap-2 rounded-xl bg-lime-300 py-3.5 px-6 font-black uppercase tracking-wider text-black text-xs hover:bg-lime-400 hover:shadow-[0_0_25px_rgba(185,255,44,0.5)] transition-all">
            <span>✏</span> Изменить этот товар в панели управления →
          </a>
        </div>
      </div>
    <?php else: ?>
      <!-- Add to Cart Form (Customer & Guest) -->
      <form action="index.php?page=add" method="post" class="add-to-cart-form space-y-4 border-t border-white/5 pt-4">
        <input type="hidden" name="product_id" value="<?=$product['id']?>">
        <?=Csrf::input()?>

        <!-- Size Selector for Merch -->
        <?php if ($product['category'] === 'merch'): ?>
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-2">Выберите размер:</label>
            <div class="grid grid-cols-5 gap-2">
              <?php foreach (['S', 'M', 'L', 'XL', 'XXL'] as $idx => $s): ?>
                <label class="cursor-pointer">
                  <input type="radio" name="size" value="<?=$s?>" <?=$idx===1?'checked':''?> class="peer hidden">
                  <div class="rounded-xl border border-white/10 bg-zinc-900 py-2.5 text-center text-xs font-bold peer-checked:border-lime-300 peer-checked:bg-lime-400/10 peer-checked:text-lime-300 transition-all hover:border-white/30">
                    <?=$s?>
                  </div>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <div class="flex gap-3">
          <div class="w-24">
            <label class="block text-[10px] font-bold uppercase tracking-wider text-zinc-500 mb-1">Кол-во:</label>
            <input name="qty" type="number" min="1" max="<?=max(1, $stock)?>" value="1" <?=$stock===0?'disabled':''?> class="filter w-full text-center font-mono font-bold text-sm">
          </div>
          <div class="flex-1 self-end">
            <button type="submit" <?=$stock===0?'disabled':''?> class="w-full rounded-xl bg-lime-300 py-3.5 px-6 font-black uppercase tracking-wider text-black text-xs hover:bg-lime-400 hover:shadow-[0_0_25px_rgba(185,255,44,0.4)] disabled:opacity-40 disabled:pointer-events-none transition-all">
              <?=$stock===0?'Товар распродан':'Добавить в корзину →'?>
            </button>
          </div>
        </div>
      </form>
    <?php endif; ?>

    <!-- Tracklist with Audio Preview & Equalizer -->
    <?php if (!empty($tracks)): ?>
      <section class="border-t border-white/5 pt-6">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-xs font-bold uppercase tracking-widest text-zinc-400">Треклист и 15-сек аудио превью</h3>
          <span class="text-[10px] text-lime-300 font-mono">15s HQ AUDIO</span>
        </div>
        <!-- WaveSurfer waveform display -->
        <div id="waveformContainer" class="mb-3 rounded-2xl border border-white/5 bg-[#141419] p-3 opacity-40 transition-opacity"></div>
        <div class="space-y-2">
          <?php foreach ($tracks as $t): ?>
            <?php 
                $trackPreview = !empty($t['preview_url']) ? $t['preview_url'] : 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-' . (($product['id'] + $t['track_number']) % 8 + 1) . '.mp3';
            ?>
            <button type="button" class="track-btn group flex w-full items-center justify-between gap-3 rounded-2xl border border-white/5 bg-[#141419] p-3 text-left hover:border-lime-400/30 hover:bg-white/[0.04] transition-all" data-preview="<?=htmlspecialchars($trackPreview)?>" data-title="<?=htmlspecialchars($t['title'])?>" data-artist="<?=htmlspecialchars($product['artist_name'])?>" data-cover="<?=htmlspecialchars($product['cover_url'])?>">
              <div class="flex items-center gap-3 min-w-0">
                <span class="w-5 text-xs font-mono text-zinc-600 group-hover:text-zinc-400"><?=$t['track_number']?></span>
                <span class="track-play-icon flex h-6 w-6 items-center justify-center rounded-full bg-white/5 text-[10px] text-zinc-300 group-hover:bg-lime-300 group-hover:text-black transition-colors">▶</span>
                <span class="truncate text-xs font-semibold text-zinc-200 group-hover:text-white"><?=htmlspecialchars($t['title'])?></span>
              </div>
              
              <div class="flex items-center gap-3 flex-shrink-0">
                <!-- Track Mini Equalizer -->
                <div class="track-eq hidden sm:flex items-end gap-0.5 h-3">
                  <div class="eq-bar h-1"></div>
                  <div class="eq-bar h-2"></div>
                  <div class="eq-bar h-1"></div>
                </div>
                <span class="text-[11px] font-mono text-lime-300">15s</span>
              </div>
            </button>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

  </div>
</div>

<!-- CUSTOMER REVIEWS SECTION -->
<section id="reviews" class="mt-16 border-t border-white/10 pt-12">
  <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
    <div>
      <p class="text-xs font-bold uppercase tracking-[.25em] text-lime-300">Реальные отзывы покупателей</p>
      <h2 class="text-3xl font-black tracking-tight text-white mt-1">Отзывы и фото распаковки</h2>
    </div>
    <a href="#review-form" class="rounded-xl bg-white/10 px-4 py-2 text-xs font-bold text-white hover:bg-white/20">
      Оставить свой отзыв ↓
    </a>
  </div>

  <div class="grid gap-8 lg:grid-cols-[340px_1fr]">
    
    <!-- Left: Ratings Summary Score Card -->
    <div class="rounded-3xl border border-white/10 bg-[#121216] p-6 h-fit glow-card">
      <div class="text-center pb-6 border-b border-white/5">
        <div class="text-5xl font-black font-mono text-white"><?=number_format($avgRating, 1)?></div>
        <div class="text-amber-400 text-lg my-1"><?=str_repeat('★', (int)round($avgRating))?></div>
        <div class="text-xs text-zinc-500">На основе <?=$revCount?> оценок покупателей</div>
      </div>

      <!-- Rating Breakdown Bars -->
      <div class="mt-6 space-y-2 text-xs">
        <?php foreach ([5, 4, 3, 2, 1] as $star): 
          $cnt = $reviewSummary['breakdown'][$star] ?? 0;
          $pct = $revCount > 0 ? round(($cnt / $revCount) * 100) : 0;
        ?>
          <div class="flex items-center gap-2">
            <span class="w-10 text-zinc-400 font-mono"><?=$star?> ★</span>
            <div class="flex-1 h-2 rounded-full bg-white/10 overflow-hidden">
              <div class="h-full bg-amber-400 rounded-full" style="width: <?=$pct?>%"></div>
            </div>
            <span class="w-8 text-right text-zinc-500 font-mono text-[10px]"><?=$cnt?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Right: Reviews List & Write Review Form -->
    <div class="space-y-8">
      
      <!-- List of Reviews -->
      <?php if (empty($reviews)): ?>
        <div class="rounded-3xl border border-white/5 bg-[#121216]/50 p-8 text-center text-xs text-zinc-500">
          К этому релизу еще нет отзывов. Станьте первым, кто оставит отзыв с фотографией!
        </div>
      <?php else: ?>
        <div class="space-y-4">
          <?php foreach ($reviews as $rev): ?>
            <div class="rounded-2xl border border-white/10 bg-[#121216] p-5 glow-card">
              <div class="flex items-start justify-between gap-4 mb-3">
                <div class="flex items-center gap-3">
                  <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-zinc-800 text-sm font-bold text-lime-300">
                    <?=mb_strtoupper(mb_substr($rev['user_name'], 0, 1))?>
                  </div>
                  <div>
                    <div class="flex items-center gap-2">
                      <span class="font-bold text-sm text-white"><?=htmlspecialchars($rev['user_name'])?></span>
                      <span class="rounded bg-lime-400/10 px-1.5 py-0.5 text-[9px] font-bold text-lime-300 uppercase">
                        <?=$rev['user_role'] === 'admin' ? 'Команда Vinylwave' : 'Покупатель'?>
                      </span>
                    </div>
                    <div class="text-[10px] text-zinc-500"><?=date('d.m.Y', strtotime($rev['created_at']))?></div>
                  </div>
                </div>
                <div class="text-amber-400 text-sm font-mono">
                  <?=str_repeat('★', (int)$rev['rating'])?>
                </div>
              </div>

              <h4 class="text-sm font-bold text-zinc-200 mb-1"><?=htmlspecialchars($rev['title'])?></h4>
              <p class="text-xs text-zinc-400 leading-relaxed"><?=htmlspecialchars($rev['comment'])?></p>

              <!-- Attached Review Photo with Lightbox Zoom -->
              <?php if (!empty($rev['photo_url'])): ?>
                <div class="mt-4">
                  <div class="relative inline-block overflow-hidden rounded-xl border border-white/10 group cursor-pointer">
                    <img src="<?=htmlspecialchars($rev['photo_url'])?>" class="review-photo-zoom h-24 w-24 object-cover transition-transform group-hover:scale-105" alt="Фото от покупателя" data-full-src="<?=htmlspecialchars($rev['photo_url'])?>">
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-xs text-white font-bold transition-opacity pointer-events-none">
                      🔍 Зум
                    </div>
                  </div>
                  <span class="block text-[10px] text-zinc-500 mt-1">Фото покупателя (нажмите для зума)</span>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Write a Review Form -->
      <div id="review-form" class="rounded-3xl border border-white/10 bg-[#121216] p-6 sm:p-8 shadow-2xl">
        <h3 class="text-xl font-black mb-1 text-white">Оставить отзыв о релизе</h3>
        <p class="text-xs text-zinc-400 mb-6">Поделитесь вашим впечатлением от прессинга, звука или мерча, прикрепив фото</p>

        <?php if ($currentUser): ?>
          <form method="post" action="index.php?page=review-add" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="product_id" value="<?=$product['id']?>">
            <?=Csrf::input()?>

            <!-- Interactive Star Rating Picker -->
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-2">Ваша оценка:</label>
              <div class="flex items-center gap-4">
                <div id="interactiveStarPicker" class="star-rating-group">
                  <input type="radio" id="star5" name="rating" value="5" checked><label for="star5" title="5 звезд">★</label>
                  <input type="radio" id="star4" name="rating" value="4"><label for="star4" title="4 звезды">★</label>
                  <input type="radio" id="star3" name="rating" value="3"><label for="star3" title="3 звезды">★</label>
                  <input type="radio" id="star2" name="rating" value="2"><label for="star2" title="2 звезды">★</label>
                  <input type="radio" id="star1" name="rating" value="1"><label for="star1" title="1 звезда">★</label>
                </div>
                <span id="starRatingLabel" class="text-xs font-bold text-lime-300">5 звёзд — Настоящий шедевр!</span>
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1.5">Заголовок отзыва *</label>
              <input type="text" name="title" required placeholder="Например: Качество прессинга и оформление — топ!" class="filter w-full text-sm">
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1.5">Текст отзыва *</label>
              <textarea name="comment" required rows="3" placeholder="Расскажите о звучании, доставке, качестве конверта..." class="filter w-full text-sm"></textarea>
            </div>

            <!-- Photo Upload with Live Preview -->
            <div class="rounded-2xl border border-white/5 bg-white/[0.02] p-4">
              <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-2">
                Прикрепить фотографию распаковки / товара:
              </label>
              <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                <input type="file" name="photo" accept="image/*" data-preview-target="#reviewPhotoPreview" class="text-xs text-zinc-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-lime-400 file:text-black hover:file:bg-lime-300">
                <div id="reviewPhotoPreviewContainer" class="hidden">
                  <img id="reviewPhotoPreview" src="" class="h-16 w-16 rounded-xl object-cover border border-white/20">
                </div>
              </div>
            </div>

            <button type="submit" class="rounded-xl bg-lime-300 px-6 py-3 text-xs font-black uppercase tracking-wider text-black hover:bg-lime-400 hover:shadow-[0_0_20px_rgba(185,255,44,0.4)] transition-all">
              Опубликовать отзыв с фото →
            </button>
          </form>
        <?php else: ?>
          <!-- Guest Call-to-action Banner -->
          <div class="rounded-2xl border border-lime-400/20 bg-lime-400/5 p-6 text-center">
            <div class="text-3xl mb-2">📸</div>
            <h4 class="text-base font-bold text-white">Хотите оставить отзыв с фото?</h4>
            <p class="text-xs text-zinc-400 max-w-md mx-auto mt-1 mb-5">
              Просматривать товары и слушать треки можно как гость без ограничений, а для публикации отзывов с фото войдите в свой аккаунт.
            </p>
            <div class="flex justify-center gap-3">
              <a href="index.php?page=login&return_to=<?=urlencode("index.php?page=product&id={$product['id']}#reviews")?>" class="rounded-xl bg-lime-300 px-5 py-2.5 text-xs font-bold text-black hover:bg-lime-400">
                Войти в аккаунт
              </a>
              <a href="index.php?page=register" class="rounded-xl border border-white/10 bg-white/5 px-5 py-2.5 text-xs font-semibold text-zinc-300 hover:bg-white/10">
                Регистрация
              </a>
            </div>
          </div>
        <?php endif; ?>

      </div>

    </div>

  </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
