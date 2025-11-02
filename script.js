/* ===================== Утилиты ===================== */
const $  = (s, r=document) => r.querySelector(s);
const $$ = (s, r=document) => [...r.querySelectorAll(s)];

function toast(msg, type = "ok") {
  const t = document.createElement("div");
  t.className = `toast toast--${type}`;
  t.textContent = msg;
  document.body.appendChild(t);
  requestAnimationFrame(() => t.classList.add("show"));
  setTimeout(() => {
    t.classList.remove("show");
    t.addEventListener("transitionend", () => t.remove());
  }, 2200);
}

/* ===== Вспомогатель для вызова API (PHP) ===== */
async function api(path, data) {
  const res = await fetch(`/api/${path}`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data || {})
  });
  // если сервер отдал 500/404 — попытаемся прочитать json, но ловим ошибки
  let json;
  try { json = await res.json(); } catch { json = { ok:false, error: 'Сервер не отвечает' }; }
  return json;
}

/* ===================== Навигация/скролл ===================== */
$$('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    const id = a.getAttribute('href');
    if (id && id.length > 1) {
      const el = $(id);
      if (el) { e.preventDefault(); el.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
    }
  });
});

(function setActiveNav() {
  const path = (location.pathname.split('/').pop() || 'index.html').toLowerCase();
  $$('.nav a').forEach(link => {
    const href = (link.getAttribute('href') || '').toLowerCase();
    if (href === path) link.classList.add('active');
  });
})();

/* ===================== Аккаунт (PHP сессии) ===================== */
/* ВАЖНО: На страницах должен быть:
   - span#userBox     — сюда пишем "Привет, ..."
   - button#openAuth  — открыть модалку
   - button#logoutBtn — выход
   - dialog#authModal с формами #loginForm и #registerForm
*/

async function renderAuth() {
  const nameBox = $('#userBox');
  const btnIn   = $('#openAuth');
  const btnOut  = $('#logoutBtn');
  if (!nameBox || !btnIn || !btnOut) return;

  try {
    const r = await api('me.php', {});
    if (r.ok && r.user) {
      nameBox.textContent = `Привет, ${r.user.name}`;
      nameBox.style.display = 'inline-block';
      btnOut.style.display  = 'inline-block';
      btnIn.style.display   = 'none';
    } else {
      nameBox.textContent = '';
      nameBox.style.display = 'none';
      btnOut.style.display  = 'none';
      btnIn.style.display   = 'inline-block';
    }
  } catch {
    // если что-то упало — показываем как гость
    nameBox.textContent = '';
    nameBox.style.display = 'none';
    btnOut.style.display  = 'none';
    btnIn.style.display   = 'inline-block';
  }
}

document.addEventListener('click', async (e) => {
  if (e.target.matches('#openAuth'))  $('#authModal')?.showModal();
  if (e.target.matches('#closeAuth')) $('#authModal')?.close();

  if (e.target.matches('#logoutBtn')) {
    try { await api('logout.php', {}); } catch {}
    toast('Вы вышли из аккаунта');
    renderAuth();
  }
});

document.addEventListener('submit', async (e) => {
  /* ---- Вход ---- */
  if (e.target.matches('#loginForm')) {
    e.preventDefault();
    const email = $('#loginEmail')?.value.trim() || '';
    const pass  = $('#loginPass')?.value.trim()  || '';
    if (!email || !pass) return toast('Заполните email и пароль', 'warn');

    const r = await api('login.php', { email, pass });
    if (r.ok) {
      $('#authModal')?.close();
      toast('Вход выполнен');
      renderAuth();
    } else {
      toast(r.error || 'Ошибка входа', 'warn');
    }
  }

  /* ---- Регистрация ---- */
  if (e.target.matches('#registerForm')) {
    e.preventDefault();
    const name  = $('#regName')?.value.trim()  || '';
    const email = $('#regEmail')?.value.trim() || '';
    const pass  = $('#regPass')?.value.trim()  || '';
    if (!name || !email || !pass) return toast('Заполните все поля', 'warn');

    const r = await api('register.php', { name, email, pass });
    if (r.ok) {
      $('#authModal')?.close();
      toast('Регистрация выполнена');
      renderAuth();
    } else {
      toast(r.error || 'Ошибка регистрации', 'warn');
    }
  }

  /* ---- Заявка/покупка на странице модели ----
     Требуется форма:
       <form id="orderForm" data-model="Porsche 911"> ... </form>
       поля: #ordPhone (обязательно), #ordQty (число, по умолч. 1)
     Имя покупателя для БД не требуется — пользователь берётся из сессии.
  */
  if (e.target.matches('#orderForm')) {
    e.preventDefault();
    const form  = e.target;
    const model = form.dataset.model || 'Porsche';
    const phone = $('#ordPhone')?.value.trim() || '';
    const qty   = parseInt($('#ordQty')?.value, 10) || 1;

    if (!phone) return toast('Укажите телефон', 'warn');

    const r = await api('order.php', { model, qty, phone });
    if (r.ok) {
      form.reset();
      toast(`Заявка по ${model} отправлена`);
    } else {
      toast(r.error || 'Ошибка отправки', 'warn');
    }
  }
});

/* стартовое состояние */
renderAuth();

/* ===================== Карта Leaflet (если используешь) ===================== */
(function initMap(){
  const mapEl = document.getElementById('map');
  if (!mapEl || !window.L) return; // на contacts.html у тебя iframe Яндекса — этот блок просто пропустится
  const lat = 55.715, lon = 37.559;
  const map = L.map('map', { scrollWheelZoom:false }).setView([lat, lon], 13);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution:'&copy; OpenStreetMap'
  }).addTo(map);
  L.marker([lat, lon]).addTo(map).bindPopup('Porsche Центр').openPopup();
})();

/* ===================== Анимация карточек ===================== */
$$('.card').forEach(c => {
  c.addEventListener('mouseenter', () => c.classList.add('lift'));
  c.addEventListener('mouseleave', () => c.classList.remove('lift'));
});