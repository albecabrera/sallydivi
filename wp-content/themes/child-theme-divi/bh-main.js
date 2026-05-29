/* Sally Divi – consolidated front-end JS
 * All data injected by PHP lives in window.bhData (wp_localize_script).
 */
(function (cfg) {
    'use strict';

    function norm(s) {
        return (s || '').replace(/\s+/g, ' ').trim().toLowerCase();
    }

    // ── 1. Header sidenav ───────────────────────────────────────────────────────
    function initSidenav() {
        if (window.innerWidth > 980) return;

        var overlay = document.createElement('div');
        overlay.className = 'bh-sidenav-overlay';
        document.body.appendChild(overlay);

        var sidenav = document.createElement('nav');
        sidenav.className = 'bh-sidenav';
        sidenav.setAttribute('aria-label', 'Navegación principal');

        if (cfg.navItems && cfg.navItems.length) {
            var ul = document.createElement('ul');
            ul.className = 'et-menu nav';
            var currentPageId = document.body.className.match(/page-id-(\d+)/);
            currentPageId = currentPageId ? currentPageId[1] : '';
            cfg.navItems.forEach(function (item) {
                var li = document.createElement('li');
                li.className = 'menu-item et_pb_menu_page_id-' + item.pageId;
                if (String(item.pageId) === currentPageId) li.classList.add('current-menu-item');
                var a = document.createElement('a');
                a.href = item.url;
                a.textContent = item.label;
                li.appendChild(a);
                ul.appendChild(li);
            });
            sidenav.appendChild(ul);
        } else {
            var headerMenu = document.querySelector('.social_header_sec1 .et_pb_menu__menu ul.et-menu');
            if (headerMenu) {
                var clone = headerMenu.cloneNode(true);
                clone.removeAttribute('id');
                sidenav.appendChild(clone);
            }
        }

        document.body.appendChild(sidenav);

        var isOpen = false;

        function openNav() {
            sidenav.classList.add('open');
            overlay.classList.add('open');
            isOpen = true;
        }

        function closeNav() {
            sidenav.classList.remove('open');
            overlay.classList.remove('open');
            isOpen = false;
        }

        var bar = document.querySelector('.social_header_sec1 .mobile_menu_bar');
        if (bar) {
            // bh-main.js is deferred → loads AFTER Divi, so Divi's capture listeners
            // are already registered first. stopImmediatePropagation() on click won't
            // stop Divi from handling the tap.
            // Fix: intercept touchstart in capture phase and call preventDefault(),
            // which blocks the browser from generating the synthetic click event entirely
            // → Divi's click handler never fires on touch devices.
            // On pointer-only devices (desktop) touchstart never fires, click still works.
            var lastHandled = 0;
            function handleHamburger() {
                var now = Date.now();
                if (now - lastHandled < 500) return;
                lastHandled = now;
                isOpen ? closeNav() : openNav();
            }
            bar.addEventListener('touchstart', function (e) {
                e.preventDefault();
                handleHamburger();
            }, { passive: false, capture: true });
            bar.addEventListener('click', function (e) {
                e.stopImmediatePropagation();
                e.preventDefault();
                handleHamburger();
            }, true);
        }

        overlay.addEventListener('click', closeNav);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && isOpen) closeNav();
        });

        sidenav.addEventListener('click', function (e) {
            var a = e.target.closest ? e.target.closest('a[href]') : null;
            if (a) closeNav();
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth > 980 && isOpen) closeNav();
        });
    }

    // ── 2. Footer hamburger menu ────────────────────────────────────────────────
    function initFooterMenu() {
        var mobileMenu = null;

        function buildMenu() {
            var wrap = document.querySelector('.et_pb_menu_0_tb_footer');
            if (!wrap) return null;
            var desktop = wrap.querySelector('.et_pb_menu__menu ul.et-menu');
            if (!desktop) return null;

            var ul = document.createElement('ul');
            ul.className = 'bh-footer-mobile-menu';
            Array.prototype.forEach.call(desktop.children, function (li) {
                ul.appendChild(li.cloneNode(true));
            });
            document.body.appendChild(ul);
            return ul;
        }

        function closeMenu() {
            if (!mobileMenu) return;
            mobileMenu.classList.remove('open');
            var nav = document.querySelector('.et_pb_menu_0_tb_footer .mobile_nav');
            if (nav) { nav.classList.remove('opened'); nav.classList.add('closed'); }
        }

        var lastToggleTime = 0;

        function handleBarInteraction(e) {
            var bar = e.target.closest
                ? e.target.closest('.et_pb_menu_0_tb_footer .mobile_menu_bar')
                : null;

            if (!bar) {
                if (mobileMenu && !mobileMenu.contains(e.target)) closeMenu();
                return;
            }

            e.preventDefault();
            e.stopPropagation();

            var now = Date.now();
            if (now - lastToggleTime < 400) return;
            lastToggleTime = now;

            var isOpen = mobileMenu && mobileMenu.classList.contains('open');
            if (isOpen) {
                closeMenu();
            } else {
                if (mobileMenu && mobileMenu.parentNode) mobileMenu.parentNode.removeChild(mobileMenu);
                mobileMenu = buildMenu();
                if (!mobileMenu) return;

                var rect = bar.getBoundingClientRect();
                mobileMenu.style.setProperty('bottom', (window.innerHeight - rect.top) + 'px', 'important');
                mobileMenu.classList.add('open');

                var nav = document.querySelector('.et_pb_menu_0_tb_footer .mobile_nav');
                if (nav) { nav.classList.add('opened'); nav.classList.remove('closed'); }
            }
        }

        document.addEventListener('touchend', handleBarInteraction, true);
        document.addEventListener('click', handleBarInteraction, true);

        window.addEventListener('resize', function () {
            if (mobileMenu && mobileMenu.classList.contains('open')) {
                var footBar = document.querySelector('.et_pb_menu_0_tb_footer .mobile_menu_bar');
                if (footBar) {
                    var rect = footBar.getBoundingClientRect();
                    mobileMenu.style.setProperty('bottom', (window.innerHeight - rect.top) + 'px', 'important');
                }
            }
        });
    }

    // ── 3. Legal bar (Impressum / Datenschutz below footer) ────────────────────
    function initLegalBar() {
        var impressumUrl    = cfg.impressumUrl    || '';
        var datenschutzUrl  = cfg.datenschutzUrl  || '';

        function hideHeaderImpressum() {
            document.querySelectorAll('header a, #main-header a, .et-l--header a').forEach(function (a) {
                if (norm(a.textContent) === 'impressum') a.style.display = 'none';
            });
        }

        function hideFooterImpressumLinks() {
            document.querySelectorAll('footer a, .et-l--footer a, #footer-bottom a').forEach(function (a) {
                if (a.closest('.bh-impressum-below-footer')) return;
                if (norm(a.textContent) === 'impressum') a.style.display = 'none';
            });
        }

        function ensureImpressumBelowFooter() {
            var footerBottom = document.querySelector('#footer-bottom') ||
                document.querySelector('.et-l--footer') ||
                document.querySelector('footer');
            if (!footerBottom) return;
            if (!document.querySelector('.bh-impressum-below-footer')) {
                var wrap = document.createElement('div');
                wrap.className = 'bh-impressum-below-footer';
                wrap.innerHTML = '<a href="' + impressumUrl + '">Impressum</a> | <a href="' + datenschutzUrl + '">Datenschutz</a>';
                footerBottom.insertAdjacentElement('afterend', wrap);
            }
        }

        function replaceLegalTextInFooter() {
            document.querySelectorAll('.bh-legal-bar').forEach(function (el) { el.remove(); });

            document.querySelectorAll('#footer-bottom, .et-l--footer, footer').forEach(function (root) {
                root.querySelectorAll('*').forEach(function (node) {
                    var t = norm(node.textContent);
                    if (!t) return;
                    if (t === 'impressum | datenschutz' || t === 'impressum|datenschutz') {
                        node.innerHTML = '<p>Design &amp; Entwicklung: Alberto Cabrera</p>';
                    }
                });
            });

            hideHeaderImpressum();
            hideFooterImpressumLinks();
            ensureImpressumBelowFooter();
        }

        var mo = null;

        function runAndMaybeStop() {
            replaceLegalTextInFooter();
            if (document.querySelector('.bh-impressum-below-footer') && mo) {
                mo.disconnect();
                mo = null;
            }
        }

        runAndMaybeStop();
        if (!document.querySelector('.bh-impressum-below-footer')) {
            mo = new MutationObserver(runAndMaybeStop);
            mo.observe(document.body, { childList: true, subtree: true });
            setTimeout(function () { if (mo) { mo.disconnect(); mo = null; } }, 5000);
        }
    }

    // ── 4. Feed leftovers cleanup ───────────────────────────────────────────────
    function initFeedCleanup() {
        function cleanup() {
            var hasMarker = Array.from(document.querySelectorAll('h1,h2,h3,h4,p,strong,.et_pb_text_inner'))
                .some(function (el) {
                    var t = norm(el.textContent);
                    return t.indexOf('meine letztes feed') !== -1 || t.indexOf('mein letztes feed') !== -1;
                });
            if (!hasMarker) return;

            document.querySelectorAll(
                '#bh-ig-feeds-wrap, #sb_instagram, .sbi, .sbi_item, .instagram-media, iframe[src*="instagram.com"]'
            ).forEach(function (el) {
                if (el.id === 'bh-ig-carousel-wrap' || el.closest('#bh-ig-carousel-wrap')) return;
                if (el.id === 'sif-wrap' || el.closest('.sif-wrap')) return;
                if (el.closest('.et-l--footer, footer, header, .et-l--header')) return;
                if (el.parentNode) el.parentNode.removeChild(el);
            });
        }

        var feedMo = null;

        function cleanupAndMaybeStop() {
            cleanup();
            var hasMarker = Array.from(document.querySelectorAll('h1,h2,h3,h4,p,strong,.et_pb_text_inner'))
                .some(function (el) {
                    var t = norm(el.textContent);
                    return t.indexOf('meine letztes feed') !== -1 || t.indexOf('mein letztes feed') !== -1;
                });
            if (!hasMarker && feedMo) { feedMo.disconnect(); feedMo = null; }
        }

        cleanupAndMaybeStop();
        if (feedMo === null && document.querySelectorAll('#bh-ig-feeds-wrap, #sb_instagram, .sbi').length) {
            feedMo = new MutationObserver(cleanupAndMaybeStop);
            feedMo.observe(document.body, { childList: true, subtree: true });
            setTimeout(function () { if (feedMo) { feedMo.disconnect(); feedMo = null; } }, 4000);
        }
    }

    // ── 5. OBJ character cleaner ────────────────────────────────────────────────
    function initObjCleaner() {
        function run() {
            var root = document.querySelector('#main-content, #page-container, body');
            if (!root) return;
            var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, null);
            var nodes = [];
            while (walker.nextNode()) nodes.push(walker.currentNode);
            nodes.forEach(function (n) {
                var t = n.nodeValue || '';
                var cleaned = t.replace(/￼/g, '').replace(/\[OBJ\]/gi, '');
                if (cleaned !== t) n.nodeValue = cleaned;
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', run);
        } else {
            run();
        }
        window.addEventListener('load', run);
    }

    // ── 6. Instagram carousel ───────────────────────────────────────────────────
    // ── 6. Instagram carousel (blockquote embeds) ──────────────────────────────
    function initCarousel() {
        var endpoint = cfg.carouselEndpoint || '';
        if (!endpoint) return;
        var onHome = document.body.classList.contains('page-id-987550602');
        if (!onHome) return;

        var currentUrls = [];

        function toEmbedUrl(postUrl) {
            var m = postUrl.match(/instagram\.com\/(p|reel)\/([A-Za-z0-9_-]+)/);
            return m ? 'https://www.instagram.com/' + m[1] + '/' + m[2] + '/embed/' : null;
        }

        function buildTrack(track, posts) {
            track.innerHTML = '';
            posts.slice(0, 4).forEach(function (url, idx) {
                var embedUrl = toEmbedUrl(url);
                if (!embedUrl) return;
                var item = document.createElement('div');
                item.className = 'bh-ig-item';
                item.style.animationDelay = ((idx + 1) * 0.08).toFixed(2) + 's';
                var iframe = document.createElement('iframe');
                iframe.src = embedUrl;
                iframe.setAttribute('frameborder', '0');
                iframe.setAttribute('scrolling', 'no');
                iframe.setAttribute('allowtransparency', 'true');
                iframe.setAttribute('loading', 'lazy');
                item.appendChild(iframe);
                track.appendChild(item);
            });
        }

        function refreshCarousel() {
            var wrap = document.getElementById('bh-ig-carousel-wrap');
            if (!wrap) return;
            var track = wrap.querySelector('.bh-ig-track');
            if (!track) return;
            fetch(endpoint + '?t=' + Date.now())
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data || !Array.isArray(data.posts) || !data.posts.length) return;
                    var newUrls = data.posts.slice(0, 4);
                    if (newUrls.join('|') === currentUrls.join('|')) return;
                    currentUrls = newUrls;
                    track.style.opacity = '0';
                    setTimeout(function () {
                        buildTrack(track, newUrls);
                        track.style.opacity = '1';
                    }, 400);
                }).catch(function () {});
        }

        function findAnchor() {
            var el = Array.from(document.querySelectorAll('.et_pb_text_inner, .et_pb_heading_container'))
                .find(function (e) { return /meine letzte[s]?\s+feeds/i.test(e.textContent); });
            return el ? el.closest('.et_pb_section') : null;
        }

        function mount() {
            if (document.getElementById('bh-ig-carousel-wrap')) return;
            var anchor = findAnchor();
            if (!anchor) return;

            fetch(endpoint + '?t=' + Date.now())
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data || !Array.isArray(data.posts) || !data.posts.length) return;
                    currentUrls = data.posts.slice(0, 4);

                    var wrap = document.createElement('div');
                    wrap.id = 'bh-ig-carousel-wrap';
                    wrap.innerHTML = '<div class="bh-ig-row"><div class="bh-ig-track"></div></div>';
                    var track = wrap.querySelector('.bh-ig-track');
                    buildTrack(track, currentUrls);
                    anchor.insertAdjacentElement('afterend', wrap);

                    setInterval(refreshCarousel, 600000);
                }).catch(function () {});
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', mount);
        } else {
            mount();
        }
    }

    // ── 7. Newsletter popup guard (Wechseljahrecoaching only) ──────────────────
    function initPopupGuard() {
        var popup = document.querySelector('.et-interaction-target-nds7mk13ev') ||
            document.querySelector('.et_pb_section_3_tb_footer');
        var trigger = document.querySelector('[data-interaction-trigger="p40hyahirx"]');

        if (!popup) return;

        var intentional = false;
        var popupObs, headObs;

        function hide() {
            popup.style.setProperty('display',        'none',   'important');
            popup.style.setProperty('visibility',     'hidden', 'important');
            popup.style.setProperty('opacity',        '0',      'important');
            popup.style.setProperty('pointer-events', 'none',   'important');
        }

        function release() {
            intentional = true;
            var s = document.getElementById('bh-nl-popup-hide');
            if (s) s.parentNode.removeChild(s);
            popup.style.removeProperty('display');
            popup.style.removeProperty('visibility');
            popup.style.removeProperty('opacity');
            popup.style.removeProperty('pointer-events');
            try { delete popup.style.setProperty;    } catch (e) {}
            try { delete popup.style.removeProperty; } catch (e) {}
            if (popupObs) popupObs.disconnect();
            if (headObs)  headObs.disconnect();
        }

        try {
            var nativeSP = CSSStyleDeclaration.prototype.setProperty;
            popup.style.setProperty = function (prop, val, priority) {
                if (!intentional && prop === 'display' && val !== 'none') {
                    return nativeSP.call(this, 'display', 'none', 'important');
                }
                return nativeSP.call(this, prop, val, priority);
            };
        } catch (e) {}

        try {
            var nativeRP = CSSStyleDeclaration.prototype.removeProperty;
            popup.style.removeProperty = function (prop) {
                if (!intentional && prop === 'display') return '';
                return nativeRP.call(this, prop);
            };
        } catch (e) {}

        if (trigger) trigger.addEventListener('click', release, true);

        popupObs = new MutationObserver(function () {
            if (intentional) { popupObs.disconnect(); return; }
            if (window.getComputedStyle(popup).display !== 'none') hide();
        });
        popupObs.observe(popup, { attributes: true, attributeFilter: ['style', 'class'] });

        headObs = new MutationObserver(function () {
            if (intentional) { headObs.disconnect(); return; }
            if (window.getComputedStyle(popup).display !== 'none') hide();
        });
        headObs.observe(document.head, { childList: true });

        hide();
        window.addEventListener('load', function () { if (!intentional) hide(); });
    }

    // ── 8. 404 enhancements ─────────────────────────────────────────────────────
    function init404() {
        if (!document.body.classList.contains('error404')) return;

        // Translate "Back to Home" button to German
        document.querySelectorAll('a.et_pb_button, .et_pb_button').forEach(function (btn) {
            if ((btn.textContent || '').trim().toLowerCase() === 'back to home') {
                btn.textContent = 'Zur Startseite';
            }
        });

        // Inject a human-readable explanation after the "Error 404" heading
        var headings = Array.from(document.querySelectorAll('h2, h1'));
        var errorHeading = headings.find(function (h) {
            return /error\s*404/i.test(h.textContent);
        });
        if (errorHeading && !document.getElementById('bh-404-msg')) {
            var msg = document.createElement('h1');
            msg.id = 'bh-404-msg';
            msg.textContent = 'Diese Seite existiert leider nicht. Bitte überprüfe die URL oder kehre zur Startseite zurück.';
            errorHeading.insertAdjacentElement('afterend', msg);
        }
    }

    // ── 9. Cookie consent banner ────────────────────────────────────────────────
    function initCookieBanner() {
        var KEY = 'bh-cookie-consent';
        var fontsUrl = cfg.googleFontsUrl || '';

        function loadFonts() {
            if (!fontsUrl || document.getElementById('bh-gfonts')) return;
            var link = document.createElement('link');
            link.id = 'bh-gfonts';
            link.rel = 'stylesheet';
            link.href = fontsUrl;
            document.head.appendChild(link);
        }

        var consent = localStorage.getItem(KEY);
        if (consent === 'accepted') { loadFonts(); return; }
        if (consent === 'declined') { return; }

        var datenschutzUrl = cfg.datenschutzUrl || '/datenschutz/';

        var banner = document.createElement('div');
        banner.id = 'bh-cookie-banner';
        banner.setAttribute('role', 'dialog');
        banner.setAttribute('aria-labelledby', 'bh-cookie-msg');
        banner.innerHTML =
            '<div class="bh-cookie-inner">' +
                '<p id="bh-cookie-msg">Wir verwenden technisch notwendige Cookies sowie <strong>Google Fonts</strong> ' +
                'für ein einheitliches Schriftbild. Weitere Infos in unserer ' +
                '<a href="' + datenschutzUrl + '">Datenschutzerklärung</a>.</p>' +
                '<div class="bh-cookie-actions">' +
                    '<button id="bh-cookie-accept" type="button">Alle akzeptieren</button>' +
                    '<button id="bh-cookie-decline" type="button">Nur notwendige</button>' +
                '</div>' +
            '</div>';

        document.body.appendChild(banner);

        document.getElementById('bh-cookie-accept').addEventListener('click', function () {
            localStorage.setItem(KEY, 'accepted');
            loadFonts();
            banner.remove();
        });

        document.getElementById('bh-cookie-decline').addEventListener('click', function () {
            localStorage.setItem(KEY, 'declined');
            banner.remove();
        });
    }

    // ── 9. Scroll cue (hero chevron) ────────────────────────────────────────────
    function initScrollCue() {
        var hero = document.querySelector('.et_pb_fullwidth_slider_0');
        if (!hero) return;

        var cue = document.createElement('button');
        cue.className = 'bh-scroll-cue';
        cue.setAttribute('aria-label', 'Nach unten scrollen');
        cue.innerHTML =
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" ' +
            'stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">' +
            '<polyline points="6 9 12 15 18 9"></polyline></svg>';
        document.body.appendChild(cue);

        var heroSection = hero.closest('.et_pb_section') || hero.parentElement;

        cue.addEventListener('click', function () {
            var next = heroSection && heroSection.nextElementSibling;
            if (next) {
                next.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                window.scrollBy({ top: window.innerHeight * 0.9, behavior: 'smooth' });
            }
        });

        if ('IntersectionObserver' in window) {
            var obs = new IntersectionObserver(function (entries) {
                cue.classList.toggle('bh-scroll-cue--hidden', !entries[0].isIntersecting);
            }, { threshold: 0.05 });
            obs.observe(hero);
        } else {
            window.addEventListener('scroll', function () {
                cue.classList.toggle('bh-scroll-cue--hidden',
                    hero.getBoundingClientRect().bottom <= 0);
            }, { passive: true });
        }
    }

    // ── 10. Desktop nav: bypass Divi's dropdown-toggle on parent items ──────────
    function initDesktopNav() {
        if (window.innerWidth <= 980) return;
        // Divi registers a touchend handler (bubbling) on li.menu-item-has-children
        // that always calls preventDefault(), blocking navigation on first tap.
        // Our touchend capture handler on <a> fires before Divi's bubbling handler
        // on <li> → stopImmediatePropagation + navigate directly on first tap.
        // For mouse clicks, stopImmediatePropagation stops Divi's jQuery click handlers.
        document.querySelectorAll(
            '.social_header_sec1 .et_pb_menu__menu .et-menu > li.menu-item-has-children > a'
        ).forEach(function (a) {
            a.addEventListener('touchend', function (e) {
                var href = a.getAttribute('href');
                if (href && href !== '#') {
                    e.stopImmediatePropagation();
                    e.preventDefault();
                    window.location.href = href;
                }
            }, { capture: true, passive: false });
            a.addEventListener('click', function (e) {
                e.stopImmediatePropagation();
            }, true);
        });
    }

    // ── Init ────────────────────────────────────────────────────────────────────
    // initSidenav + initDesktopNav deferred to window.load so Divi's JS finishes
    // its menu DOM manipulation first.
    function initOnLoad() {
        initSidenav();
        initDesktopNav();
    }
    if (document.readyState === 'complete') {
        initOnLoad();
    } else {
        window.addEventListener('load', initOnLoad);
    }
    initFooterMenu();
    initLegalBar();
    initFeedCleanup();
    initObjCleaner();
    initCarousel();
    initScrollCue();
    if (cfg.isWechseljahre) initPopupGuard();
    init404();
    initCookieBanner();

}(window.bhData || {}));
