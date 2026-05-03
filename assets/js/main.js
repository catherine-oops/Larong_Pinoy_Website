document.addEventListener('DOMContentLoaded', function () {
  if (window.LP && LP.initPage) {
    LP.initPage({ bandiritas: 'bandiritasContainer' });
  }

  const root = document.documentElement;
  const toggle = document.getElementById('themeToggle');
  const savedTheme = localStorage.getItem('lp_theme');

  if (savedTheme === 'dark') {
    root.classList.add('theme-dark');
  }

  if (toggle) {
    const refreshLabel = function () {
      toggle.textContent = root.classList.contains('theme-dark') ? 'Light Mode' : 'Dark Mode';
    };

    refreshLabel();
    toggle.addEventListener('click', function () {
      root.classList.toggle('theme-dark');
      localStorage.setItem('lp_theme', root.classList.contains('theme-dark') ? 'dark' : 'light');
      refreshLabel();
    });
  }
});
