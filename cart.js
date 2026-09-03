/* =====================================================================
   BIG2BIG — Panier & Compte client
   Ce fichier est autonome : à inclure sur TOUTES les pages
   (juste après script.js) pour que le bouton panier + le tiroir
   apparaissent partout :
     <link rel="stylesheet" href="/cart.css">
     ...
     <script src="/cart.js"></script>
   ===================================================================== */
(function () {
  'use strict';

  var CART_KEY = 'b2b_cart';
  var API = '/api/';

  var state = {
    cart: loadCart(),
    user: null,
    authTab: 'login',
    modalProduct: null,
    modalQty: 1,
  };

  // ---------------------------------------------------------------
  // Stockage local du panier
  // ---------------------------------------------------------------
  function loadCart() {
    try {
      var raw = localStorage.getItem(CART_KEY);
      var parsed = raw ? JSON.parse(raw) : [];
      return Array.isArray(parsed) ? parsed : [];
    } catch (e) {
      return [];
    }
  }
  function saveCart() {
    try { localStorage.setItem(CART_KEY, JSON.stringify(state.cart)); } catch (e) {}
    updateCountBadge();
  }
  function cartCount() {
    return state.cart.reduce(function (sum, it) { return sum + (it.qty || 1); }, 0);
  }

  // ---------------------------------------------------------------
  // API produit exposée pour produits.html (bouton "Ajouter au panier"
  // dans la popup produit)
  // ---------------------------------------------------------------
  function addToCart(product, qty) {
    if (!product || !product.name) return;
    qty = Math.max(1, parseInt(qty, 10) || 1);
    var id = product.name; // identifiant simple : le nom du produit
    var existing = state.cart.filter(function (it) { return it.id === id; })[0];
    if (existing) {
      existing.qty += qty;
    } else {
      state.cart.push({
        id: id,
        name: product.name,
        meta: product.meta || '',
        category: product.category || '',
        qty: qty,
      });
    }
    saveCart();
    renderCartBody();
  }

  function removeFromCart(id) {
    state.cart = state.cart.filter(function (it) { return it.id !== id; });
    saveCart();
    renderCartBody();
  }

  function setQty(id, qty) {
    qty = parseInt(qty, 10);
    var item = state.cart.filter(function (it) { return it.id === id; })[0];
    if (!item) return;
    if (!qty || qty < 1) qty = 1;
    item.qty = qty;
    saveCart();
    renderCartBody();
  }

  // ---------------------------------------------------------------
  // Injection du markup (bouton + tiroir) — une seule fois
  // ---------------------------------------------------------------
  var els = {};

  function injectMarkup() {
    // Bouton panier dans la nav desktop, sur chaque page qui a .nav__actions
    var navActions = document.querySelectorAll('.nav__actions');
    navActions.forEach(function (nav) {
      if (nav.querySelector('.b2b-cart-btn')) return;
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'b2b-cart-btn';
      btn.setAttribute('aria-label', 'Panier');
      btn.innerHTML = '🛒<span class="b2b-cart-count" style="display:none;">0</span>';
      nav.insertBefore(btn, nav.firstChild);
      btn.addEventListener('click', openCart);
    });

    // Bouton panier dans le menu burger mobile, à côté de "Devis Rapide"
    var mobileMenus = document.querySelectorAll('.nav__mobile');
    mobileMenus.forEach(function (menu) {
      if (menu.querySelector('.b2b-cart-btn')) return;
      var ctaLink = menu.querySelector('a.btn--primary');
      if (!ctaLink) return;

      var row = document.createElement('div');
      row.className = 'nav__mobile-actions';

      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'b2b-cart-btn';
      btn.setAttribute('aria-label', 'Panier');
      btn.innerHTML = '🛒<span class="b2b-cart-count" style="display:none;">0</span>';

      ctaLink.parentNode.insertBefore(row, ctaLink);
      row.appendChild(btn);
      row.appendChild(ctaLink);

      btn.addEventListener('click', function () {
        menu.classList.remove('open');
        document.body.style.overflow = '';
        openCart();
      });
    });

    // Tiroir panier, une seule instance ajoutée au body
    if (!document.getElementById('b2bCartOverlay')) {
      var wrap = document.createElement('div');
      wrap.innerHTML =
        '<div class="b2b-cart-overlay" id="b2bCartOverlay">' +
          '<div class="b2b-cart-backdrop" data-cart-close></div>' +
          '<aside class="b2b-cart-panel" role="dialog" aria-modal="true" aria-label="Panier">' +
            '<div class="b2b-cart-header">' +
              '<h3>Votre panier</h3>' +
              '<button type="button" class="b2b-cart-close" data-cart-close aria-label="Fermer">✕</button>' +
            '</div>' +
            '<div class="b2b-cart-body" id="b2bCartBody"></div>' +
          '</aside>' +
        '</div>';
      document.body.appendChild(wrap.firstChild);
    }

    els.overlay = document.getElementById('b2bCartOverlay');
    els.body = document.getElementById('b2bCartBody');

    els.overlay.addEventListener('click', function (e) {
      if (e.target && e.target.hasAttribute('data-cart-close')) closeCart();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closeCart();
    });

    // Délégation d'événements dans le corps du tiroir
    els.body.addEventListener('click', handleBodyClick);
    els.body.addEventListener('submit', handleBodySubmit);
    els.body.addEventListener('change', handleBodyChange);
  }

  function updateCountBadge() {
    var n = cartCount();
    document.querySelectorAll('.b2b-cart-count').forEach(function (el) {
      el.textContent = n;
      el.style.display = n > 0 ? 'flex' : 'none';
    });
  }

  function openCart() {
    els.overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
    renderCartBody();
  }
  function closeCart() {
    els.overlay.classList.remove('open');
    document.body.style.overflow = '';
  }

  // ---------------------------------------------------------------
  // Rendu du contenu du tiroir selon l'état (panier / connexion / envoi)
  // ---------------------------------------------------------------
  function esc(str) {
    return String(str || '').replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  function renderCartBody() {
    if (!els.body) return;

    if (state.cart.length === 0) {
      els.body.innerHTML =
        '<div class="b2b-cart-empty">Votre panier est vide.<br>' +
        'Ajoutez des produits depuis <a href="/produits.html">notre catalogue</a>.</div>';
      return;
    }

    var itemsHtml = state.cart.map(function (it) {
      return (
        '<div class="b2b-cart-item" data-id="' + esc(it.id) + '">' +
          '<div class="b2b-cart-item__info"><strong>' + esc(it.name) + '</strong>' +
          (it.meta ? '<span>' + esc(it.meta) + '</span>' : '') + '</div>' +
          '<div class="b2b-cart-item__qty">' +
            '<button type="button" data-action="dec">−</button>' +
            '<input type="number" min="1" value="' + it.qty + '" data-action="qty-input">' +
            '<button type="button" data-action="inc">+</button>' +
          '</div>' +
          '<button type="button" class="b2b-cart-item__remove" data-action="remove" aria-label="Retirer">✕</button>' +
        '</div>'
      );
    }).join('');

    var footerHtml;
    if (state.user) {
      footerHtml =
        '<div class="b2b-cart-user">' +
          '<div class="b2b-cart-user__row"><span>Connecté en tant que <strong>' + esc(state.user.name) + '</strong></span>' +
          '<button type="button" class="b2b-cart-user__logout" data-action="logout">Déconnexion</button></div>' +
          '<button type="button" class="btn btn--primary" data-action="send-order">Envoyer la commande →</button>' +
          '<div class="b2b-cart-order-msg" id="b2bOrderMsg"></div>' +
        '</div>';
    } else {
      footerHtml =
        '<div class="b2b-cart-auth">' +
          '<p class="b2b-cart-auth__intro">Connectez-vous ou créez un compte pour valider votre commande.</p>' +
          '<div class="b2b-cart-auth__tabs">' +
            '<button type="button" data-tab="login" class="' + (state.authTab === 'login' ? 'active' : '') + '">Connexion</button>' +
            '<button type="button" data-tab="register" class="' + (state.authTab === 'register' ? 'active' : '') + '">Créer un compte</button>' +
          '</div>' +
          (state.authTab === 'login'
            ? '<form class="b2b-cart-auth__form" data-form="login">' +
                '<input type="email" name="email" placeholder="Email" required>' +
                '<input type="password" name="password" placeholder="Mot de passe" required>' +
                '<div class="b2b-cart-auth__error" id="b2bAuthError"></div>' +
                '<button type="submit" class="btn btn--primary">Se connecter</button>' +
              '</form>'
            : '<form class="b2b-cart-auth__form" data-form="register">' +
                '<input type="text" name="name" placeholder="Nom complet" required>' +
                '<input type="tel" name="phone" placeholder="Téléphone" required>' +
                '<input type="email" name="email" placeholder="Email" required>' +
                '<input type="password" name="password" placeholder="Mot de passe (6 caractères min.)" required minlength="6">' +
                '<div class="b2b-cart-auth__error" id="b2bAuthError"></div>' +
                '<button type="submit" class="btn btn--primary">Créer mon compte</button>' +
              '</form>') +
        '</div>';
    }

    els.body.innerHTML = itemsHtml + footerHtml;
  }

  // ---------------------------------------------------------------
  // Gestion des clics dans le tiroir (quantités, suppression, tabs, etc.)
  // ---------------------------------------------------------------
  function handleBodyClick(e) {
    var actionEl = e.target.closest('[data-action]');
    if (actionEl) {
      var itemEl = actionEl.closest('.b2b-cart-item');
      var id = itemEl ? itemEl.getAttribute('data-id') : null;
      var action = actionEl.getAttribute('data-action');

      if (action === 'inc' || action === 'dec') {
        var item = state.cart.filter(function (it) { return it.id === id; })[0];
        if (item) setQty(id, action === 'inc' ? item.qty + 1 : item.qty - 1);
      } else if (action === 'remove') {
        removeFromCart(id);
      } else if (action === 'logout') {
        doLogout();
      } else if (action === 'send-order') {
        submitOrder();
      }
      return;
    }

    var tabEl = e.target.closest('[data-tab]');
    if (tabEl) {
      state.authTab = tabEl.getAttribute('data-tab');
      renderCartBody();
    }
  }

  function handleBodyChange(e) {
    if (e.target.getAttribute('data-action') === 'qty-input') {
      var itemEl = e.target.closest('.b2b-cart-item');
      var id = itemEl ? itemEl.getAttribute('data-id') : null;
      setQty(id, e.target.value);
    }
  }

  function handleBodySubmit(e) {
    e.preventDefault();
    var form = e.target;
    var type = form.getAttribute('data-form');
    var data = {};
    Array.prototype.forEach.call(form.elements, function (el) {
      if (el.name) data[el.name] = el.value;
    });

    if (type === 'login') doLogin(data);
    if (type === 'register') doRegister(data);
  }

  function setAuthError(msg) {
    var el = document.getElementById('b2bAuthError');
    if (el) el.textContent = msg || '';
  }

  // ---------------------------------------------------------------
  // Appels API
  // ---------------------------------------------------------------
  function apiCall(endpoint, payload) {
    return fetch(API + endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify(payload || {}),
    }).then(function (res) {
      return res.json().then(function (data) { return { ok: res.ok, data: data }; });
    });
  }

  function checkSession() {
    fetch(API + 'session.php', { credentials: 'same-origin' })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (data && data.logged_in) state.user = data.user;
        renderCartBody();
      })
      .catch(function () {});
  }

  function doLogin(data) {
    setAuthError('');
    apiCall('login.php', data).then(function (r) {
      if (r.ok && r.data.success) {
        state.user = r.data.user;
        renderCartBody();
      } else {
        setAuthError(r.data.error || 'Connexion impossible.');
      }
    }).catch(function () { setAuthError('Erreur réseau, réessayez.'); });
  }

  function doRegister(data) {
    setAuthError('');
    apiCall('register.php', data).then(function (r) {
      if (r.ok && r.data.success) {
        state.user = r.data.user;
        renderCartBody();
      } else {
        setAuthError(r.data.error || 'Inscription impossible.');
      }
    }).catch(function () { setAuthError('Erreur réseau, réessayez.'); });
  }

  function doLogout() {
    apiCall('logout.php', {}).then(function () {
      state.user = null;
      renderCartBody();
    });
  }

  function submitOrder() {
    var msgEl = document.getElementById('b2bOrderMsg');
    var btn = els.body.querySelector('[data-action="send-order"]');
    if (btn) { btn.disabled = true; btn.textContent = 'Envoi en cours…'; }
    if (msgEl) { msgEl.textContent = ''; msgEl.className = 'b2b-cart-order-msg'; }

    apiCall('order.php', { cart: state.cart }).then(function (r) {
      if (r.ok && r.data.success) {
        state.cart = [];
        saveCart();
        els.body.innerHTML =
          '<div class="b2b-cart-empty">✅ Votre commande a bien été envoyée.<br>Notre équipe vous recontacte rapidement.</div>';
      } else {
        if (btn) { btn.disabled = false; btn.textContent = 'Envoyer la commande →'; }
        if (msgEl) { msgEl.textContent = r.data.error || "Erreur lors de l'envoi."; msgEl.className = 'b2b-cart-order-msg error'; }
      }
    }).catch(function () {
      if (btn) { btn.disabled = false; btn.textContent = 'Envoyer la commande →'; }
      if (msgEl) { msgEl.textContent = 'Erreur réseau, réessayez.'; msgEl.className = 'b2b-cart-order-msg error'; }
    });
  }

  // ---------------------------------------------------------------
  // Initialisation
  // ---------------------------------------------------------------
  function init() {
    injectMarkup();
    updateCountBadge();
    checkSession();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // API publique utilisée par produits.html (popup produit)
  window.B2BCart = {
    addToCart: addToCart,
    open: openCart,
  };
})();