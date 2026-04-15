/* ════════════════════════════════════════════════
   Drakkar Box — Main JavaScript
   ════════════════════════════════════════════════ */

// ── Schedule renderer ────────────────────────────
(function () {
  const PILLS = {
    WOD:     { label: 'WOD DK 1.0',         cls: 'pill-wod'   },
    Open:    { label: 'OPEN DK 1.0',         cls: 'pill-open1' },
    WOD2:    { label: 'WOD DK 2.0',          cls: 'pill-wod2'  },
    OpenDK2: { label: 'OPEN DK 2.0',         cls: 'pill-open2' },
    Lift:    { label: 'OPEN LIFT DK 2.0',    cls: 'pill-lift'  },
    Hybrid:  { label: 'DRAKKAR HYBRID 2.0',  cls: 'pill-hybrid'},
  };

  const SCHED = [
    { label: 'Lun · Mié · Jue', rows: [
      {t:'7:00',  n1:['WOD','Open'], n2:['OpenDK2','Lift']},
      {t:'8:30',  n1:['WOD','Open'], n2:['OpenDK2','Lift']},
      {t:'9:30',  n1:['WOD','Open'], n2:['OpenDK2','Lift']},
      {t:'10:30', n1:['WOD','Open'], n2:['OpenDK2','Lift']},
      {t:'11:30', n1:['WOD','Open'], n2:['OpenDK2','Lift']},
      {t:'12:30', n1:['Open'],       n2:['OpenDK2','Lift']},
      {t:'13:30', n1:['Open'],       n2:['OpenDK2','Lift']},
      {t:'14:30', n1:['WOD','Open'], n2:['OpenDK2','Lift']},
      {t:'16:00', n1:['WOD','Open'], n2:[]},
      {t:'16:30', n1:[],             n2:['OpenDK2','Lift']},
      {t:'17:00', n1:['WOD','Open'], n2:[]},
      {t:'17:30', n1:[],             n2:['WOD2','OpenDK2','Lift']},
      {t:'18:00', n1:['WOD','Open'], n2:[]},
      {t:'18:30', n1:[],             n2:['WOD2','OpenDK2','Lift']},
      {t:'19:00', n1:['WOD','Open'], n2:[]},
      {t:'19:30', n1:[],             n2:['WOD2','OpenDK2','Lift']},
      {t:'20:00', n1:['WOD','Open'], n2:[]},
      {t:'20:30', n1:[],             n2:['Hybrid','OpenDK2','Lift']},
      {t:'21:00', n1:['WOD','Open'], n2:[]},
    ]},
    { label: 'Martes', rows: [
      {t:'7:00',  n1:['WOD','Open'], n2:['OpenDK2','Lift']},
      {t:'8:30',  n1:['WOD','Open'], n2:['OpenDK2','Lift']},
      {t:'9:30',  n1:['WOD','Open'], n2:['OpenDK2','Lift']},
      {t:'10:30', n1:['WOD','Open'], n2:['OpenDK2','Lift']},
      {t:'11:30', n1:['WOD','Open'], n2:['OpenDK2','Lift']},
      {t:'12:30', n1:['Open'],       n2:['OpenDK2','Lift']},
      {t:'13:30', n1:['Open'],       n2:['OpenDK2','Lift']},
      {t:'14:30', n1:['WOD','Open'], n2:['OpenDK2','Lift']},
      {t:'16:00', n1:['WOD','Open'], n2:[]},
      {t:'16:30', n1:[],             n2:['OpenDK2','Lift']},
      {t:'17:00', n1:['WOD','Open'], n2:[]},
      {t:'17:30', n1:[],             n2:['WOD2','OpenDK2','Lift']},
      {t:'18:00', n1:['WOD','Open'], n2:[]},
      {t:'18:30', n1:[],             n2:['WOD2','OpenDK2','Lift']},
      {t:'19:00', n1:['WOD','Open'], n2:[]},
      {t:'19:30', n1:[],             n2:['WOD2','OpenDK2','Lift']},
      {t:'20:00', n1:['WOD','Open'], n2:[]},
      {t:'20:30', n1:[],             n2:['WOD2','OpenDK2','Lift']},
      {t:'21:00', n1:['WOD','Open'], n2:[]},
    ]},
    { label: 'Viernes', rows: [
      {t:'7:00',  n1:['WOD','Open'], n2:['OpenDK2','Lift']},
      {t:'8:30',  n1:['WOD','Open'], n2:['OpenDK2','Lift']},
      {t:'9:30',  n1:['WOD','Open'], n2:['OpenDK2','Lift']},
      {t:'10:30', n1:['WOD','Open'], n2:['OpenDK2','Lift']},
      {t:'11:30', n1:['WOD','Open'], n2:['OpenDK2','Lift']},
      {t:'12:30', n1:['Open'],       n2:['OpenDK2','Lift']},
      {t:'13:30', n1:['Open'],       n2:['OpenDK2','Lift']},
      {t:'14:30', n1:['WOD','Open'], n2:['OpenDK2','Lift']},
      {t:'16:00', n1:['WOD','Open'], n2:[]},
      {t:'16:30', n1:[],             n2:['OpenDK2','Lift']},
      {t:'17:00', n1:['WOD','Open'], n2:[]},
      {t:'17:30', n1:[],             n2:['OpenDK2','Lift']},
      {t:'18:00', n1:['WOD','Open'], n2:[]},
      {t:'18:30', n1:[],             n2:['OpenDK2','Lift']},
      {t:'19:00', n1:['WOD','Open'], n2:[]},
      {t:'19:30', n1:[],             n2:['OpenDK2','Lift']},
      {t:'20:00', n1:['WOD','Open'], n2:[]},
      {t:'20:30', n1:[],             n2:['OpenDK2','Lift']},
      {t:'21:00', n1:['WOD','Open'], n2:[]},
    ]},
    { label: 'Sábado', rows: [
      {t:'9:30',  n1:['WOD'], n2:['OpenDK2','Lift']},
      {t:'10:30', n1:['WOD'], n2:['OpenDK2','Lift']},
      {t:'11:30', n1:['WOD'], n2:['OpenDK2','Lift']},
    ]},
  ];

  function pill(k) {
    const p = PILLS[k];
    return `<span class="class-pill ${p.cls}">${p.label}</span>`;
  }

  function col(classes) {
    if (!classes.length) return `<div class="nave-empty">—</div>`;
    return `<div class="nave-col">${classes.map(pill).join('')}</div>`;
  }

  (function render() {
    const el = document.getElementById('schedule-container');
    if (!el) return;

    const tabs = SCHED.map((s, i) =>
      `<button class="day-tab${i === 0 ? ' active' : ''}" data-idx="${i}">${s.label}</button>`
    ).join('');

    const grids = SCHED.map((s, i) =>
      `<div class="schedule-grid${i === 0 ? ' active' : ''}" data-idx="${i}">
        <div class="sched-col-headers">
          <div class="sched-col-header"></div>
          <div class="sched-col-header">Nave 1.0</div>
          <div class="sched-col-header">Nave 2.0</div>
        </div>
        ${s.rows.map(r => `
          <div class="sched-row">
            <span class="sched-time">${r.t}</span>
            ${col(r.n1)}
            ${col(r.n2)}
          </div>`).join('')}
      </div>`
    ).join('');

    el.innerHTML = `<div class="day-tabs">${tabs}</div>${grids}`;

    el.addEventListener('click', e => {
      const tab = e.target.closest('.day-tab');
      if (!tab) return;
      el.querySelectorAll('.day-tab').forEach(t => t.classList.remove('active'));
      el.querySelectorAll('.schedule-grid').forEach(g => g.classList.remove('active'));
      tab.classList.add('active');
      el.querySelector(`.schedule-grid[data-idx="${tab.dataset.idx}"]`).classList.add('active');
    });
  })();
})();


// ── Reviews renderer ─────────────────────────────
const REVIEWS = [
  { name: 'Pako King',               text: 'El box que Huelva necesitaba! Ambientazo de 10, entrenadores inmejorables que se preocupan de que todos avancemos y nos superemos día a día, y material nuevo y en constante renovación. Recomendado al 100%.' },
  { name: 'k0l0mb1t0',               text: 'Decidí apuntarme en Enero y es lo mejor que he podido hacer, tanto Lolo como Ricardo son dos grandes entrenadores, están pendiente de todo. También cabe destacar el buen rollo que hay en las clases. FAMILIA DRAKKAR BOX!' },
  { name: 'Alejandro Puente',        text: 'Excelente BOX con magníficos entrenadores pendientes constantemente de ti, corrigiendo cada movimiento y haciendo que mejores diariamente.' },
  { name: 'Lau Núñez',               text: 'Entrenar en este box ha sido, sin duda, una de las mejores decisiones que he tomado. Estoy súper contenta de formar parte de esta gran familia que se ha creado entre todos los compañeros. El ambiente es increíble, siempre hay apoyo...' },
  { name: 'Paloma Prieto Molina',    text: 'Buen ambiente, entrenadores atentos y entrenos variados que enganchan. La instalación es amplia, está bien equipada y se entrena con comodidad. La vibra del box es muy buena: hay compañerismo, motivación y se entrena a gusto desde el primer día.' },
  { name: 'Patricia Pulido Galan',   text: 'Sin duda es el mejor sitio de Huelva donde entrenar. He probado muchísimos sitios, y es que no tiene ni punto de comparación, tanto instalaciones, material, entrenadores y ¡sobre todo el buen ambiente que hay!...' },
  { name: 'Alba Rico Damota',        text: 'Encantada con Drakkar Box, el ambiente es motivador, los entrenadores son profesionales y se toman el tiempo para corregir la técnica y adaptarse a cada nivel. Además, el grupo de compañeros es muy acogedor, se siente como una pequeña comunidad. Recomendable 100%.' },
  { name: 'Manuel Lopez',            text: 'No se os ocurra apuntaros. Vais a sufrir, a sudar, a mirar el reloj, y lo peor de todo es que encima os engancharéis y estaréis deseando volver al día siguiente.' },
  { name: 'Cesáreo Muñoz',           text: 'Cuando más necesitaba un empujón de seguridad para volver al deporte, descubrí este establecimiento y su gente, su equipo y su buen rollo me hicieron volver a creer en mi para enfrentarme a mis inseguridades y lo conseguí! Además, un box...' },
  { name: 'David Velasco',           text: 'La verdad que no estaba muy seguro de empezar en esto, pero bendito día que fui a probar y mira que acabé molido... me dolía partes del cuerpo que no sabía ni que existían...' },
  { name: 'Gema Hernandez',          text: 'Tengo 52 años y he encontrado un lugar donde trabajo cuerpo y mente. Drakar Box es genial!!! Llevo 6 meses y los cambios han sido espectaculares, gracias a los entrenadores y al CrossFit.' },
  { name: 'Daniel Duddey',           text: 'He estado en muchos sitios para entrenar, pero lo que se vive en Drakkar no se encuentra fácilmente. Aquí no solo sudas... aquí entras en una comunidad real...' },
  { name: 'Javier Peña Castro',      text: 'Gran box con buenas instalaciones y material de primera. Un 10 a los entrenadores. Valoro mucho que están muy pendientes. Explican técnica y hacen las correcciones y adaptaciones que sean necesarias para cada nivel. Un ambiente excelente en las clases.' },
  { name: 'Pablo Laiseca Martin',    text: 'Si aún sigues dudando si hacer CrossFit o no, DrakkarBox es la opción perfecta!! un ambiente inigualable, rodeado de grandes profesionales y buenas personas...' },
  { name: 'Laura del Valle Mesa',    text: 'Es un box súper chulo. Está muy bien equipado... Los entrenadores te acompañan para que aprendas, mejores y te motives cada día.' },
  { name: 'Juan javier Rueda olmo',  text: 'Magníficos profesional, clases divertidas pero muy intensas 💪. Y sobre todo la gran atención a los alumnos.' },
  { name: 'David Hernandez Rios',    text: 'Gran box, gran equipo, grandes personas. Entrenamientos únicos: diversión y sufrimiento asegurado.' },
  { name: 'elena gm',                text: 'Un gran centro de entrenamiento en Huelva donde el buen rollo y el aprendizaje están asegurados. Entreno con ellos desde que abrieron y no puedo estar más contenta.' },
  { name: 'Cristina Tejada Mora',    text: 'Sitio de 10! Buen ambiente, buenos materiales y lo mejor de todo buenos entrenadores. Te ayudan a mejorar y te motivan en cada entreno...' },
  { name: 'Sara Gonzalez Roldan',    text: 'He estado en muchos gym y en varios box pero nada como este... lo que lo hace único. Es casa!' },
  { name: 'Laura Lozano Borrero',    text: 'Si quieres aprender y disfrutar del crossfit, no dudes en apuntarte a Drakkar Box. Muy contenta...' },
  { name: 'Daniel Casado',           text: 'Si quieres hacer CrossFit, con los mejores entrenadores y un ambiente increíble, este es tu sitio en Huelva.' },
  { name: 'Anton',                   text: 'Un Box donde cada día es una nueva experiencia... con el plus de un ambiente fantástico.' },
  { name: 'Mercedes Martín Fenández',text: 'Mi experiencia con DRAKKAR BOX no puede ser más positiva... creo que lo que ha conseguido es quitarme años.' },
  { name: 'Ana Bayo',                text: 'Un box que acabas sintiéndolo como casa... encantada de ser parte de esta gran familia!!' },
  { name: 'Maria Martín Romero',     text: 'El mejor box sin duda, buenas instalaciones, coaches profesionales y atentos y un ambientazo!!!' },
  { name: 'Leonardo Camacho',        text: 'Buen ambiente, clases de grupos reducidos con monitorización continua. Sala ordenada y limpia.' },
  { name: 'Paco Saavedra',           text: 'Desde que estoy apuntado aquí la experiencia es única, los entrenadores son unas máquinas y el trato es inmejorable.' },
  { name: 'Amelia Pérez Bolaños',    text: 'Los entrenadores son geniales, el ambiente fantástico. El mejor sitio para entrenar.' },
  { name: 'José Miguel García Muñiz',text: 'El mejor box de Huelva. Los profesores unos cracks, material de sobra y las clases muy entretenidas.' },
  { name: 'NIETO HUELVA MOTOR',      text: 'Gran equipo y muy profesionales, un placer entrenar con ellos.' },
  { name: 'Jesus Perez Delgado',     text: 'El mejor sitio para desconectar y hacer deporte, con dos buenos entrenadores tanto dentro como fuera del entreno.' },
  { name: 'Gonzalo Camacho',         text: 'Ambiente de lujo y entrenadores atentos que transmiten entusiasmo y confianza.' },
  { name: 'Lucia Garcia',            text: 'Encantada de formar parte de esta familia. ¡Muy recomendable!' },
  { name: 'María Teresa Feria González', text: 'El mejor box de Andalucía y parte del mundo, con los mejores entrenadores!!' },
  { name: 'Miguel Garrido',          text: 'Familia.' },
];

(function renderReviews() {
  const grid = document.getElementById('reviews-grid');
  if (!grid) return;

  const star = '<svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>';
  const stars = star.repeat(5);

  const shuffled = [...REVIEWS].sort(() => Math.random() - 0.5).slice(0, 3);

  grid.innerHTML = shuffled.map((r, i) => `
    <div class="review-card fade-in" style="transition-delay:${i * 0.1}s">
      <div class="review-top">
        <div class="review-avatar">${r.name.charAt(0).toUpperCase()}</div>
        <div class="review-author">
          <span class="review-name">${r.name}</span>
          <span class="review-date">Reseña de Google</span>
        </div>
      </div>
      <div class="review-card-stars">${stars}</div>
      <p class="review-text">${r.text}</p>
    </div>
  `).join('');

  const dotsEl = document.getElementById('reviews-dots');
  const cards = grid.querySelectorAll('.review-card');
  let current = 0;
  let carouselInterval = null;
  let isDragging = false;
  let dragStartX = 0;
  const THRESHOLD = 60;
  let listenersAdded = false;

  function buildDots() {
    dotsEl.innerHTML = Array.from(cards).map(function (_, i) {
      return '<div class="reviews-dot' + (i === current ? ' active' : '') + '" data-i="' + i + '"></div>';
    }).join('');
    dotsEl.querySelectorAll('.reviews-dot').forEach(function (dot) {
      dot.addEventListener('click', function () {
        const idx = parseInt(this.dataset.i);
        if (idx === current) return;
        goTo(idx, idx > current ? 'next' : 'prev');
      });
    });
  }

  function updateDots() {
    dotsEl.querySelectorAll('.reviews-dot').forEach(function (d, i) {
      d.classList.toggle('active', i === current);
    });
  }

  function initCarousel() {
    if (window.innerWidth > 768) return;
    cards.forEach(function (c, i) {
      c.style.cssText = '';
      c.classList.remove('review-active');
      if (i === current) c.classList.add('review-active');
    });
    buildDots();
    addListeners();
  }

  function goTo(idx, dir) {
    if (idx === current) return;
    const outCard = cards[current];
    current = idx;
    const inCard = cards[current];
    const w = grid.offsetWidth;
    const tr = 'transform 0.35s ease';

    outCard.style.transition = tr;
    outCard.style.transform = dir === 'next' ? 'translateX(-' + w + 'px)' : 'translateX(' + w + 'px)';

    inCard.style.cssText = 'position:absolute;top:0;left:0;width:100%;display:flex;transition:none;transform:translateX(' + (dir === 'next' ? w : -w) + 'px)';
    void inCard.offsetWidth;
    inCard.style.transition = tr;
    inCard.style.transform = 'translateX(0)';

    setTimeout(function () {
      outCard.style.cssText = '';
      outCard.classList.remove('review-active');
      inCard.style.cssText = '';
      inCard.classList.add('review-active');
      updateDots();
      resetInterval();
    }, 360);
  }

  function addListeners() {
    if (listenersAdded) return;
    listenersAdded = true;

    grid.addEventListener('touchstart', function (e) {
      if (window.innerWidth > 768 || isDragging) return;
      isDragging = true;
      dragStartX = e.touches[0].clientX;
      const w = grid.offsetWidth;
      const ni = (current + 1) % cards.length;
      const pi = (current - 1 + cards.length) % cards.length;
      cards[ni].style.cssText = 'position:absolute;top:0;left:0;width:100%;display:flex;transition:none;transform:translateX(' + w + 'px)';
      cards[pi].style.cssText = 'position:absolute;top:0;left:0;width:100%;display:flex;transition:none;transform:translateX(-' + w + 'px)';
      cards[current].style.transition = 'none';
    }, { passive: true });

    grid.addEventListener('touchmove', function (e) {
      if (!isDragging || window.innerWidth > 768) return;
      const delta = e.touches[0].clientX - dragStartX;
      const w = grid.offsetWidth;
      const ni = (current + 1) % cards.length;
      const pi = (current - 1 + cards.length) % cards.length;
      cards[current].style.transform = 'translateX(' + delta + 'px)';
      cards[ni].style.transform = 'translateX(' + (w + delta) + 'px)';
      cards[pi].style.transform = 'translateX(' + (-w + delta) + 'px)';
    }, { passive: true });

    grid.addEventListener('touchend', function (e) {
      if (!isDragging || window.innerWidth > 768) return;
      isDragging = false;
      const delta = e.changedTouches[0].clientX - dragStartX;
      const w = grid.offsetWidth;
      const ni = (current + 1) % cards.length;
      const pi = (current - 1 + cards.length) % cards.length;
      const tr = 'transform 0.3s ease';

      if (delta < -THRESHOLD) {
        cards[current].style.transition = tr;
        cards[ni].style.transition = tr;
        cards[current].style.transform = 'translateX(-' + w + 'px)';
        cards[ni].style.transform = 'translateX(0)';
        setTimeout(function () {
          const oldCurrent = current;
          current = ni;
          cards[oldCurrent].style.cssText = '';
          cards[oldCurrent].classList.remove('review-active');
          cards[current].style.cssText = '';
          cards[current].classList.add('review-active');
          cards[pi].style.cssText = '';
          updateDots();
          resetInterval();
        }, 320);
      } else if (delta > THRESHOLD) {
        cards[current].style.transition = tr;
        cards[pi].style.transition = tr;
        cards[current].style.transform = 'translateX(' + w + 'px)';
        cards[pi].style.transform = 'translateX(0)';
        setTimeout(function () {
          const oldCurrent = current;
          current = pi;
          cards[oldCurrent].style.cssText = '';
          cards[oldCurrent].classList.remove('review-active');
          cards[current].style.cssText = '';
          cards[current].classList.add('review-active');
          cards[ni].style.cssText = '';
          updateDots();
          resetInterval();
        }, 320);
      } else {
        cards[current].style.transition = tr;
        cards[ni].style.transition = tr;
        cards[pi].style.transition = tr;
        cards[current].style.transform = 'translateX(0)';
        cards[ni].style.transform = 'translateX(' + w + 'px)';
        cards[pi].style.transform = 'translateX(-' + w + 'px)';
        setTimeout(function () {
          cards[current].style.transition = '';
          cards[current].style.transform = '';
          cards[ni].style.cssText = '';
          cards[pi].style.cssText = '';
        }, 320);
      }
    }, { passive: true });
  }

  function resetInterval() {
    clearInterval(carouselInterval);
    carouselInterval = setInterval(function () {
      goTo((current + 1) % cards.length, 'next');
    }, 15000);
  }

  initCarousel();
  if (window.innerWidth <= 768) resetInterval();
  window.addEventListener('resize', function () {
    clearInterval(carouselInterval);
    current = 0;
    initCarousel();
    if (window.innerWidth <= 768) resetInterval();
  });
})();


// ── Trainer video hover ──────────────────────────
document.querySelectorAll('.trainer-photo video').forEach(vid => {
  const slide = vid.closest('.trainer-slide');
  const photo = vid.closest('.trainer-photo');
  photo.style.position = 'relative';

  const canvas = document.createElement('canvas');
  canvas.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;display:none';
  photo.appendChild(canvas);
  const ctx = canvas.getContext('2d');

  let frames = [], capturing = false, reversing = false;
  let reverseIdx = -1, reverseRaf = null, lastFrameTs = 0;
  const FRAME_MS = 1000 / 30;
  const hasRVFC = !!vid.requestVideoFrameCallback;

  function drawBmp(bmp) {
    const cw = canvas.width, ch = canvas.height;
    const scale = Math.max(cw / bmp.width, ch / bmp.height);
    ctx.clearRect(0, 0, cw, ch);
    ctx.drawImage(bmp, (cw - bmp.width * scale) / 2, 0, bmp.width * scale, bmp.height * scale);
  }

  if (hasRVFC) {
    function captureLoop() {
      if (!capturing) return;
      createImageBitmap(vid).then(bmp => {
        frames.push(bmp);
        if (capturing) vid.requestVideoFrameCallback(captureLoop);
      });
    }
    vid.addEventListener('play',  () => { if (reversing) return; frames = []; capturing = true; vid.requestVideoFrameCallback(captureLoop); });
    vid.addEventListener('pause', () => { capturing = false; });
    vid.addEventListener('ended', () => { capturing = false; });
  }

  function startReverse() {
    reversing = true;
    reverseIdx = frames.length - 1;
    if (reverseIdx < 0) { reversing = false; return; }
    canvas.width  = photo.offsetWidth;
    canvas.height = photo.offsetHeight;
    canvas.style.display = 'block';
    lastFrameTs = 0;
    function step(ts) {
      if (!reversing) return;
      if (!lastFrameTs || ts - lastFrameTs >= FRAME_MS) {
        lastFrameTs = ts;
        drawBmp(frames[reverseIdx--]);
        if (reverseIdx < 0) {
          reversing = false;
          vid.currentTime = 0;
          vid.addEventListener('seeked', function onSeeked() {
            vid.removeEventListener('seeked', onSeeked);
            canvas.style.display = 'none';
            frames = [];
          }, { once: true });
          return;
        }
      }
      reverseRaf = requestAnimationFrame(step);
    }
    reverseRaf = requestAnimationFrame(step);
  }

  slide.addEventListener('mouseenter', () => {
    if (reverseRaf) { cancelAnimationFrame(reverseRaf); reverseRaf = null; }
    reversing = false;
    canvas.style.display = 'none';
    vid.play();
  });

  slide.addEventListener('mouseleave', () => {
    capturing = false;
    vid.pause();
    if (hasRVFC && frames.length > 0) startReverse();
    else vid.currentTime = 0;
  });
});


// ── Facilities coverflow carousel ────────────────
(function () {
  const carousel = document.getElementById('facilitiesCarousel');
  const dotsWrap = document.getElementById('facilitiesDots');
  if (!carousel) return;
  const photos = Array.from(carousel.querySelectorAll('.facility-photo'));
  const n = photos.length;
  let current = 0;

  photos.forEach((_, i) => {
    const d = document.createElement('span');
    if (i === 0) d.classList.add('fac-dot-active');
    d.addEventListener('click', () => goTo(i));
    dotsWrap.appendChild(d);
  });
  const dots = Array.from(dotsWrap.querySelectorAll('span'));

  function mod(x) { return ((x % n) + n) % n; }

  function update() {
    photos.forEach((p, i) => {
      const rel = mod(i - current);
      p.className = 'facility-photo ' + (
        rel === 0     ? 'fc-active'    :
        rel === 1     ? 'fc-next'      :
        rel === n - 1 ? 'fc-prev'      :
        rel === 2     ? 'fc-far-right' :
        rel === n - 2 ? 'fc-far-left'  : 'fc-hidden'
      );
    });
    dots.forEach((d, i) => d.classList.toggle('fac-dot-active', i === current));
  }

  function goTo(idx) { current = mod(idx); update(); }

  photos.forEach(p => p.addEventListener('click', () => {
    if (p.classList.contains('fc-prev')) goTo(current - 1);
    else if (p.classList.contains('fc-next')) goTo(current + 1);
  }));

  let tx = 0;
  carousel.addEventListener('touchstart', e => { tx = e.touches[0].clientX; }, { passive: true });
  carousel.addEventListener('touchend', e => {
    const dx = e.changedTouches[0].clientX - tx;
    if (Math.abs(dx) > 40) goTo(dx < 0 ? current + 1 : current - 1);
  }, { passive: true });

  update();
})();


// ── Signup cascade effect ─────────────────────────
(function () {
  const cta = document.getElementById('signup');
  if (!cta) return;
  let raf = null, pos = 0, vel = 0, targetVel = 0;
  const isTouch = window.matchMedia('(hover: none)').matches;
  function step() {
    vel += (targetVel - vel) * 0.05;
    pos += vel;
    cta.style.setProperty('--cascade-y', pos + 'px');
    if (targetVel === 0 && Math.abs(vel) < 0.01) { vel = 0; raf = null; return; }
    raf = requestAnimationFrame(step);
  }
  if (isTouch) {
    targetVel = 0.15;
    raf = requestAnimationFrame(step);
  } else {
    cta.addEventListener('mouseenter', () => {
      targetVel = 0.15;
      if (!raf) raf = requestAnimationFrame(step);
    });
    cta.addEventListener('mouseleave', () => { targetVel = 0; });
  }
})();


// ── Signup form shine border (mouse reactive) ─────
(function () {
  const box = document.querySelector('.signup-form-box');
  if (!box) return;
  let mouseX = 50, mouseY = 50;
  let curX = 50, curY = 50;
  let hovered = false;
  let t = 0;

  function lerp(a, b, f) { return a + (b - a) * f; }

  function tick() {
    if (hovered) {
      curX = lerp(curX, mouseX, 0.07);
      curY = lerp(curY, mouseY, 0.07);
    } else {
      t += 0.006;
      const autoX = 50 + Math.cos(t) * 50;
      const autoY = 50 + Math.sin(t * 0.65) * 50;
      curX = lerp(curX, autoX, 0.025);
      curY = lerp(curY, autoY, 0.025);
    }
    box.style.setProperty('--shine-x', curX.toFixed(2) + '%');
    box.style.setProperty('--shine-y', curY.toFixed(2) + '%');
    requestAnimationFrame(tick);
  }

  box.addEventListener('mouseenter', () => { hovered = true; });
  box.addEventListener('mousemove', e => {
    const r = box.getBoundingClientRect();
    mouseX = ((e.clientX - r.left) / r.width) * 100;
    mouseY = ((e.clientY - r.top) / r.height) * 100;
  });
  box.addEventListener('mouseleave', () => { hovered = false; });

  tick();
})();


// ── Mobile menu ───────────────────────────────────
const hamburger = document.getElementById('hamburger');
const mobileNav = document.getElementById('mobileNav');

function closeMenu() {
  if (!mobileNav.classList.contains('open')) return;
  mobileNav.classList.remove('open');
  mobileNav.classList.add('closing');
  hamburger.classList.remove('active');
  setTimeout(() => mobileNav.classList.remove('closing'), 200);
}

hamburger.addEventListener('click', () => {
  if (mobileNav.classList.contains('open')) {
    closeMenu();
  } else {
    mobileNav.classList.remove('closing');
    mobileNav.classList.add('open');
    hamburger.classList.add('active');
  }
});
document.querySelectorAll('.mnl').forEach(l => l.addEventListener('click', closeMenu));
document.addEventListener('click', e => {
  if (!mobileNav.contains(e.target) && !hamburger.contains(e.target)) closeMenu();
});
window.addEventListener('resize', () => {
  if (window.innerWidth > 960) closeMenu();
});


// ── Fade-in on scroll ─────────────────────────────
const observer = new IntersectionObserver((entries) => {
  entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
}, { threshold: 0.1, rootMargin: '0px 0px -30px 0px' });
document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));


// ── Smooth scroll ─────────────────────────────────
document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', function (e) {
    const t = document.querySelector(this.getAttribute('href'));
    if (t) { e.preventDefault(); t.scrollIntoView({ behavior: 'smooth' }); }
  });
});


// ── Form logic ────────────────────────────────────
(function () {
  // ⚠️ Reemplaza esta URL con la de tu Google Apps Script web app
  const SHEETS_WEBAPP_URL = 'https://script.google.com/macros/s/AKfycby9ycAsai_2Nc-cY3pNZyF9K2y5pPWEb1m0FPgUWs4QF6oIHOHlWwAUUG7MB0Gd_RGk/exec';

  const form        = document.getElementById('suForm');
  const submitBtn   = document.getElementById('suSubmitBtn');
  const notice      = document.getElementById('suNotice');
  const noticeText  = document.getElementById('suNoticeText');
  const successFin  = document.getElementById('suSuccessFinal');
  const sucTitle    = document.getElementById('suSuccessTitle');
  const sucBody     = document.getElementById('suSuccessBody');
  const progressBar = document.getElementById('suProgressBar');
  const progressPct = document.getElementById('suProgressPct');
  const step1       = document.getElementById('su-step-1');

  if (!form) return;

  const fields = [
    document.getElementById('su-nombre'),
    document.getElementById('su-apellidos'),
    document.getElementById('su-telefono'),
    document.getElementById('su-correo'),
    document.getElementById('su-tarifa'),
    document.getElementById('su-lesion'),
    document.getElementById('su-enfermedad'),
  ].filter(Boolean);

  function isFilled(el) { return el.value.trim() !== ''; }

  function updateProgress() {
    const filledCount = fields.filter(isFilled).length;
    const pct = Math.round((filledCount / fields.length) * 100);

    progressBar.style.width = pct + '%';
    if (progressPct) progressPct.textContent = pct + '%';

    fields.forEach(f => {
      if (isFilled(f)) f.classList.add('su-filled');
      else             f.classList.remove('su-filled');
    });

    const allFilled = filledCount === fields.length;

    submitBtn.disabled = !allFilled;
    if (allFilled) submitBtn.classList.add('su-ready');
    else           submitBtn.classList.remove('su-ready');

    if (allFilled) {
      const tarifaEl  = document.getElementById('su-tarifa');
      const tarifaStr = tarifaEl && tarifaEl.selectedIndex > 0
        ? '<strong>' + tarifaEl.options[tarifaEl.selectedIndex].text + '</strong>'
        : '<strong>tu tarifa</strong>';
      noticeText.innerHTML =
        '¡Todo listo! Pulsa el botón para registrar tus datos. Después, crea tu cuenta en ' +
        '<a href="https://drakkar.wodbuster.com/" target="_blank" rel="noopener noreferrer" style="color:var(--gold);text-decoration:underline">WodBuster</a>' +
        ' con este mismo correo y ven al box a abonar ' + tarifaStr + '.';
      notice.classList.add('show');
      step1.classList.add('done');
    } else {
      notice.classList.remove('show');
      step1.classList.remove('done');
    }
  }

  fields.forEach(f => {
    f.addEventListener('input', updateProgress);
    f.addEventListener('change', updateProgress);
  });

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (!fields.every(isFilled)) return;

    submitBtn.classList.add('su-sending');
    submitBtn.textContent = 'Enviando…';
    submitBtn.disabled = true;

    const nombre   = document.getElementById('su-nombre').value.trim();
    const tarifaEl = document.getElementById('su-tarifa');
    const tarifa   = tarifaEl.selectedIndex > 0
      ? tarifaEl.options[tarifaEl.selectedIndex].text : '';

    const payload = {
      nombre:     nombre,
      apellidos:  document.getElementById('su-apellidos').value.trim(),
      telefono:   document.getElementById('su-telefono').value.trim(),
      correo:     document.getElementById('su-correo').value.trim(),
      tarifa:     document.getElementById('su-tarifa').value,
      lesion:     document.getElementById('su-lesion').value.trim(),
      enfermedad: document.getElementById('su-enfermedad').value.trim(),
      fecha:      new Date().toLocaleDateString('es-ES'),
    };

    function showFinal() {
      form.style.display = 'none';
      notice.classList.remove('show');
      sucTitle.textContent = '¡Inscripción registrada, ' + nombre + '!';
      sucBody.innerHTML =
        'Paso 2: crea tu cuenta en <a href="https://wodbuster.com/account/login.aspx" target="_blank" rel="noopener noreferrer" style="color:var(--gold);text-decoration:underline">wodbuster.com</a> ' +
        'usando el correo que introduciste. Después acércate al box (<strong>C/ Gravera nº8, Huelva</strong>) ' +
        'y abona <strong>' + tarifa + '</strong> a uno de los entrenadores.';
      successFin.classList.add('show');
      step1.classList.add('done');
    }

    if (!SHEETS_WEBAPP_URL || SHEETS_WEBAPP_URL === 'REEMPLAZA_CON_TU_URL_DE_APPS_SCRIPT') {
      showFinal();
      return;
    }

    fetch(SHEETS_WEBAPP_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'text/plain' },
      body: JSON.stringify(payload),
    }).then(showFinal).catch(showFinal);
  });

  // Animar campos al hacer scroll sobre la sección
  const signupSection = document.getElementById('signup');
  if (signupSection) {
    const fieldAnims = signupSection.querySelectorAll('.su-field-anim');
    const suObs = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          fieldAnims.forEach(function (el) {
            const delay = parseFloat(el.style.transitionDelay || '0') * 1000;
            setTimeout(function () { el.classList.add('in'); }, 300 + delay);
          });
          suObs.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15 });
    suObs.observe(signupSection);
  }
})();


// ── Hero logo positioning ─────────────────────────
(function () {
  // Ajusta estos valores para alinear el logo con "DRAKKAR"
  const LOGO_X    = 0.50;
  const LOGO_Y    = 0.22;
  const LOGO_SIZE = 0.26;
  const LOGO_MIN  = 140;
  const LOGO_MAX  = 420;

  const LOGO_X_MOBILE    = 0.50;
  const LOGO_Y_MOBILE    = 0.25;
  const LOGO_SIZE_MOBILE = 0.45;

  const bg         = document.querySelector('.hero-bg-img');
  const hero       = document.getElementById('hero');
  const logoCenter = document.querySelector('.hero-logo-center');
  const logoImg    = document.querySelector('.hero-logo-main');

  function positionLogo() {
    const cW = hero.offsetWidth;
    const cH = hero.offsetHeight;
    const iW = bg.naturalWidth;
    const iH = bg.naturalHeight;
    if (!iW || !iH) return;

    const mobile = cW < 768;
    const lx   = mobile ? LOGO_X_MOBILE    : LOGO_X;
    const ly   = mobile ? LOGO_Y_MOBILE    : LOGO_Y;
    const lsz  = mobile ? LOGO_SIZE_MOBILE : LOGO_SIZE;

    const scale = Math.max(cW / iW, cH / iH);
    const rW = iW * scale;
    const rH = iH * scale;
    const oX = (cW - rW) / 2;
    const oY = 0;

    const x = oX + lx * rW;
    const y = oY + ly * rH;
    const size = Math.min(Math.max(lsz * rW, LOGO_MIN), LOGO_MAX);

    logoCenter.style.left   = x + 'px';
    logoCenter.style.top    = y + 'px';
    logoImg.style.width     = size + 'px';
    logoImg.style.height    = size + 'px';
  }

  if (bg.complete && bg.naturalWidth) positionLogo();
  else bg.addEventListener('load', positionLogo);
  window.addEventListener('resize', positionLogo);
})();


// ── Rune circle ───────────────────────────────────
(function () {
  const divider = document.querySelector('.rune-divider');
  const hero = document.getElementById('hero');

  const RUNES_ARR = ['ᚠ','ᚢ','ᚦ','ᚨ','ᚱ','ᚲ','ᚷ','ᚹ','ᚾ','ᛁ','ᛇ','ᛈ','ᛊ','ᛏ','ᛒ','ᛖ','ᛗ','ᛚ','ᛟ'];
  const n = RUNES_ARR.length;
  const radius = 40, cx = 48, cy = 48;

  const runeCircle = document.createElement('div');
  runeCircle.className = 'rune-circle';
  runeCircle.style.cursor = 'pointer';
  runeCircle.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  const inner = document.createElement('div');
  inner.className = 'rune-circle-inner';

  RUNES_ARR.forEach(function (rune, i) {
    const angle = (i / n) * 2 * Math.PI - Math.PI / 2;
    const x = cx + radius * Math.cos(angle);
    const y = cy + radius * Math.sin(angle);
    const span = document.createElement('span');
    span.textContent = rune;
    span.style.left = x + 'px';
    span.style.top = y + 'px';
    inner.appendChild(span);
  });

  runeCircle.appendChild(inner);

  const logoImg = document.createElement('img');
  logoImg.src = 'assets/img/logo.webp';
  logoImg.className = 'rune-circle-logo';
  runeCircle.appendChild(logoImg);

  document.body.appendChild(runeCircle);

  let rotation = 0;
  let lastScrollY = window.scrollY;

  function onScroll() {
    const heroBottom = hero.getBoundingClientRect().bottom;
    const inHero = heroBottom > 100;

    divider.style.opacity = inHero ? '1' : '0';
    runeCircle.style.opacity = inHero ? '0' : '1';
    runeCircle.style.pointerEvents = inHero ? 'none' : 'auto';

    if (!inHero) {
      const delta = window.scrollY - lastScrollY;
      rotation += delta * 0.1;
      inner.style.transform = 'rotate(' + rotation + 'deg)';
      inner.querySelectorAll('span').forEach(function (span) {
        span.style.transform = 'translate(-50%, -50%) rotate(' + (-rotation) + 'deg)';
      });
    }
    lastScrollY = window.scrollY;
  }

  divider.style.transition = 'opacity 0.5s ease';
  runeCircle.style.transition = 'opacity 0.6s ease';
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
})();


// ── Chatbot callout ───────────────────────────────
(function () {
  const callout  = document.getElementById('dk-chat-callout');
  const closeBtn = document.getElementById('dk-callout-close');
  if (!callout) return;
  let dismissed = false;

  setTimeout(function () {
    if (!dismissed) callout.classList.add('dk-callout-visible');
  }, 3000);

  callout.addEventListener('click', function (e) {
    if (e.target === closeBtn) return;
    callout.classList.remove('dk-callout-visible');
    const btn = document.getElementById('dk-chat-btn');
    if (btn) btn.click();
  });

  closeBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    dismissed = true;
    callout.classList.remove('dk-callout-visible');
  });

  const chatBtn = document.getElementById('dk-chat-btn');
  if (chatBtn) {
    chatBtn.addEventListener('click', function () {
      callout.classList.remove('dk-callout-visible');
    });
  }
})();


// ── Chatbot Drakkar Box ───────────────────────────
(function () {
  const WEBHOOK = 'https://agencia-n8n.oyvucf.easypanel.host/webhook/drakkar-chat';
  const TOKEN   = 'drakkar-chat-2025';
  let open = false;
  let thinking = false;
  let convId = sessionStorage.getItem('dk_cid') || ('conv_' + Date.now());
  let history = JSON.parse(sessionStorage.getItem('dk_hist') || '[]');
  sessionStorage.setItem('dk_cid', convId);

  setTimeout(function () {
    if (!open) {
      document.getElementById('dk-badge').style.display = 'flex';
    }
  }, 5000);

  window.dkToggle = function () {
    open = !open;
    const box = document.getElementById('dk-chat-box');
    if (open) { box.classList.add('dk-open'); } else { box.classList.remove('dk-open'); }
    document.getElementById('dk-badge').style.display = 'none';
    if (open && document.getElementById('dk-messages').children.length === 0) {
      dkAddMsg('bot', '¡Ey!, qué tal Drakkarian@!? 🐉👊 Soy **Drakkaria**, el asistente de **Drakkar Box**.\n¿En qué puedo ayudarte guerrero ⚔️?',
        ['Ver precios', 'Ver horarios', '¿Dónde estáis?', 'Primera clase gratis']);
    }
    if (open) setTimeout(function () { document.getElementById('dk-input').focus(); }, 100);
  };

  window.dkOpenFreeClass = function () {
    const box = document.getElementById('dk-chat-box');
    if (!box.classList.contains('dk-open')) {
      window.dkToggle();
      setTimeout(function () { window.dkSend('Primera clase gratis'); }, 400);
    } else {
      window.dkSend('Primera clase gratis');
    }
  };

  window.dkSend = async function (text) {
    if (thinking) return;
    const input = document.getElementById('dk-input');
    const msg = (text || input.value).trim();
    if (!msg) return;
    input.value = '';
    dkAddMsg('user', msg);
    dkShowTyping();
    thinking = true;

    try {
      const res = await fetch(WEBHOOK, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'x-chat-token': TOKEN },
        body: JSON.stringify({ message: msg, conversationId: convId, history: history, token: TOKEN }),
      });
      const data = await res.json();
      dkHideTyping();
      dkAddMsg('bot', data.reply, data.suggestions);
      history.push({ role: 'user', content: msg });
      history.push({ role: 'assistant', content: data.reply });
      if (history.length > 6) history = history.slice(-6);
      sessionStorage.setItem('dk_hist', JSON.stringify(history));
    } catch (e) {
      dkHideTyping();
      dkAddMsg('bot', 'Lo siento, ha ocurrido un error. Escríbenos por WhatsApp: **604 95 57 06**');
    }
    thinking = false;
  };

  function dkAddMsg(type, text, suggestions) {
    const msgs = document.getElementById('dk-messages');
    const div = document.createElement('div');
    div.className = 'dk-msg ' + type;
    const bubble = document.createElement('div');
    bubble.className = 'dk-bubble';
    bubble.innerHTML = text
      .replace(/\*\*(.*?)\*\*/g, '<b>$1</b>')
      .replace(/\n/g, '<br>');
    div.appendChild(bubble);
    if (suggestions && suggestions.length) {
      const s = document.createElement('div');
      s.className = 'dk-suggestions';
      suggestions.forEach(function (t) {
        const b = document.createElement('button');
        b.className = 'dk-sugg-btn';
        b.textContent = t;
        b.addEventListener('click', function () { window.dkSend(t); });
        s.appendChild(b);
      });
      div.appendChild(s);
    }
    msgs.appendChild(div);
    msgs.scrollTop = msgs.scrollHeight;
  }

  function dkShowTyping() {
    const msgs = document.getElementById('dk-messages');
    const d = document.createElement('div');
    d.id = 'dk-typing';
    d.className = 'dk-typing';
    d.innerHTML = '<span></span><span></span><span></span>';
    msgs.appendChild(d);
    msgs.scrollTop = msgs.scrollHeight;
  }

  function dkHideTyping() {
    const t = document.getElementById('dk-typing');
    if (t) t.remove();
  }

  // Resize desde cualquier lateral
  (function () {
    const box = document.getElementById('dk-chat-box');
    const MIN_W = 280, MIN_H = 320, MAX_W = 640;

    box.querySelectorAll('[data-dk-dir]').forEach(function (handle) {
      function startResize(clientX, clientY) {
        const dir    = handle.dataset.dkDir;
        const rect   = box.getBoundingClientRect();
        const startX = clientX, startY = clientY;
        const startW = rect.width,  startH = rect.height;
        const startL = rect.left,   startT = rect.top;
        const maxH   = window.innerHeight * 0.85;

        box.style.left   = startL + 'px';
        box.style.top    = startT + 'px';
        box.style.right  = 'auto';
        box.style.bottom = 'auto';
        box.style.transition = 'opacity 0.25s ease, visibility 0.25s';

        function applyResize(cx, cy) {
          const dx = cx - startX, dy = cy - startY;
          let newW = startW, newH = startH, newL = startL, newT = startT;

          if (dir.indexOf('e') !== -1) newW = Math.min(MAX_W, Math.max(MIN_W, startW + dx));
          if (dir.indexOf('s') !== -1) newH = Math.min(maxH,  Math.max(MIN_H, startH + dy));
          if (dir.indexOf('w') !== -1) {
            newW = Math.min(MAX_W, Math.max(MIN_W, startW - dx));
            newL = startL + (startW - newW);
          }
          if (dir.indexOf('n') !== -1) {
            newH = Math.min(maxH, Math.max(MIN_H, startH - dy));
            newT = startT + (startH - newH);
          }

          box.style.width  = newW + 'px';
          box.style.height = newH + 'px';
          box.style.left   = newL + 'px';
          box.style.top    = newT + 'px';
        }

        function endResize() {
          box.style.transition = 'opacity 0.25s ease, transform 0.25s ease, visibility 0.25s';
          document.removeEventListener('mousemove', onMouseMove);
          document.removeEventListener('mouseup',   onMouseUp);
          document.removeEventListener('touchmove', onTouchMove);
          document.removeEventListener('touchend',  onTouchEnd);
        }

        function onMouseMove(e) { applyResize(e.clientX, e.clientY); }
        function onMouseUp()    { endResize(); }
        function onTouchMove(e) { e.preventDefault(); const t = e.touches[0]; applyResize(t.clientX, t.clientY); }
        function onTouchEnd()   { endResize(); }

        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup',   onMouseUp);
        document.addEventListener('touchmove', onTouchMove, { passive: false });
        document.addEventListener('touchend',  onTouchEnd);
      }

      handle.addEventListener('mousedown', function (e) {
        e.preventDefault(); e.stopPropagation();
        startResize(e.clientX, e.clientY);
      });
      handle.addEventListener('touchstart', function (e) {
        e.preventDefault(); e.stopPropagation();
        startResize(e.touches[0].clientX, e.touches[0].clientY);
      }, { passive: false });
    });
  })();
})();


// ── Event listeners (reemplazando handlers inline) ─
document.querySelectorAll('[data-action="free-class"]').forEach(btn => {
  btn.addEventListener('click', window.dkOpenFreeClass);
});

const chatBtn = document.getElementById('dk-chat-btn');
if (chatBtn) chatBtn.addEventListener('click', window.dkToggle);

const chatClose = document.querySelector('.dk-close');
if (chatClose) chatClose.addEventListener('click', window.dkToggle);

const sendBtn = document.getElementById('dk-send');
if (sendBtn) sendBtn.addEventListener('click', () => window.dkSend());

const chatInput = document.getElementById('dk-input');
if (chatInput) {
  chatInput.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') window.dkSend();
  });
}
