/* ==========================================================================
   Vitrin E-Ticaret — Mağaza etkileşimleri (bağımlılıksız)
   ========================================================================== */
(() => {
  'use strict';

  const APP = window.APP || { base: '', csrf: '', flashes: [] };
  const $ = (sel, ctx = document) => ctx.querySelector(sel);
  const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));
  const url = (path) => `${APP.base}/${String(path).replace(/^\/+/, '')}`;

  const ICONS = {
    success: '<path d="M20 6 9 17l-5-5"/>',
    error: '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
    warning: '<path d="M12 9v4"/><path d="M12 17h.01"/>',
    info: '<path d="M12 16v-4"/><path d="M12 8h.01"/>',
  };
  const svg = (paths, size = 14) => `<svg width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">${paths}</svg>`;

  /* ---------- Toast ---------- */
  function toast(message, type = 'success', html = false) {
    const wrap = $('[data-toasts]');
    if (!wrap || !message) return;
    const el = document.createElement('div');
    el.className = `toast toast-${type}`;
    el.setAttribute('role', type === 'error' ? 'alert' : 'status');
    const text = document.createElement('div');
    if (html) text.innerHTML = message; else text.textContent = message;
    el.innerHTML = `<span class="toast-icon">${svg(ICONS[type] || ICONS.info)}</span>`;
    el.appendChild(text);
    const close = document.createElement('button');
    close.className = 'toast-close';
    close.setAttribute('aria-label', 'Kapat');
    close.innerHTML = svg(ICONS.error, 16);
    close.addEventListener('click', () => dismiss());
    el.appendChild(close);
    wrap.appendChild(el);
    const dismiss = () => {
      el.classList.add('is-leaving');
      setTimeout(() => el.remove(), 300);
    };
    setTimeout(dismiss, type === 'error' ? 6000 : 4200);
  }
  window.toast = toast;
  (APP.flashes || []).forEach((f) => toast(f.message, f.type));

  /* ---------- İstek yardımcı fonksiyonu ---------- */
  async function post(path, data = {}) {
    const body = data instanceof FormData ? data : new URLSearchParams(data);
    if (!(data instanceof FormData)) body.append('_token', APP.csrf);
    const res = await fetch(path.startsWith('http') || path.startsWith('/') ? path : url(path), {
      method: 'POST',
      body,
      headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json', 'X-CSRF-Token': APP.csrf },
      credentials: 'same-origin',
    });
    let json = {};
    try { json = await res.json(); } catch (e) { json = { ok: false, message: 'Beklenmeyen bir hata oluştu.' }; }
    json.status = res.status;
    return json;
  }

  function updateCart(json) {
    if (typeof json.count === 'number') {
      $$('[data-cart-count]').forEach((el) => {
        el.textContent = json.count || '';
        el.dataset.count = json.count;
      });
    }
    if (json.mini) {
      const mini = $('[data-mini-cart]');
      if (mini) mini.innerHTML = json.mini;
    }
  }

  /* ---------- Sepete ekle ---------- */
  async function addToCart(id, qty, btn) {
    if (btn) btn.classList.add('is-loading');
    try {
      const json = await post('sepet/ekle', { product_id: id, qty });
      updateCart(json);
      if (json.status === 422 || json.ok === false) {
        toast(json.message || 'Ürün sepete eklenemedi.', 'error');
      } else {
        const warn = json.message && json.message !== 'Ürün sepetinize eklendi.';
        toast(warn ? json.message : `Ürün sepetinize eklendi. <a href="${url('sepet')}">Sepete git →</a>`, warn ? 'warning' : 'success', !warn);
      }
    } catch (e) {
      toast('Bağlantı hatası. Lütfen tekrar deneyin.', 'error');
    } finally {
      if (btn) btn.classList.remove('is-loading');
    }
  }

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-add-to-cart]');
    if (!btn || btn.disabled) return;
    e.preventDefault();
    addToCart(btn.dataset.addToCart, 1, btn);
  });

  $$('[data-add-form]').forEach((form) => {
    form.addEventListener('submit', (e) => {
      if (e.submitter && e.submitter.name === 'buy_now') return; // normal gönderim
      e.preventDefault();
      const btn = e.submitter || $('button[type=submit]', form);
      addToCart(form.product_id.value, form.qty.value, btn);
    });
  });

  /* ---------- Favoriler ---------- */
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-wishlist]');
    if (!btn) return;
    e.preventDefault();
    btn.disabled = true;
    try {
      const json = await post('favori', { product_id: btn.dataset.wishlist });
      if (json.status === 401) {
        toast(`${json.message} <a href="${json.login}">Giriş yap →</a>`, 'info', true);
        return;
      }
      $$(`[data-wishlist="${btn.dataset.wishlist}"]`).forEach((b) => b.classList.toggle('is-active', !!json.active));
      toast(json.message, 'success');
      if (!json.active && location.pathname.endsWith('/hesabim/favoriler')) {
        btn.closest('.product-card')?.remove();
      }
    } finally {
      btn.disabled = false;
    }
  });

  /* ---------- Adet seçici ---------- */
  function bindQty(wrap, onChange) {
    const input = $('input', wrap);
    const clamp = (v) => {
      const min = parseInt(input.min || '1', 10);
      const max = parseInt(input.max || '99', 10) || 99;
      return Math.max(min, Math.min(max, v || min));
    };
    const set = (v) => {
      const val = clamp(v);
      if (String(val) !== input.value) {
        input.value = val;
        onChange && onChange(val);
      } else if (v > val) {
        toast(`Stokta en fazla ${val} adet bulunuyor.`, 'warning');
      }
    };
    $('[data-qty-minus]', wrap)?.addEventListener('click', () => set(parseInt(input.value, 10) - 1));
    $('[data-qty-plus]', wrap)?.addEventListener('click', () => set(parseInt(input.value, 10) + 1));
    input.addEventListener('change', () => { input.value = clamp(parseInt(input.value, 10)); onChange && onChange(parseInt(input.value, 10)); });
  }
  $$('[data-qty]').forEach((w) => bindQty(w));

  /* ---------- Sepet sayfası ---------- */
  function bindCart() {
    const content = $('[data-cart-content]');
    if (!content) return;
    const refresh = (json) => {
      updateCart(json);
      if (json.html) {
        content.innerHTML = json.html;
        bindCart();
      }
      if (json.message) toast(json.message, 'warning');
    };
    $$('[data-cart-qty]', content).forEach((wrap) => {
      let timer;
      bindQty(wrap, (qty) => {
        clearTimeout(timer);
        timer = setTimeout(async () => {
          $('[data-cart-items]')?.classList.add('is-busy');
          refresh(await post('sepet/guncelle', { product_id: wrap.dataset.cartQty, qty }));
        }, 350);
      });
    });
    $$('[data-cart-remove]', content).forEach((btn) => {
      btn.addEventListener('click', async () => {
        $('[data-cart-items]')?.classList.add('is-busy');
        const json = await post('sepet/sil', { product_id: btn.dataset.cartRemove });
        refresh(json);
        toast('Ürün sepetinizden çıkarıldı.', 'success');
      });
    });
  }
  bindCart();

  /* ---------- Canlı arama ---------- */
  $$('[data-search]').forEach((wrap) => {
    const input = $('input[name=q]', wrap);
    const box = $('[data-search-results]', wrap);
    let timer;
    let controller;
    let active = -1;
    const close = () => { box.classList.remove('is-open'); active = -1; };
    const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    input.addEventListener('input', () => {
      clearTimeout(timer);
      const q = input.value.trim();
      if (q.length < 2) return close();
      timer = setTimeout(async () => {
        controller?.abort();
        controller = new AbortController();
        try {
          const res = await fetch(`${url('api/arama')}?q=${encodeURIComponent(q)}`, { signal: controller.signal, headers: { Accept: 'application/json' } });
          const json = await res.json();
          if (!json.items.length) {
            box.innerHTML = `<div class="suggest-empty">“${esc(q)}” için sonuç bulunamadı.</div>`;
          } else {
            box.innerHTML = json.items.map((i) => `
              <a class="suggest-item" href="${esc(i.url)}">
                <img src="${esc(i.image)}" alt="">
                <div><div class="name">${esc(i.name)}</div><div class="meta">${esc(i.category || '')}</div></div>
                <span class="price">${esc(i.price)}</span>
              </a>`).join('') + `<a class="suggest-all" href="${url('ara')}?q=${encodeURIComponent(q)}">Tüm sonuçları gör →</a>`;
          }
          active = -1;
          box.classList.add('is-open');
        } catch (e) { /* iptal edildi */ }
      }, 220);
    });
    input.addEventListener('keydown', (e) => {
      const items = $$('.suggest-item', box);
      if (!box.classList.contains('is-open') || !items.length) return;
      if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
        e.preventDefault();
        active = (active + (e.key === 'ArrowDown' ? 1 : -1) + items.length) % items.length;
        items.forEach((it, i) => it.classList.toggle('is-active', i === active));
      } else if (e.key === 'Enter' && active >= 0) {
        e.preventDefault();
        location.href = items[active].href;
      } else if (e.key === 'Escape') {
        close();
      }
    });
    document.addEventListener('click', (e) => { if (!wrap.contains(e.target)) close(); });
  });

  /* ---------- Slider ---------- */
  $$('[data-slider]').forEach((slider) => {
    const slides = $$('.slide', slider);
    const dots = $$('[data-slide-to]', slider);
    if (slides.length < 2) return;
    let index = 0;
    let timer;
    const go = (i) => {
      index = (i + slides.length) % slides.length;
      slides.forEach((s, n) => s.classList.toggle('is-active', n === index));
      dots.forEach((d, n) => d.classList.toggle('is-active', n === index));
      slider.classList.toggle('is-light', slides[index].dataset.theme === 'light');
    };
    const play = () => { clearInterval(timer); timer = setInterval(() => go(index + 1), 6000); };
    dots.forEach((d) => d.addEventListener('click', () => { go(+d.dataset.slideTo); play(); }));
    $('[data-slide-next]', slider)?.addEventListener('click', () => { go(index + 1); play(); });
    $('[data-slide-prev]', slider)?.addEventListener('click', () => { go(index - 1); play(); });
    slider.addEventListener('mouseenter', () => clearInterval(timer));
    slider.addEventListener('mouseleave', play);
    let startX = null;
    slider.addEventListener('touchstart', (e) => { startX = e.touches[0].clientX; }, { passive: true });
    slider.addEventListener('touchend', (e) => {
      if (startX === null) return;
      const dx = e.changedTouches[0].clientX - startX;
      if (Math.abs(dx) > 40) { go(index + (dx < 0 ? 1 : -1)); play(); }
      startX = null;
    });
    play();
  });

  /* ---------- Geri sayım ---------- */
  $$('[data-countdown]').forEach((el) => {
    const end = new Date(el.dataset.countdown).getTime();
    const pad = (n) => String(n).padStart(2, '0');
    const tick = () => {
      const diff = Math.max(0, end - Date.now());
      $('[data-h]', el).textContent = pad(Math.floor(diff / 3.6e6));
      $('[data-m]', el).textContent = pad(Math.floor((diff % 3.6e6) / 6e4));
      $('[data-s]', el).textContent = pad(Math.floor((diff % 6e4) / 1000));
    };
    tick();
    setInterval(tick, 1000);
  });

  /* ---------- Kopyala ---------- */
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-copy]');
    if (!btn) return;
    e.preventDefault();
    try {
      await navigator.clipboard.writeText(btn.dataset.copy);
      toast('Panoya kopyalandı.', 'success');
    } catch (err) {
      toast(btn.dataset.copy, 'info');
    }
  });

  /* ---------- Mobil menü & filtre çekmecesi ---------- */
  const body = document.body;
  $$('[data-drawer-open]').forEach((b) => b.addEventListener('click', () => body.classList.add('drawer-open')));
  $$('[data-filters-open]').forEach((b) => b.addEventListener('click', () => body.classList.add('filters-open')));
  $$('[data-drawer-close], [data-filters-close]').forEach((b) => b.addEventListener('click', () => body.classList.remove('drawer-open', 'filters-open')));
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') body.classList.remove('drawer-open', 'filters-open'); });

  /* Dokunmatik cihazlarda açılır menüler */
  $$('.dropdown').forEach((dd) => {
    const trigger = dd.querySelector(':scope > a');
    trigger?.addEventListener('click', (e) => {
      if (window.matchMedia('(hover: none)').matches && !dd.classList.contains('is-open')) {
        e.preventDefault();
        $$('.dropdown.is-open').forEach((o) => o.classList.remove('is-open'));
        dd.classList.add('is-open');
      }
    });
  });
  document.addEventListener('click', (e) => {
    $$('.dropdown.is-open').forEach((dd) => { if (!dd.contains(e.target)) dd.classList.remove('is-open'); });
  });

  /* ---------- Sıralama ---------- */
  $$('[data-sort]').forEach((sel) => sel.addEventListener('change', () => { location.href = sel.value; }));

  /* ---------- Ürün galerisi ---------- */
  $$('[data-gallery]').forEach((g) => {
    const main = $('[data-main-image]', g);
    const thumbs = $$('.gallery-thumbs button', g);
    thumbs.forEach((t) => t.addEventListener('click', () => {
      thumbs.forEach((x) => x.classList.toggle('is-active', x === t));
      main.src = t.dataset.src;
    }));
    const zoom = $('[data-zoom]', g);
    if (zoom && window.matchMedia('(hover: hover)').matches) {
      zoom.addEventListener('mousemove', (e) => {
        const r = zoom.getBoundingClientRect();
        main.style.transformOrigin = `${((e.clientX - r.left) / r.width) * 100}% ${((e.clientY - r.top) / r.height) * 100}%`;
      });
      zoom.addEventListener('mouseenter', () => zoom.classList.add('is-zoom'));
      zoom.addEventListener('mouseleave', () => zoom.classList.remove('is-zoom'));
    }
  });

  /* ---------- Sekmeler ---------- */
  $$('[data-tabs]').forEach((tabs) => {
    const activate = (name) => {
      $$('[data-tab]', tabs).forEach((b) => b.classList.toggle('is-active', b.dataset.tab === name));
      $$('[data-panel]', tabs).forEach((p) => p.classList.toggle('is-active', p.dataset.panel === name));
    };
    $$('[data-tab]', tabs).forEach((b) => b.addEventListener('click', () => activate(b.dataset.tab)));
    $$('[data-tab-link]').forEach((a) => a.addEventListener('click', () => activate(a.dataset.tabLink)));
    if (location.hash === '#yorumlar') activate('reviews');
  });

  /* ---------- Ödeme sayfası ---------- */
  const checkout = $('[data-checkout]');
  if (checkout) {
    const newAddress = $('[data-new-address]');
    $$('[data-address-option]', checkout).forEach((r) => r.addEventListener('change', () => {
      newAddress.classList.toggle('hidden', r.value !== 'new' || !r.checked);
    }));
    $$('[data-invoice-type]', checkout).forEach((r) => r.addEventListener('change', () => {
      $('[data-corporate]').classList.toggle('hidden', r.value !== 'corporate');
    }));

    const totalEl = $('[data-grand-total]', checkout);
    const codRow = $('[data-cod-row]', checkout);
    const fmt = (n) => new Intl.NumberFormat('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n) + ' ' + (window.CHECKOUT?.currency || '₺');
    const updateMethod = () => {
      const m = $('[data-pay-method]:checked', checkout)?.value;
      $$('[data-pay-panel]', checkout).forEach((p) => p.classList.toggle('hidden', p.dataset.payPanel !== m));
      const base = parseFloat(totalEl.dataset.base || '0');
      const fee = m === 'cod' ? (window.CHECKOUT?.codFee || 0) : 0;
      totalEl.textContent = fmt(base + fee);
      codRow?.classList.toggle('hidden', m !== 'cod');
      $$('[data-pay-panel="card"] input, [data-pay-panel="card"] select', checkout).forEach((i) => { i.disabled = m !== 'card'; });
    };
    $$('[data-pay-method]', checkout).forEach((r) => r.addEventListener('change', updateMethod));
    updateMethod();

    // Kart önizleme
    const number = $('[data-cc="number"]', checkout);
    const out = (k) => $(`[data-cc-out="${k}"]`, checkout);
    number?.addEventListener('input', () => {
      const digits = number.value.replace(/\D/g, '').slice(0, 19);
      number.value = digits.replace(/(.{4})/g, '$1 ').trim();
      const masked = (digits + '•'.repeat(Math.max(0, 16 - digits.length))).replace(/(.{4})/g, '$1 ').trim();
      out('number').textContent = masked;
      const brand = /^4/.test(digits) ? 'VISA' : /^(5[1-5]|2[2-7])/.test(digits) ? 'mastercard' : /^3[47]/.test(digits) ? 'AMEX' : /^9792/.test(digits) ? 'troy' : 'VISA';
      $('[data-cc-brand]', checkout).textContent = brand;
    });
    $('[data-cc="holder"]', checkout)?.addEventListener('input', (e) => {
      out('holder').textContent = e.target.value.toLocaleUpperCase('tr-TR') || 'AD SOYAD';
    });
    const exp = () => {
      const m = String($('[data-cc="month"]', checkout).value).padStart(2, '0');
      const y = String($('[data-cc="year"]', checkout).value).slice(-2);
      out('exp').textContent = `${m}/${y}`;
    };
    $('[data-cc="month"]', checkout)?.addEventListener('change', exp);
    $('[data-cc="year"]', checkout)?.addEventListener('change', exp);
    $$('input[name="card[cvc]"]', checkout).forEach((i) => i.addEventListener('input', () => { i.value = i.value.replace(/\D/g, '').slice(0, 4); }));

    checkout.addEventListener('submit', (e) => {
      const agree = $('input[name=agreement]', checkout);
      if (!agree.checked) {
        e.preventDefault();
        toast('Devam etmek için Mesafeli Satış Sözleşmesi\'ni onaylamanız gerekiyor.', 'warning');
        agree.focus();
        return;
      }
      $('[data-place-order]', checkout).classList.add('is-loading');
    });
  }

  /* ---------- Formlar ---------- */
  document.addEventListener('submit', (e) => {
    const form = e.target;
    if (form.matches('[data-confirm]') && !window.confirm(form.dataset.confirm)) {
      e.preventDefault();
      return;
    }
    if (form.matches('[data-submit-once]')) {
      const btn = e.submitter || $('button[type=submit]', form);
      btn?.classList.add('is-loading');
    }
  });

  $$('[data-ajax-form]').forEach((form) => {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const btn = $('button[type=submit]', form);
      btn?.classList.add('is-loading');
      try {
        const json = await post(form.action, new FormData(form));
        toast(json.message, json.ok ? 'success' : 'error');
        if (json.ok) form.reset();
      } finally {
        btn?.classList.remove('is-loading');
      }
    });
  });

  $$('[data-toggle-pass]').forEach((btn) => btn.addEventListener('click', () => {
    const input = btn.parentElement.querySelector('input');
    input.type = input.type === 'password' ? 'text' : 'password';
    btn.style.color = input.type === 'text' ? 'var(--primary)' : '';
  }));

  $$('[data-pw-meter]').forEach((input) => {
    const bar = input.closest('.form-group')?.querySelector('[data-pw-bar]');
    if (!bar) return;
    input.addEventListener('input', () => {
      const v = input.value;
      let score = 0;
      if (v.length >= 8) score++;
      if (/[a-zçğıöşü]/.test(v) && /[A-ZÇĞİÖŞÜ]/.test(v)) score++;
      if (/\d/.test(v)) score++;
      if (/[^A-Za-z0-9çğıöşüÇĞİÖŞÜ]/.test(v) || v.length >= 12) score++;
      bar.dataset.score = v ? Math.max(1, score) : 0;
    });
  });
})();
