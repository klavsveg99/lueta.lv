(function () {
  // ── CONFIG ── Fill these with your Supabase project details ──
  var CONFIG = {
    supabaseUrl: 'https://nyrzjdotaxacvjomthll.supabase.co',
    supabaseKey: 'REPLACE_ANON_KEY',
  };
  // ──────────────────────────────────────────────────────────────

  var page = document.documentElement.lang === 'en' ? 'en' : 'index';
  if (!CONFIG.supabaseUrl || !CONFIG.supabaseKey) return;

  var api = CONFIG.supabaseUrl.replace(/\/+$/, '') + '/rest/v1';

  function headers() {
    return {
      apikey: CONFIG.supabaseKey,
      Authorization: 'Bearer ' + CONFIG.supabaseKey,
    };
  }

  function fetchJson(url) {
    return fetch(url, { headers: headers() }).then(function (r) {
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.json();
    });
  }

  function fetchBlocks() {
    var q = encodeURIComponent('page') + '=eq.' + encodeURIComponent(page);
    return fetchJson(api + '/content_blocks?' + q + '&select=block_key,block_value').then(function (rows) {
      var map = {};
      rows.forEach(function (r) { map[r.block_key] = r.block_value; });
      return map;
    });
  }

  function fetchGlobalImages() {
    var q = 'page=eq.index';
    return fetchJson(api + '/content_blocks?' + q + '&select=block_key,block_value').then(function (rows) {
      var map = {};
      rows.forEach(function (r) {
        if (r.block_key === 'hero_images' || r.block_key === 'missis_images') {
          map[r.block_key] = r.block_value;
        }
      });
      return map;
    });
  }

  function fetchItems(table) {
    var q = encodeURIComponent('page') + '=eq.' + encodeURIComponent(page);
    return fetchJson(api + '/' + table + '?' + q + '&order=' + encodeURIComponent('display_order.asc'));
  }

  function fetchFallbackImages() {
    return fetch('get-images.php').then(function (r) {
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.json();
    }).catch(function () { return []; });
  }

  // Start fetching immediately (each failure returns empty defaults)
  var safe = function (p, def) { return p.catch(function () { return def; }); };
  var dataPromise = Promise.all([
    safe(fetchBlocks(), {}),
    safe(fetchItems('services'), []),
    safe(fetchItems('testimonials'), []),
    safe(fetchItems('experiences'), []),
    safe(fetchGlobalImages(), {}),
    safe(fetchFallbackImages(), [])
  ]);

  function domReady(fn) {
    if (document.readyState !== 'loading') { fn(); }
    else { document.addEventListener('DOMContentLoaded', fn); }
  }

  function escapeHtml(str) {
    var d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
  }

  function applyBlocks(blocks, fallbackImages) {
    Object.keys(blocks).forEach(function (key) {
      var val = blocks[key];
      if (val == null || val === '') return;
      var el = document.querySelector('[data-cms="' + key + '"]');
      if (!el) return;
      if (el.dataset.cmsType === 'html') {
        el.innerHTML = val;
      } else if (el.dataset.cmsType === 'list') {
        var items;
        try { items = JSON.parse(val); } catch(e) {}
        if (!Array.isArray(items)) { items = val.split('\n').filter(Boolean); }
        if (items.length === 0) return;
        el.innerHTML = items.map(function (item) { return '<li>' + escapeHtml(item) + '</li>'; }).join('');
      } else {
        el.textContent = val;
      }
      if (el.dataset.count !== undefined && !isNaN(Number(val))) {
        el.dataset.count = val.replace(/\D/g, '');
      }
    });

    var setTitle = function (sel, line1, line2) {
      if (!line1 && !line2) return;
      var el = document.querySelector(sel);
      if (el) el.innerHTML = (line1 || '') + '<br>' + (line2 || '');
    };
    setTitle('.about .section-title', blocks['about_title_line1'], blocks['about_title_line2']);
    setTitle('.services .section-title', blocks['services_title_line1'], blocks['services_title_line2']);
    setTitle('.missis h2', blocks['missis_title_line1'], blocks['missis_title_line2']);
    setTitle('.experience .section-title', blocks['exp_title_line1'], blocks['exp_title_line2']);
    setTitle('.contact .section-title', blocks['contact_title_line1'], blocks['contact_title_line2']);

    if (blocks['hero_tag']) {
      var tagEl = document.querySelector('.hero-tag');
      if (tagEl) {
        var sp = tagEl.querySelector('.hero-tag-line');
        tagEl.innerHTML = '';
        if (sp) tagEl.appendChild(sp);
        tagEl.appendChild(document.createTextNode(' ' + blocks['hero_tag']));
      }
    }
    if (blocks['hero_title']) {
      var h1 = document.querySelector('.hero h1');
      if (h1) h1.innerHTML = blocks['hero_title'];
    }
    if (blocks['contact_cta_title']) {
      var ctaT = document.querySelector('.contact-cta h3');
      if (ctaT) ctaT.innerHTML = blocks['contact_cta_title'].replace(/\n/g, '<br>');
    }
    if (blocks['contact_cta_text']) {
      var ctaP = document.querySelector('.contact-cta p');
      if (ctaP) ctaP.textContent = blocks['contact_cta_text'];
    }
    if (blocks['contact_cta_btn']) {
      var ctaB = document.querySelector('.contact-cta .btn-primary');
      if (ctaB) ctaB.innerHTML = blocks['contact_cta_btn'] + ' <i class="fa-solid fa-arrow-right"></i>';
    }
    if (blocks['contact_email']) {
      var eEls = document.querySelectorAll('[data-cms="contact_email"]');
      eEls.forEach(function (e) { e.innerHTML = '<i class="fa-solid fa-envelope"></i> ' + escapeHtml(blocks['contact_email']); if (e.tagName === 'A') e.href = 'mailto:' + blocks['contact_email']; });
    }
    if (blocks['contact_location']) {
      var lEl = document.querySelector('.contact-link[data-cms="contact_location"]');
      if (lEl) lEl.innerHTML = '<i class="fa-solid fa-location-dot"></i> ' + blocks['contact_location'];
    }
    if (blocks['missis_url']) {
      var mBtn = document.querySelector('.missis .btn-primary');
      if (mBtn) mBtn.href = blocks['missis_url'];
    }
    if (blocks['missis_btn']) {
      var mBtnT = document.querySelector('.missis .btn-primary');
      if (mBtnT) mBtnT.innerHTML = blocks['missis_btn'] + ' <i class="fa-solid fa-arrow-up-right-from-square"></i>';
    }
    // Update LinkedIn URLs in contact section
    if (blocks['contact_linkedin']) {
      var liEls = document.querySelectorAll('a[href*="linkedin.com"]');
      liEls.forEach(function (a) {
        if (a.closest('.contact')) a.href = blocks['contact_linkedin'];
      });
    }

    // Set hero and missis images from JSON gallery arrays (random pick)
    function startSlideshow(key, sel1, sel2, fallbackImages) {
      var paths = [];
      if (blocks[key]) {
        try {
          var parsed = JSON.parse(blocks[key]);
          if (Array.isArray(parsed) && parsed.length) paths = parsed;
        } catch(e) {}
      }
      if (!paths.length && fallbackImages && fallbackImages.length) {
        paths = fallbackImages;
      }
      if (!paths.length) return;
      try {
        var container1 = document.querySelector(sel1);
        var container2 = document.querySelector(sel2);
        var img1 = container1 ? container1.querySelector('img') : null;
        var img2 = container2 ? container2.querySelector('img') : null;
        if (!img1 && !img2) return;

        var deck = [];
        function refillDeck() {
          var shuffled = paths.slice();
          for (var i = shuffled.length - 1; i > 0; i--) {
            var j = Math.floor(Math.random() * (i + 1));
            var tmp = shuffled[i]; shuffled[i] = shuffled[j]; shuffled[j] = tmp;
          }
          deck = shuffled;
        }

        function updateImages() {
          if (deck.length < 2) refillDeck();
          var p1 = deck.shift();
          var p2 = deck.shift();
          if (img1) {
            if (p1) {
              img1.style.opacity = '0';
              setTimeout(function() { img1.src = p1; img1.style.opacity = '1'; }, 500);
            } else {
              if (container1) container1.style.display = 'none';
            }
          }
          if (img2) {
            if (p2) {
              if (container2) container2.style.display = 'block';
              img2.style.opacity = '0';
              setTimeout(function() { img2.src = p2; img2.style.opacity = '1'; }, 500);
            } else {
              if (container2) container2.style.display = 'none';
            }
          }
        }

        refillDeck();
        if (img1 && deck.length > 0) {
          var p1 = deck.shift();
          img1.src = p1;
          img1.style.opacity = '1';
        }
        if (img2 && deck.length > 0) {
          var p2 = deck.shift();
          img2.src = p2;
          img2.style.opacity = '1';
        } else if (img2) {
          if (container2) container2.style.display = 'none';
        }

        setInterval(updateImages, 4000);
      } catch(e) { console.warn('Slideshow error for ' + key + ':', e); }
    }
    startSlideshow('hero_images', '.hero-img-1', '.hero-img-2', fallbackImages);
    startSlideshow('missis_images', '.missis-img-1', '.missis-img-2', fallbackImages);
  }

  function renderServices(items) {
    var grid = document.querySelector('.services-grid');
    if (!grid || !items || !items.length) return;
    grid.innerHTML = '';
    items.forEach(function (item, i) {
      var delay = i % 3 === 0 ? '' : ' reveal-delay-' + (i % 3);
      var swirl = i % 3 === 1 ? ' service-card-swirl' : '';
      var card = document.createElement('div');
      card.className = 'service-card' + swirl + ' reveal' + delay;
      card.innerHTML = '<h3>' + escapeHtml(item.title) + '</h3><p>' + escapeHtml(item.description) + '</p>';
      grid.appendChild(card);
    });
  }

  function renderTestimonials(items) {
    var grid = document.querySelector('.test-grid');
    if (!grid || !items || !items.length) return;
    grid.innerHTML = '';
    items.forEach(function (item, i) {
      var delay = i === 0 ? '' : i === 1 ? ' reveal-delay-1' : ' reveal-delay-2';
      var card = document.createElement('div');
      card.className = 'test-card reveal' + delay;
      card.innerHTML = '<p>' + escapeHtml(item.text) + '</p><div class="test-author"><div><div class="test-author-name">' + escapeHtml(item.author_name) + '</div><div class="test-author-role">' + escapeHtml(item.author_role) + '</div></div></div>';
      grid.appendChild(card);
    });
  }

  function renderExperiences(items) {
    var grid = document.querySelector('.exp-grid');
    if (!grid || !items || !items.length) return;
    grid.innerHTML = '';
    items.forEach(function (item, i) {
      var delay = i % 2 === 0 ? '' : ' reveal-delay-1';
      var card = document.createElement('div');
      card.className = 'exp-card reveal' + delay;
      card.innerHTML = '<div class="exp-icon"><i class="' + escapeHtml(item.icon) + '"></i></div><div><h3>' + escapeHtml(item.title) + '</h3><p>' + escapeHtml(item.description) + '</p></div>';
      grid.appendChild(card);
    });
  }

  function observeReveals() {
    var obs = window.__revealObserver;
    if (!obs) return;
    document.querySelectorAll('.reveal:not(.visible)').forEach(function (el) { obs.observe(el); });
  }

  function updateStats() {
    // Re-trigger intersection observers for stat counters
    var counters = document.querySelectorAll('.stat-number[data-cms]');
    if (counters.length) {
      // Dispatch scroll to potentially re-trigger
      setTimeout(function () { window.dispatchEvent(new Event('scroll')); }, 50);
    }
  }

  dataPromise.then(function (results) {
    var blocks = results[0];
    var services = results[1];
    var testimonials = results[2];
    var experiences = results[3];
    var globalImages = results[4];
    var fallbackImages = results[5];

    // Merge global images into blocks so they're available for the slideshow
    if (globalImages) {
      Object.assign(blocks, globalImages);
    }

    domReady(function () {
      if (blocks && Object.keys(blocks).length) applyBlocks(blocks, fallbackImages);
      if (services && services.length) { renderServices(services); observeReveals(); }
      if (testimonials && testimonials.length) { renderTestimonials(testimonials); observeReveals(); }
      if (experiences && experiences.length) { renderExperiences(experiences); observeReveals(); }
      if (blocks) updateStats();
    });
  }).catch(function (e) { console.warn('CMS load failed:', e); });
})();
