/* ============================================================
   PACONI — Main Script
   Handles: AOS init, header scroll, mobile menu,
            active nav links, form validation, smooth scroll
   ============================================================ */

'use strict';

/* =====================
   AOS — Animate on Scroll
====================== */
AOS.init({
  duration: 720,
  once: true,
  easing: 'ease-out-cubic',
  offset: 50,
});

/* =====================
   Footer year
====================== */
const yearEl = document.getElementById('currentYear');
if (yearEl) yearEl.textContent = new Date().getFullYear();

/* =====================
   Header scroll state
====================== */
const header = document.getElementById('header');

function syncHeader() {
  header.classList.toggle('is-scrolled', window.scrollY > 48);
}

window.addEventListener('scroll', syncHeader, { passive: true });
syncHeader();

/* =====================
   Hero Ken-Burns trigger
====================== */
const hero = document.querySelector('.hero');
if (hero) setTimeout(() => hero.classList.add('is-loaded'), 80);

/* =====================
   Mobile menu
====================== */
const hamburger = document.getElementById('hamburger');
const nav       = document.getElementById('nav');

function setMenuOpen(open) {
  hamburger.classList.toggle('is-open', open);
  nav.classList.toggle('is-open', open);
  hamburger.setAttribute('aria-expanded', String(open));
  document.body.style.overflow = open ? 'hidden' : '';
}

hamburger.addEventListener('click', () => {
  setMenuOpen(!nav.classList.contains('is-open'));
});

nav.querySelectorAll('.nav__link').forEach(link => {
  link.addEventListener('click', () => setMenuOpen(false));
});

document.addEventListener('keydown', e => {
  if (e.key === 'Escape' && nav.classList.contains('is-open')) setMenuOpen(false);
});

/* =====================
   Active nav on scroll
   IDs: accueil, apropos, services, realisations, contact
====================== */
const sections = Array.from(document.querySelectorAll('section[id]'));
const navLinks  = Array.from(document.querySelectorAll('.nav__link'));

function syncActiveLink() {
  const scrollMid = window.scrollY + window.innerHeight / 3;

  let current = sections[0];
  sections.forEach(section => {
    if (scrollMid >= section.offsetTop) current = section;
  });

  navLinks.forEach(link => {
    const match = link.getAttribute('href') === `#${current.id}`;
    link.classList.toggle('is-active', match);
  });
}

window.addEventListener('scroll', syncActiveLink, { passive: true });
syncActiveLink();

/* =====================
   Smooth scroll (offset for fixed header)
====================== */
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', e => {
    const id = anchor.getAttribute('href');
    if (id === '#') return;
    const target = document.querySelector(id);
    if (!target) return;
    e.preventDefault();
    const headerH = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--header-h'), 10) || 80;
    const top = target.getBoundingClientRect().top + window.scrollY - headerH;
    window.scrollTo({ top, behavior: 'smooth' });
  });
});

/* =====================
   Contact form validation
====================== */
const form       = document.getElementById('contactForm');
const submitBtn  = document.getElementById('submitBtn');
const successMsg = document.getElementById('formSuccess');

if (form) {

  /* --- helpers --- */
  function markError(inputId, errorId, msg) {
    const input = document.getElementById(inputId);
    const span  = document.getElementById(errorId);
    if (input) input.classList.add('is-error');
    if (span)  span.textContent = msg;
  }

  function clearError(inputId, errorId) {
    const input = document.getElementById(inputId);
    const span  = document.getElementById(errorId);
    if (input) input.classList.remove('is-error');
    if (span)  span.textContent = '';
  }

  function isValidEmail(val) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
  }

  /* --- full form validation, returns true when all fields are ok --- */
  function validate() {
    let ok = true;

    clearError('name',    'nameError');
    clearError('email',   'emailError');
    clearError('message', 'messageError');

    const name    = document.getElementById('name').value.trim();
    const email   = document.getElementById('email').value.trim();
    const message = document.getElementById('message').value.trim();

    if (!name) {
      markError('name', 'nameError', 'Veuillez saisir votre nom.');
      ok = false;
    }

    if (!email) {
      markError('email', 'emailError', 'Veuillez saisir votre adresse e-mail.');
      ok = false;
    } else if (!isValidEmail(email)) {
      markError('email', 'emailError', "L'adresse e-mail n'est pas valide.");
      ok = false;
    }

    if (!message) {
      markError('message', 'messageError', 'Veuillez écrire un message.');
      ok = false;
    }

    return ok;
  }

  /* --- validate on blur for real-time feedback --- */
  ['name', 'email', 'message'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('blur', validate);
  });

  /* --- submit handler --- */
  form.addEventListener('submit', e => {
    e.preventDefault();
    if (!validate()) return;

    /* Simulate a network request — replace with fetch() to a real endpoint */
    submitBtn.textContent = 'Envoi en cours…';
    submitBtn.disabled    = true;

    setTimeout(() => {
      form.reset();

      successMsg.classList.add('is-visible');
      submitBtn.textContent = 'Envoyer';
      submitBtn.disabled    = false;

      /* Hide success message after 7 s */
      setTimeout(() => successMsg.classList.remove('is-visible'), 7000);
    }, 1200);
  });

}
