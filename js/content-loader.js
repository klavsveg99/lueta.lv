(function () {
  // ── CONFIG ── Fill these with your Supabase project details ──
  var CONFIG = {
    supabaseUrl: 'https://nyrzjdotaxacvjomthll.supabase.co',
    supabaseKey: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im55cnpqZG90YXhhY3Zqb210aGxsIiwicm9sZSI6ImFub24iLCJpYXQiOjE3ODExMTE1MzAsImV4cCI6MjA5NjY4NzUzMH0.rxocr7Og-NbcucDWZ01l80F3ZSNc17sREoZdfLj1J3U',
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

  function fetchBlogs() {
    var q = encodeURIComponent('page') + '=eq.' + encodeURIComponent(page);
    return fetchJson(api + '/blogs?' + q + '&select=id,title,description,featured_image,published_at&order=' + encodeURIComponent('published_at.desc'));
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
    safe(fetchBlogs(), []),
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

        function shuffleArray(arr) {
          var a = arr.slice();
          for (var i = a.length - 1; i > 0; i--) {
            var j = Math.floor(Math.random() * (i + 1));
            var tmp = a[i]; a[i] = a[j]; a[j] = tmp;
          }
          return a;
        }

        var order = [];
        var pos = 0;
        function nextPair() {
          if (pos >= order.length - 1) {
            order = shuffleArray(paths);
            pos = 0;
          }
          var p1 = order[pos];
          var p2 = order[pos + 1] || order[0];
          pos += 2;
          return [p1, p2];
        }

        function fadeSwap(imgEl, newSrc, cb) {
          if (!imgEl) { if (cb) cb(); return; }
          if (imgEl.src.indexOf(newSrc) !== -1 && imgEl.style.opacity === '1') { if (cb) cb(); return; }
          imgEl.style.transition = 'none';
          imgEl.style.opacity = '0';
          requestAnimationFrame(function() {
            requestAnimationFrame(function() {
              imgEl.src = newSrc;
              imgEl.style.transition = 'opacity 1s ease-in-out';
              imgEl.style.opacity = '1';
              var onDone = function() {
                imgEl.removeEventListener('transitionend', onDone);
                if (cb) cb();
              };
              imgEl.addEventListener('transitionend', onDone);
              setTimeout(onDone, 1200);
            });
          });
        }

        var pending = paths.length;
        function onAllPreloaded() {
          var pair = nextPair();
          img1.style.transition = 'none';
          if (pair[0]) img1.src = pair[0];
          img1.style.opacity = '1';
          if (img2) {
            if (container2) container2.style.display = 'block';
            img2.style.transition = 'none';
            if (pair[1]) img2.src = pair[1];
            img2.style.opacity = '1';
          } else if (container2) {
            container2.style.display = 'none';
          }
          void img1.offsetWidth;
          if (img2) void img2.offsetWidth;

          setInterval(function () {
            var pair = nextPair();
            fadeSwap(img1, pair[0]);
            setTimeout(function() {
              if (img2) {
                if (container2) container2.style.display = 'block';
                fadeSwap(img2, pair[1]);
              }
            }, 1500);
          }, 4000);
        }

        paths.forEach(function(path) {
          var im = new Image();
          im.onload = im.onerror = function() {
            pending--;
            if (pending <= 0) onAllPreloaded();
          };
          im.src = path;
        });
        if (pending <= 0) onAllPreloaded();
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

  function renderBlogs(items) {
    if (!items || !items.length) {
      var navLink = document.getElementById('navBlogLink');
      if (navLink) navLink.style.display = 'none';
      return;
    }
    var navLink = document.getElementById('navBlogLink');
    if (navLink) navLink.style.display = '';
    var hero = document.querySelector('.hero');
    if (!hero) return;

    var section = document.createElement('section');
    section.className = 'blog-preview';
    section.id = 'blog-preview';
    section.style.padding = '80px 0';
    section.style.background = 'var(--bg)';

    var container = document.createElement('div');
    container.className = 'container';

    var header = document.createElement('div');
    header.className = 'section-header';
    header.style.textAlign = 'center';
    header.style.marginBottom = '48px';

    var tag = document.createElement('div');
    tag.className = 'section-tag reveal';
    tag.innerText = window.cmsBlocks && window.cmsBlocks.blog_tag ? window.cmsBlocks.blog_tag : (page === 'en' ? 'Latest' : 'Jaunumi');

    var title = document.createElement('h2');
    title.className = 'section-title reveal reveal-delay-1';
    title.innerText = window.cmsBlocks && window.cmsBlocks.blog_title ? window.cmsBlocks.blog_title : (page === 'en' ? 'Blog' : 'Jaunumi');

    var desc = document.createElement('p');
    desc.className = 'section-desc reveal reveal-delay-2';
    desc.innerText = window.cmsBlocks && window.cmsBlocks.blog_desc ? window.cmsBlocks.blog_desc : (page === 'en' ? 'Insights, stories and updates from my journey.' : 'Iedvesma, stāsti un atjauninājumi no manas ceļojuma.');
    desc.style.maxWidth = '600px';
    desc.style.margin = '12px auto 0';
    desc.style.color = 'var(--text-muted)';

    header.appendChild(tag);
    header.appendChild(title);
    header.appendChild(desc);

    var grid = document.createElement('div');
    grid.className = 'blog-grid';
    grid.style.display = 'grid';
    grid.style.gridTemplateColumns = 'repeat(3, 1fr)';
    grid.style.gap = '30px';

    items.slice(0, 3).forEach(function (item, i) {
      var delay = ' reveal-delay-' + (i + 1);
      var card = document.createElement('a');
      card.href = 'blog.php?id=' + item.id + '&lang=' + page;
      card.className = 'blog-card reveal' + delay;
      card.style.textDecoration = 'none';
      card.style.color = 'var(--text)';
      card.style.display = 'flex';
      card.style.flexDirection = 'column';
      card.style.borderRadius = 'var(--radius)';
      card.style.overflow = 'hidden';
      card.style.background = 'var(--bg2)';
      card.style.transition = 'transform 0.3s, box-shadow 0.3s';
      card.onmouseenter = function() { this.style.transform = 'translateY(-4px)'; this.style.boxShadow = '0 12px 32px rgba(0,0,0,0.12)'; };
      card.onmouseleave = function() { this.style.transform = ''; this.style.boxShadow = ''; };

      var img = document.createElement('img');
      img.src = '/' + (item.featured_image || 'media/placeholder.jpg');
      img.style.width = '100%';
      img.style.aspectRatio = '16/9';
      img.style.objectFit = 'cover';

      var content = document.createElement('div');
      content.style.padding = '20px';

      var date = document.createElement('div');
      date.style.fontSize = '13px';
      date.style.color = 'var(--text-muted)';
      date.style.marginBottom = '8px';
      date.innerText = (page === 'en' ? 'Published' : 'Publicēts') + ': ' + item.published_at;

      var h3 = document.createElement('h3');
      h3.innerText = item.title;
      h3.style.margin = '0 0 8px 0';
      h3.style.fontSize = '18px';
      h3.style.fontWeight = '600';

      var excerpt = document.createElement('p');
      var descText = item.description || '';
      excerpt.innerText = descText.length > 25 ? descText.substring(0, 25) + '...' : descText;
      excerpt.style.margin = '0';
      excerpt.style.color = 'var(--text-muted)';
      excerpt.style.fontSize = '14px';

      content.appendChild(date);
      content.appendChild(h3);
      content.appendChild(excerpt);
      card.appendChild(img);
      card.appendChild(content);
      grid.appendChild(card);
    });

    container.appendChild(header);
    container.appendChild(grid);
    section.appendChild(container);

    hero.after(section);
    observeReveals();
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
    var blogs = results[4];
    var globalImages = results[5];
    var fallbackImages = results[6];

    // Merge global images into blocks so they're available for the slideshow
    if (globalImages) {
      Object.assign(blocks, globalImages);
    }

    domReady(function () {
      window.cmsBlocks = blocks;
      if (blocks && Object.keys(blocks).length) applyBlocks(blocks, fallbackImages);
      if (services && services.length) { renderServices(services); observeReveals(); }
      if (testimonials && testimonials.length) { renderTestimonials(testimonials); observeReveals(); }
      if (experiences && experiences.length) { renderExperiences(experiences); observeReveals(); }
      if (blogs && blogs.length) { renderBlogs(blogs); }
      if (blocks) updateStats();
    });
  }).catch(function (e) { console.warn('CMS load failed:', e); });
})();
