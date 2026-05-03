/* global window, document, localStorage, fetch */
(function () {
  /* ═══════════════════════════════════════════════════════════════════════
     AUTH & UTILITIES
     ═══════════════════════════════════════════════════════════════════════ */

  function getAuth() {
    const token = localStorage.getItem('lp_token');
    const userRaw = localStorage.getItem('lp_user');
    const user = userRaw ? safeJsonParse(userRaw) : null;
    return { token, user };
  }

  function safeJsonParse(s) {
    try {
      return JSON.parse(s);
    } catch {
      return null;
    }
  }

  function logout(redirectTo) {
    localStorage.removeItem('lp_token');
    localStorage.removeItem('lp_user');
    if (redirectTo) window.location.href = redirectTo;
    else window.location.reload();
  }

  function setYear(elementId) {
    const el = document.getElementById(elementId);
    if (el) el.textContent = new Date().getFullYear();
  }

  function escapeHtml(s) {
    return String(s || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function getApiBase() {
    if (window.location.protocol === 'file:') {
      return 'http://localhost/larong-pinoy-system/api';
    }

    // Auto-detect project prefix from current path, e.g. /larong-pinoy-system/frontend/pages/*
    const path = window.location.pathname || '';
    const marker = '/frontend/';
    const idx = path.indexOf(marker);
    if (idx > 0) {
      const projectPrefix = path.slice(0, idx);
      return `${projectPrefix}/api`;
    }

    // Fallback for root-hosted projects.
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
      return `${window.location.origin}/larong-pinoy-system/api`;
    }
    return '/api';
  }

  function apiUrl(path) {
    const p = String(path || '');
    if (/^https?:\/\//i.test(p)) return p;
    const normalizedPath = p.startsWith('/') ? p : `/${p}`;
    const base = getApiBase();
    // Avoid double /api only for root-relative base "/api".
    // If base includes a project prefix (e.g. "/larong-pinoy-system/api"),
    // we must keep that prefix.
    if ((base === '/api' || /\/api$/i.test(base.replace(/^https?:\/\/[^/]+/i, ''))) && normalizedPath.startsWith('/api/')) {
      if (base === '/api') return normalizedPath;
      return `${base}${normalizedPath.slice(4)}`;
    }
    return `${base}${normalizedPath}`;
  }

  async function apiFetch(url, options) {
    const { token } = getAuth();
    const headers = new Headers(options?.headers || {});
    if (!headers.has('Content-Type') && options?.body) headers.set('Content-Type', 'application/json');
    if (token && !headers.has('Authorization')) headers.set('Authorization', `Bearer ${token}`);
    const res = await fetch(url, { ...options, headers });
    return res;
  }

  function requireAuth(redirectTo) {
    const { token, user } = getAuth();
    if (!token || !user) {
      sessionStorage.setItem('lp_redirect_after_login', window.location.href);
      window.location.replace(redirectTo || 'login.html');
      return false;
    }
    return true;
  }

  /* ═══════════════════════════════════════════════════════════════════════
     NAVIGATION & AUTH LINKS
     ═══════════════════════════════════════════════════════════════════════ */

  function renderNav(navLinksId, opts) {
    const navLinks = document.getElementById(navLinksId);
    if (!navLinks) return;

    const { token, user } = getAuth();
    const username = user?.username || user?.first_name || 'User';

    // Clear existing content
    navLinks.innerHTML = '';

    if (token && user) {
      const pill = document.createElement('div');
      pill.className = 'nav-user';
      pill.innerHTML = `
        <span class="nav-user-name">👤 ${escapeHtml(username)}</span>
        <button class="nav-logout" data-lp-logout title="Logout">✕</button>
        <div class="profile-dropdown">
          <div class="profile-dropdown-header">
            <div class="profile-dropdown-name">${escapeHtml(username)}</div>
          </div>
          <a href="profile.html" class="profile-dropdown-item">
            👤 &nbsp; My Profile
          </a>
          <a href="my-comments.html" class="profile-dropdown-item">
            💬 &nbsp; My Comments
          </a>
          ${user.role === 'admin' ? `
            <a href="admin.html" class="profile-dropdown-item">
              ⚙️ &nbsp; Admin Panel
            </a>
          ` : ''}
          <div class="profile-dropdown-divider"></div>
          <div class="profile-dropdown-item" data-lp-logout>
            🚪 &nbsp; Logout
          </div>
        </div>
      `;
      navLinks.appendChild(pill);
      
      // Update logout handler for the dropdown item
      const logoutItem = pill.querySelector('[data-lp-logout]');
      if (logoutItem) {
        logoutItem.addEventListener('click', (e) => {
          e.stopPropagation();
          logout(opts?.logoutRedirect);
        });
      }
      
      // Optional CTA hide
      (opts?.hideWhenAuthedIds || []).forEach((id) => {
        const el = document.getElementById(id);
        if (el) el.style.display = 'none';
      });
    } else {
      const loginA = document.createElement('a');
      loginA.href = opts?.loginHref || 'login.html';
      loginA.textContent = 'Login';
      const regA = document.createElement('a');
      regA.href = opts?.registerHref || 'register.html';
      regA.textContent = 'Register';
      regA.className = 'nav-btn';
      navLinks.appendChild(loginA);
      navLinks.appendChild(regA);
    }

    // Remove old listener to avoid duplicates, then add new one
    const oldHandler = navLinks._lpLogoutHandler;
    if (oldHandler) navLinks.removeEventListener('click', oldHandler);
    
    const clickHandler = (e) => {
      const btn = e.target?.closest?.('[data-lp-logout]');
      if (btn) logout(opts?.logoutRedirect);
    };
    navLinks._lpLogoutHandler = clickHandler;
    navLinks.addEventListener('click', clickHandler);
  }

  /* ═══════════════════════════════════════════════════════════════════════
     BANDIRITAS (Decorative Bunting)
     ═══════════════════════════════════════════════════════════════════════ */

  function renderBandiritas(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    container.innerHTML = '<div class="bandiritas-inner" id="bandiritasInner"></div>';
    const inner = document.getElementById('bandiritasInner');
    if (!inner) return;
    
    const count = Math.ceil(window.innerWidth / 26) + 10;
    for (let i = 0; i < count; i++) {
      const t = document.createElement('div');
      t.className = 'bandirita';
      inner.appendChild(t);
    }
  }

  /* ═══════════════════════════════════════════════════════════════════════
     NAVBAR COMPONENT
     ═══════════════════════════════════════════════════════════════════════ */

  function renderNavbar(containerId, customLogoPath) {
    const logoPath = customLogoPath || '../assets/Untitled%20design%20(2).png';
    const navbarHTML = `
      <nav class="navbar">
        <a href="games.html" class="navbar-brand">
          <div class="navbar-logo">
            <img src="${logoPath}" alt="Larong Pinoy logo" style="width:100%;height:100%;object-fit:cover;" />
          </div>
          <div>
            <div class="navbar-title">Larong Pinoy</div>
            <div class="navbar-subtitle">Heritage Preservation</div>
          </div>
        </a>
        <div class="navbar-links" id="navLinks"></div>
      </nav>
    `;
    
    const container = document.getElementById(containerId);
    if (container) container.innerHTML = navbarHTML;
    return navbarHTML;
  }

  /* ═══════════════════════════════════════════════════════════════════════
     FOOTER COMPONENT
     ═══════════════════════════════════════════════════════════════════════ */

  function renderFooter(containerId, customText) {
    const footerText = customText || 'Larong Pinoy Preservation Information System';
    const footerHTML = `
      <footer class="site-footer">
        <div class="footer-bottom">
          &copy; <span id="yr"></span> &nbsp;✦&nbsp; ${footerText} &nbsp;✦&nbsp; Laro · Kultura · Pamana
        </div>
      </footer>
    `;
    
    const container = document.getElementById(containerId);
    if (container) container.innerHTML = footerHTML;
    setYear('yr');
    return footerHTML;
  }

  /* ═══════════════════════════════════════════════════════════════════════
     CORNER FOLDS COMPONENT
     ═══════════════════════════════════════════════════════════════════════ */

  function renderCornerFolds(containerId) {
    const foldsHTML = `
      <div class="corner-fold tl"></div>
      <div class="corner-fold tr"></div>
      <div class="corner-fold bl"></div>
      <div class="corner-fold br"></div>
    `;
    
    const container = document.getElementById(containerId);
    if (container) container.innerHTML = foldsHTML;
    return foldsHTML;
  }

  /* ═══════════════════════════════════════════════════════════════════════
     ONE-CLICK PAGE INITIALIZATION
     ═══════════════════════════════════════════════════════════════════════ */

  function initPage(ids, options) {
    const opts = options || {};
    
    if (ids.cornerFolds) renderCornerFolds(ids.cornerFolds);
    if (ids.navbar) renderNavbar(ids.navbar, opts.logoPath);
    if (ids.bandiritas) renderBandiritas(ids.bandiritas);
    if (ids.footer) renderFooter(ids.footer, opts.footerText);
    
    setYear('yr');
  }

  /* ═══════════════════════════════════════════════════════════════════════
     EXPOSE PUBLIC API
     ═══════════════════════════════════════════════════════════════════════ */

  window.LP = {
    // Auth & core
    getAuth,
    logout,
    requireAuth,
    apiFetch,
    getApiBase,
    apiUrl,
    
    // Utilities
    setYear,
    escapeHtml,
    
    // Navigation (auth-aware links inside navbar)
    renderNav,
    
    // Individual UI components
    renderBandiritas,
    renderNavbar,
    renderFooter,
    renderCornerFolds,
    
    // One-call page initialization
    initPage,
  };
})();