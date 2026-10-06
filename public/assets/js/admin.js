/* Vitrin E-Ticaret — Yönetim paneli etkileşimleri */
(() => {
  'use strict';
  const $ = (s, c = document) => c.querySelector(s);
  const $$ = (s, c = document) => Array.from(c.querySelectorAll(s));

  // Mobil kenar çubuğu
  $$('[data-sidebar-open]').forEach((b) => b.addEventListener('click', () => document.body.classList.add('sidebar-open')));
  $$('[data-sidebar-close]').forEach((b) => b.addEventListener('click', () => document.body.classList.remove('sidebar-open')));

  // Toplu seçim
  const bulk = $('[data-bulk]');
  if (bulk) {
    const all = $('[data-check-all]', bulk);
    const items = $$('[data-check-item]', bulk);
    const bar = $('[data-bulkbar]', bulk);
    const sync = () => {
      const n = items.filter((i) => i.checked).length;
      $('[data-bulk-count]', bulk).textContent = n;
      bar.classList.toggle('is-visible', n > 0);
      all.checked = n > 0 && n === items.length;
      all.indeterminate = n > 0 && n < items.length;
    };
    all?.addEventListener('change', () => { items.forEach((i) => { i.checked = all.checked; }); sync(); });
    items.forEach((i) => i.addEventListener('change', sync));
  }

  // Görsel yükleme önizleme + sürükle bırak
  const input = $('[data-image-input]');
  const zone = $('[data-dropzone]');
  const preview = $('[data-image-preview]');
  if (input && zone && preview) {
    const render = () => {
      $$('.image-item.is-new', preview).forEach((n) => n.remove());
      Array.from(input.files).forEach((f) => {
        if (!f.type.startsWith('image/')) return;
        const div = document.createElement('div');
        div.className = 'image-item is-new';
        const img = document.createElement('img');
        img.src = URL.createObjectURL(f);
        div.appendChild(img);
        const tag = document.createElement('span');
        tag.className = 'cover-tag';
        tag.style.background = 'var(--ink)';
        tag.textContent = 'Yeni';
        div.appendChild(tag);
        preview.appendChild(div);
      });
    };
    input.addEventListener('change', render);
    ['dragenter', 'dragover'].forEach((ev) => zone.addEventListener(ev, (e) => { e.preventDefault(); zone.classList.add('is-over'); }));
    ['dragleave', 'drop'].forEach((ev) => zone.addEventListener(ev, (e) => { e.preventDefault(); zone.classList.remove('is-over'); }));
    zone.addEventListener('drop', (e) => {
      if (e.dataTransfer?.files?.length) {
        input.files = e.dataTransfer.files;
        render();
      }
    });
  }

  // Fiyat ipuçları (indirim oranı & kâr marjı)
  const parse = (v) => parseFloat(String(v || '').replace(/\./g, '').replace(',', '.')) || 0;
  const price = $('[data-price]');
  if (price) {
    const compare = $('[data-compare]');
    const cost = $('[data-cost]');
    const dHint = $('[data-discount-hint]');
    const mHint = $('[data-margin-hint]');
    const upd = () => {
      const p = parse(price.value);
      const c = parse(compare?.value);
      const k = parse(cost?.value);
      if (dHint) dHint.textContent = c > p && p > 0 ? `Müşteri %${Math.round(((c - p) / c) * 100)} indirim görecek.` : '';
      if (mHint) mHint.innerHTML = k > 0 && p > 0 ? `Kâr: <strong>${(p - k).toLocaleString('tr-TR', { minimumFractionDigits: 2 })} ₺</strong> · Marj: <strong>%${Math.round(((p - k) / p) * 100)}</strong>` : 'Müşteriler bu alanı görmez.';
    };
    [price, compare, cost].forEach((el) => el?.addEventListener('input', upd));
    upd();
  }

  // Kupon kodu üret
  $('[data-generate-code]')?.addEventListener('click', () => {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    let code = '';
    for (let i = 0; i < 8; i++) code += chars[Math.floor(Math.random() * chars.length)];
    $('[data-coupon-code]').value = code;
  });

  // Ayarlar sekmeleri
  const tabs = $('[data-admin-tabs]');
  if (tabs) {
    const show = (id) => {
      if (!document.getElementById(id)) return;
      $$('a', tabs).forEach((a) => a.classList.toggle('is-active', a.getAttribute('href') === `#${id}`));
      $$('.tab-pane').forEach((p) => p.classList.toggle('is-active', p.id === id));
      const ti = $('[data-tab-input]');
      if (ti) ti.value = id;
    };
    $$('a', tabs).forEach((a) => a.addEventListener('click', (e) => {
      e.preventDefault();
      const id = a.getAttribute('href').slice(1);
      show(id);
      history.replaceState(null, '', `#${id}`);
    }));
    if (location.hash) show(location.hash.slice(1));
    // Hatalı alan varsa ilgili sekmeyi aç
    const invalid = $('.is-invalid');
    if (invalid) show(invalid.closest('.tab-pane')?.id);
  }

  // Renk ön ayarları
  $$('[data-color-presets] [data-color]').forEach((b) => b.addEventListener('click', () => {
    const inp = $('[data-color-input]');
    inp.value = b.dataset.color;
    document.documentElement.style.setProperty('--primary', b.dataset.color);
  }));
  $('[data-color-input]')?.addEventListener('input', (e) => document.documentElement.style.setProperty('--primary', e.target.value));
})();
