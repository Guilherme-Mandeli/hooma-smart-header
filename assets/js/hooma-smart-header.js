// Hooma Smart Header - Main Entry Point
import * as helpers from './hooma-helpers.js';
import { initialModule } from './hooma-initial-behavior.js';
import { scrollModule } from './hooma-scroll-behavior.js';
import { responsiveModule } from './hooma-responsive-behavior.js';
import { logoModule } from './hooma-logo-switcher.js';
import { topHeaderModule } from './hooma-top-header-behavior.js';

document.addEventListener('DOMContentLoaded', () => {
    if (typeof HoomaSHConfig === 'undefined') return;

    // Safety: Define build-time constants if not present (fallback mode)
    if (typeof HOOMA_SH_INITIAL_ENABLED === 'undefined') window.HOOMA_SH_INITIAL_ENABLED = true;
    if (typeof HOOMA_SH_SCROLL_ENABLED === 'undefined') window.HOOMA_SH_SCROLL_ENABLED = true;
    if (typeof HOOMA_SH_RESPONSIVE_ENABLED === 'undefined') window.HOOMA_SH_RESPONSIVE_ENABLED = true;
    if (typeof HOOMA_SH_LOGO_ENABLED === 'undefined') window.HOOMA_SH_LOGO_ENABLED = true;

    // Ensure Global Namespace for compatibility/state
    window.HoomaSH = window.HoomaSH || {};
    const app = window.HoomaSH;
    const config = HoomaSHConfig;

    // Attach helpers and modules to window for legacy support or console debugging
    app.helpers = helpers;
    app.modules = {
        initial: initialModule,
        scroll: scrollModule,
        responsive: responsiveModule,
        logo: logoModule,
        topHeader: topHeaderModule
    };

    // Init Global State
    app.state = {
        lastScrollY: window.scrollY,
        isHidden: false,
        hasShown: false,
        isFixedLayout: false,
        headerHeight: 0,
        topHeaderHeight: 0,
        adminBarHeight: 0
    };

    // Cache Elements
    const header = document.querySelector(config.selectors.header);
    const body = document.body;
    const topHeader = document.getElementById('top-header');
    const adminBar = document.getElementById('wpadminbar');

    if (!header) {
        return;
    }

    app.elements = {
        header: header,
        body: body,
        topHeader: topHeader,
        adminBar: adminBar
    };

    // --- Core Logic ---

    // Measure dimensions and layout state only during layout changes/initialization/resizes
    const measureStaticDimensions = () => {
        if (!header) return;

        const adminBarEl = app.elements.adminBar || document.getElementById('wpadminbar');
        app.state.adminBarHeight = adminBarEl ? adminBarEl.offsetHeight : 0;

        const topHeaderEl = app.elements.topHeader || document.getElementById('top-header');
        app.state.topHeaderHeight = topHeaderEl ? topHeaderEl.offsetHeight : 0;

        app.state.headerHeight = header.offsetHeight;

        const computedStyle = window.getComputedStyle(header);
        app.state.isFixedLayout = (computedStyle.position === 'fixed');
    };
    
    // Expose layout measurement function to state or other modules
    app.measureStaticDimensions = measureStaticDimensions;
    
    const updateHeaderOffset = () => {
        if (!header) return;
        const computedValue = helpers.getCorrectTop();
        document.documentElement.style.setProperty('--hoo-header-top', computedValue + 'px');
    };

    const handleScroll = () => {
        const currentScrollY = window.scrollY;

        // --- Status Classes ---
        if (currentScrollY <= 0) {
            header.classList.add('hoo-is-at-top');
            header.classList.remove('hoo-is-scrolled');
        } else {
            header.classList.remove('hoo-is-at-top');
            header.classList.add('hoo-is-scrolled');
        }

        // Check Fixed Status (using cache to avoid getComputedStyle layout thrashing!)
        if (app.state.isFixedLayout) {
            header.classList.add('hoo-is-fixed');
        } else {
            header.classList.remove('hoo-is-fixed');
        }

        if (currentScrollY === app.state.lastScrollY) return;

        const delta = currentScrollY - app.state.lastScrollY;
        const scrollingDown = delta > 0;
        const scrollingUp = delta < 0;

        // Body Classes
        if (scrollingDown) {
            body.classList.add('hoo-is-scrolling-down');
            body.classList.remove('hoo-is-scrolling-up');
        } else if (scrollingUp) {
            body.classList.add('hoo-is-scrolling-up');
            body.classList.remove('hoo-is-scrolling-down');
        }

        // Logic Delegation
        let handledByPhase1 = false;
        
        // SMART BLOCK: Initial Behavior
        if (HOOMA_SH_INITIAL_ENABLED && initialModule && helpers) {
            if (typeof helpers.isInitialBehaviorActive === 'function' && helpers.isInitialBehaviorActive()) {
                if (typeof initialModule.handleScrollPhase1 === 'function') {
                    handledByPhase1 = initialModule.handleScrollPhase1(currentScrollY);
                }
            }
        }

        if (!handledByPhase1) {
            // SMART BLOCK: Scroll Behavior
            if (HOOMA_SH_SCROLL_ENABLED && scrollModule) {
                if (typeof scrollModule.handleScrollPhase2 === 'function') {
                    scrollModule.handleScrollPhase2(delta, scrollingDown);
                }
            }
        }

        if (app.modules.topHeader && typeof app.modules.topHeader.toggleTopHeader === 'function') {
            app.modules.topHeader.toggleTopHeader();
        }

        app.state.lastScrollY = currentScrollY;
    };

    // --- Init Sequence ---
    
    // SMART BLOCK: Responsive / Layout
    if (HOOMA_SH_RESPONSIVE_ENABLED && responsiveModule) {
        measureStaticDimensions();
        if (typeof responsiveModule.calculateHeight === 'function') responsiveModule.calculateHeight(true);
        if (typeof responsiveModule.init === 'function') responsiveModule.init();
        if (typeof responsiveModule.applyForcedFixed === 'function') responsiveModule.applyForcedFixed();
    }

    // Animation Class
    const animMode = (config.behavior && config.behavior.animation) || 'fade';
    header.classList.add('hoo-anim-' + animMode);

    // SMART BLOCK: Initial Behavior Init
    if (HOOMA_SH_INITIAL_ENABLED && initialModule && typeof initialModule.init === 'function') {
        initialModule.init();
    }

    // SMART BLOCK: Logo Switcher Init
    if (HOOMA_SH_LOGO_ENABLED && logoModule && typeof logoModule.init === 'function') {
        logoModule.init();
    }

    // SMART BLOCK: Top Header Behavior Init
    if (topHeaderModule && typeof topHeaderModule.init === 'function') {
        topHeaderModule.init();
    }

    // Listeners
    const runFinalLayout = () => {
        measureStaticDimensions();
        if (HOOMA_SH_RESPONSIVE_ENABLED) {
            responsiveModule.calculateHeight(true);
            responsiveModule.applyLayoutCompensation(true);
        }
        updateHeaderOffset();

        // Delayed re-validation for layout shifts
        const delayedValidation = () => {
            // Liberar la restricción inicial de altura máxima del top-header
            document.documentElement.style.setProperty('--hoo-top-header-max-height', 'none');
            document.documentElement.style.setProperty('--hoo-top-header-overflow', 'visible');

            measureStaticDimensions();

            if (HOOMA_SH_RESPONSIVE_ENABLED) {
                responsiveModule.calculateHeight(true);
                responsiveModule.applyLayoutCompensation(true);
            }
            updateHeaderOffset();
        };

        setTimeout(delayedValidation, 500);
        setTimeout(delayedValidation, 1500);
    };

    if (document.readyState === 'complete') {
        setTimeout(runFinalLayout, 10);
    } else {
        window.addEventListener('load', () => setTimeout(runFinalLayout, 10));
    }

    let lastWidth = window.innerWidth;
    let scrollTicking = false;
    let scrollEndTimeout = null;

    const resolveFinalHeaderState = () => {
        const currentScrollY = window.scrollY;
        const state = app.state;
        const elements = app.elements;
        if (!elements || !elements.header) return;

        // If at the very top, always ensure visible (unless hide_on_scroll is active)
        if (currentScrollY <= 0) {
            const config = HoomaSHConfig;
            if (config.behavior && config.behavior.hide_on_scroll === '1') {
                return;
            }
            if (state.isHidden) {
                elements.header.classList.remove('hoo-is-hidden');
                elements.header.classList.add('hoo-is-visible');
                state.isHidden = false;
            }
            return;
        }

        // Self-healing: make sure DOM matches state.isHidden cleanly
        if (state.isHidden) {
            if (!elements.header.classList.contains('hoo-is-hidden')) {
                elements.header.classList.add('hoo-is-hidden');
                elements.header.classList.remove('hoo-is-visible');
            }
        } else {
            if (!elements.header.classList.contains('hoo-is-visible')) {
                elements.header.classList.remove('hoo-is-hidden');
                elements.header.classList.add('hoo-is-visible');
            }
        }

        if (app.modules.topHeader && typeof app.modules.topHeader.toggleTopHeader === 'function') {
            app.modules.topHeader.toggleTopHeader();
        }
    };

    const handleScrollThrottled = () => {
        if (!scrollTicking) {
            window.requestAnimationFrame(() => {
                handleScroll();
                scrollTicking = false;
            });
            scrollTicking = true;
        }

        // Scroll end self-healing check (fallback for older browsers)
        clearTimeout(scrollEndTimeout);
        scrollEndTimeout = setTimeout(resolveFinalHeaderState, 150);
    };

    window.addEventListener('resize', () => {
        const currentWidth = window.innerWidth;
        if (currentWidth === lastWidth) {
            // Skip execution if width has not changed (e.g. mobile address bar toggled height only)
            return;
        }
        lastWidth = currentWidth;

        measureStaticDimensions();
        app.state.lastScrollY = window.scrollY;
        if (HOOMA_SH_RESPONSIVE_ENABLED) {
            responsiveModule.applyLayoutCompensation(true);
            responsiveModule.applyForcedFixed();
        }
        updateHeaderOffset();
        handleScroll();
    });

    window.addEventListener('scroll', handleScrollThrottled, { passive: true });
    
    if ('onscrollend' in window) {
        window.addEventListener('scrollend', resolveFinalHeaderState, { passive: true });
    }

    handleScroll(); // Init status classes

    // Sticky Observer
    const stickyObserverTarget = config.selectors.sticky ? document.querySelector(config.selectors.sticky) : header;
    if (stickyObserverTarget) {
        const observer = new MutationObserver(() => {
            let isSticky = false;
            if (config.selectors.sticky && config.selectors.sticky.startsWith('.')) {
                const cls = config.selectors.sticky.substring(1);
                if (stickyObserverTarget.classList.contains(cls)) {
                    isSticky = true;
                }
            } else {
                const style = window.getComputedStyle(stickyObserverTarget);
                if (style.position === 'fixed' || style.position === 'sticky') {
                    isSticky = true;
                }
            }

            if (isSticky) {
                if (!header.classList.contains('hoo-is-sticky')) {
                    header.classList.add('hoo-is-sticky');
                }
            } else {
                if (header.classList.contains('hoo-is-sticky')) {
                    header.classList.remove('hoo-is-sticky');
                }
            }
        });
        observer.observe(stickyObserverTarget, { attributes: true, attributeFilter: ['class', 'style'] });
    }
});
