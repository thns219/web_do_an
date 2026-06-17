// header.js - interactive behaviors

document.addEventListener('DOMContentLoaded', function(){

  // Mobile menu open/close
  const mobileToggle = document.getElementById('mobileToggle');
  const mobileClose = document.getElementById('mobileClose');
  const mobileMenu  = document.getElementById('mobileMenu');

  if (mobileToggle && mobileMenu) {
    mobileToggle.addEventListener('click', () => {
      mobileMenu.classList.add('open');
      mobileMenu.setAttribute('aria-hidden','false');
    });
  }
  if (mobileClose && mobileMenu) {
    mobileClose.addEventListener('click', () => {
      mobileMenu.classList.remove('open');
      mobileMenu.setAttribute('aria-hidden','true');
    });
  }

  // Account dropdown
  const accountWrap = document.getElementById('accountWrap');
  const accountBtn  = document.getElementById('accountBtn');
  if (accountBtn && accountWrap) {
    accountBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      accountWrap.classList.toggle('open');
      accountBtn.setAttribute('aria-expanded', accountWrap.classList.contains('open'));
    });
  }

  // Notifications dropdown
  const notifWrap = document.getElementById('notifWrap');
  const notifBtn  = document.getElementById('notifBtn');
  if (notifBtn && notifWrap) {
    notifBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      notifWrap.classList.toggle('open');
      notifBtn.setAttribute('aria-expanded', notifWrap.classList.contains('open'));
      // Optionally: mark notifications as read via fetch to server (not included)
    });
  }

  // Close dropdowns clicking outside
  document.addEventListener('click', () => {
    if (accountWrap) accountWrap.classList.remove('open');
    if (notifWrap) notifWrap.classList.remove('open');
  });

  // Search autocomplete (demo only: local suggestions)
  const suggestions = ["Phở bò", "Bún chả", "Cơm tấm", "Mì xào", "Gà rán", "Cafe sữa đá"];
  const input = document.getElementById('header-search');
  const sugBox = document.getElementById('search-suggestions');

  if (input && sugBox) {
    input.addEventListener('input', () => {
      const q = input.value.trim().toLowerCase();
      if (!q) { sugBox.classList.remove('show'); sugBox.innerHTML=''; return; }
      const matched = suggestions.filter(s => s.toLowerCase().includes(q)).slice(0,6);
      if (matched.length === 0) { sugBox.classList.remove('show'); sugBox.innerHTML=''; return; }
      sugBox.innerHTML = matched.map(m => `<li role="option" tabindex="0">${escapeHtml(m)}</li>`).join('');
      sugBox.classList.add('show');
    });

    // click suggestion
    sugBox.addEventListener('click', (e) => {
      if (e.target.tagName === 'LI') {
        input.value = e.target.textContent;
        sugBox.classList.remove('show');
        // auto-submit search if you like
        // document.getElementById('search-btn').click();
      }
    });

    // keyboard support
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') { sugBox.classList.remove('show'); }
    });
  }

  // Search submit: redirect to search page with keyword as GET param
  const searchBtn = document.getElementById('search-btn');
  if (input) {
    // submit on Enter
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        const q = input.value.trim();
        if (q) window.location.href = '/pages/search.php?keyword=' + encodeURIComponent(q);
      }
    });
  }
  if (searchBtn && input) {
    searchBtn.addEventListener('click', (e) => {
      const q = input.value.trim();
      if (!q) return;
      window.location.href = '/pages/search.php?keyword=' + encodeURIComponent(q);
    });
  }

  // Theme toggle (dark/light) saved in localStorage
  const themeToggle = document.getElementById('themeToggle');
  const html = document.documentElement;
  const THEME_KEY = 'site-theme';
  const saved = localStorage.getItem(THEME_KEY);
  if (saved === 'dark') html.classList.add('dark');

  if (themeToggle) {
    themeToggle.addEventListener('click', () => {
      html.classList.toggle('dark');
      localStorage.setItem(THEME_KEY, html.classList.contains('dark') ? 'dark' : 'light');
    });
  }

  // Language toggle (client-only demo)
  const langBtn = document.getElementById('langToggle');
  const LANG_KEY = 'site-lang';
  const curLang = localStorage.getItem(LANG_KEY) || 'vi';
  if (langBtn) {
    langBtn.textContent = curLang === 'vi' ? '🇻🇳' : '🇬🇧';
    langBtn.addEventListener('click', () => {
      const newLang = (localStorage.getItem(LANG_KEY) || 'vi') === 'vi' ? 'en' : 'vi';
      localStorage.setItem(LANG_KEY, newLang);
      langBtn.textContent = newLang === 'vi' ? '🇻🇳' : '🇬🇧';
      // you can add code to reload translations from server
    });
  }

  // escape HTML helper
  function escapeHtml(str){
    return str.replace(/[&<>"']/g, (m) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
  }

});
