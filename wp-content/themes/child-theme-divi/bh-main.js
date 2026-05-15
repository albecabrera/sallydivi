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
        if (window.innerWidth >= 980) return;

        var overlay = document.createElement('div');
        overlay.className = 'bh-sidenav-overlay';
        document.body.appendChild(overlay);

        var sidenav = document.createElement('nav');
        sidenav.className = 'bh-sidenav';
        sidenav.setAttribute('aria-label', 'Navegación principal');

        var headerMenu = document.querySelector('.social_header_sec1 .et_pb_menu__menu ul.et-menu');
        if (headerMenu) {
            var clone = headerMenu.cloneNode(true);
            clone.removeAttribute('id');
            sidenav.appendChild(clone);
        }

        document.body.appendChild(sidenav);

        var isOpen = false;
        var mobileNav = document.querySelector('.social_header_sec1 .mobile_nav');

        function openNav() {
            sidenav.classList.add('open');
            overlay.classList.add('open');
            document.body.classList.add('bh-sidenav-open');
            if (mobileNav) { mobileNav.classList.remove('closed'); mobileNav.classList.add('opened'); }
            isOpen = true;
        }

        function closeNav() {
            sidenav.classList.remove('open');
            overlay.classList.remove('open');
            document.body.classList.remove('bh-sidenav-open');
            if (mobileNav) { mobileNav.classList.remove('opened'); mobileNav.classList.add('closed'); }
            isOpen = false;
        }

        var bar = document.querySelector('.social_header_sec1 .mobile_menu_bar');
        if (bar) {
            bar.addEventListener('click', function (e) {
                e.stopImmediatePropagation();
                e.preventDefault();
                isOpen ? closeNav() : openNav();
            }, true);
        }

        overlay.addEventListener('click', closeNav);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && isOpen) closeNav();
        });

        sidenav.addEventListener('click', function (e) {
            if (e.target.tagName === 'A' && e.target.getAttribute('href')) closeNav();
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth >= 980 && isOpen) closeNav();
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
                        node.innerHTML = '<p>Design &amp; Entwicklung: <a href="https://deinewebseite.de">Alberto Cabrera</a></p>';
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
    function initCarousel() {
        var endpoint = cfg.carouselEndpoint || '';
        if (!endpoint) return;

        function mountCarousel() {
            var existingWrap = document.getElementById('bh-ig-carousel-wrap');
            if (existingWrap) {
                existingWrap.style.display = 'block';
                if (existingWrap.querySelector('.instagram-media, iframe, .sbi_item')) return;
                existingWrap.remove();
            }

            var readMoreBtn = Array.from(document.querySelectorAll('a,button,.et_pb_button'))
                .find(function (el) { return norm(el.textContent) === 'read more'; });
            var marker = readMoreBtn || Array.from(document.querySelectorAll('h1,h2,h3,h4,p,strong,.et_pb_text_inner'))
                .find(function (el) { return norm(el.textContent).indexOf('meine letztes feeds') !== -1; });
            if (!marker) return;

            fetch(endpoint)
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data || !Array.isArray(data.posts) || !data.posts.length) return;

                    var wrap = document.createElement('div');
                    wrap.id = 'bh-ig-carousel-wrap';
                    wrap.innerHTML = '<div class="bh-ig-row">' +
                        '<button type="button" class="bh-ig-nav bh-ig-prev" aria-label="Zurück">&#8249;</button>' +
                        '<div class="bh-ig-track"></div>' +
                        '<button type="button" class="bh-ig-nav bh-ig-next" aria-label="Weiter">&#8250;</button>' +
                        '</div>';
                    var track = wrap.querySelector('.bh-ig-track');

                    data.posts.slice(0, 4).forEach(function (url) {
                        var item = document.createElement('div');
                        item.className = 'bh-ig-item';
                        var bq = document.createElement('blockquote');
                        bq.className = 'instagram-media';
                        bq.setAttribute('data-instgrm-permalink', url);
                        bq.setAttribute('data-instgrm-version', '14');
                        item.appendChild(bq);
                        track.appendChild(item);
                    });

                    var row = marker.closest('.et_pb_row');
                    if (row) {
                        row.classList.add('bh-feed-block-center');
                        row.insertAdjacentElement('afterend', wrap);
                    } else {
                        var anchor = marker.closest('.et_pb_button_module_wrapper') ||
                            marker.closest('.et_pb_module') || marker;
                        anchor.insertAdjacentElement('afterend', wrap);
                    }
                    wrap.style.display = 'block';

                    wrap.querySelector('.bh-ig-prev').addEventListener('click', function () {
                        track.scrollBy({ left: -320, behavior: 'smooth' });
                    });
                    wrap.querySelector('.bh-ig-next').addEventListener('click', function () {
                        track.scrollBy({ left: 320, behavior: 'smooth' });
                    });

                    if (!document.getElementById('instagram-embed-js')) {
                        var s = document.createElement('script');
                        s.id = 'instagram-embed-js';
                        s.async = true;
                        s.src = 'https://www.instagram.com/embed.js';
                        s.onload = function () {
                            if (window.instgrm && window.instgrm.Embeds) window.instgrm.Embeds.process();
                        };
                        document.body.appendChild(s);
                    } else if (window.instgrm && window.instgrm.Embeds) {
                        window.instgrm.Embeds.process();
                    }
                })
                .catch(function () {});
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', mountCarousel);
        } else {
            mountCarousel();
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

    // ── Init ────────────────────────────────────────────────────────────────────
    initSidenav();
    initFooterMenu();
    initLegalBar();
    initFeedCleanup();
    initObjCleaner();
    initCarousel();
    if (cfg.isWechseljahre) initPopupGuard();

}(window.bhData || {}));
