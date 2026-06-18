// Hooma Smart Header - Responsive Behavior (Layout)

export const responsiveModule = {
    compensationHeight: 0,
    initTime: 0,
    lastWidth: 0,

    init: function () {
        this.initTime = Date.now();
        this.lastWidth = window.innerWidth;
        
        // Ensure state is fully ready
        if (window.HoomaSH && !window.HoomaSH.state) {
            window.HoomaSH.state = {};
        }
        if (window.HoomaSH.state) {
            window.HoomaSH.state.isTransitioning = false;
        }

        this.ensurePlaceholder();
        this.applyLayoutCompensation(true);
        this.initCacheBridge();

        // Listeners
        window.addEventListener('resize', () => {
            const currentWidth = window.innerWidth;
            if (currentWidth === this.lastWidth) {
                // Skip execution if width has not changed (e.g. mobile address bar toggled height only)
                return;
            }
            this.lastWidth = currentWidth;
            this.applyLayoutCompensation(true);
        });

        // Monitor real-time header height changes for --hoo-dynamic-header-height
        const elements = window.HoomaSH.elements;
        const topHeader = document.getElementById('top-header');
        
        if (elements && elements.header) {
            // Transition Guards
            elements.header.addEventListener('transitionstart', () => {
                if (window.HoomaSH.state) window.HoomaSH.state.isTransitioning = true;
            });

            elements.header.addEventListener('transitionend', () => {
                if (window.HoomaSH.state) window.HoomaSH.state.isTransitioning = false;
                // Recalculate once transition finishes to ensure exact heights are captured
                this.applyLayoutCompensation(true);
            });

            if (window.ResizeObserver) {
                // Initialize the dynamic variables
                this.calculateHeight(true);

                const headerObserver = new ResizeObserver(() => {
                    if (window.HoomaSH.state && window.HoomaSH.state.isTransitioning) {
                        return; // Ignore updates during CSS transitions
                    }
                    this.applyLayoutCompensation();
                });
                
                headerObserver.observe(elements.header);
                if (topHeader) headerObserver.observe(topHeader);
            }
        }
    },

    ensurePlaceholder: function () {
        const config = window.HoomaSHConfig;
        const elements = window.HoomaSH.elements;
        if (config && config.layout && config.layout.placeholder === '1' && elements && elements.header) {
            let placeholder = document.getElementById('hoo-sh-placeholder');
            if (!placeholder) {
                placeholder = document.createElement('div');
                placeholder.id = 'hoo-sh-placeholder';
                if (elements.header.nextSibling) {
                    elements.header.parentNode.insertBefore(placeholder, elements.header.nextSibling);
                } else {
                    elements.header.parentNode.appendChild(placeholder);
                }
            }
        }
    },

    calculateHeight: function (force = false) {
        const config = window.HoomaSHConfig;
        const elements = window.HoomaSH.elements;
        const topHeader = document.getElementById('top-header');
        
        if (!elements.header) return;

        // We only update the BASE height (--hoo-header-height) if the header is at its natural state (top)
        // or if we are forcing the update (e.g., during initialization or resize).
        const isShrunken = elements.header.classList.contains('hoo-is-scrolled') || 
                           elements.header.classList.contains('hoo-is-sticky') ||
                           elements.header.classList.contains('hoo-is-hidden');

        let hMain = 0;
        let hTop = 0;

        if (config.layout.height_mode === 'manual' && config.layout.height_val > 0) {
            hMain = config.layout.height_val;
        } else {
            hMain = elements.header.offsetHeight;
        }

        if (topHeader) {
            hTop = topHeader.offsetHeight;
        }

        if (this.initTime > 0 && (Date.now() - this.initTime < 500)) {
            const width = window.innerWidth;
            const mobileSettings = config.mobile || {};
            const bpMobile = parseInt(mobileSettings.breakpoint) || parseInt(mobileSettings.mobile_breakpoint) || 768;
            const bpTablet = parseInt(mobileSettings.tablet_breakpoint) || 1024;
            
            const isMobile = width <= bpMobile;
            const isTablet = width > bpMobile && width <= bpTablet;

            // Desktop base
            let bhDesk = parseFloat(config.layout.backup_height);
            bhDesk = isNaN(bhDesk) ? 0 : bhDesk;
            let btDesk = parseFloat(config.layout.backup_offset_height);
            btDesk = isNaN(btDesk) ? 0 : btDesk;

            // Tablet cascade
            let bhTablet = parseFloat(config.layout.backup_height_tablet);
            bhTablet = isNaN(bhTablet) ? bhDesk : bhTablet;
            let btTablet = parseFloat(config.layout.backup_offset_height_tablet);
            btTablet = isNaN(btTablet) ? btDesk : btTablet;

            // Mobile cascade
            let bhMobile = parseFloat(config.layout.backup_height_mobile);
            bhMobile = isNaN(bhMobile) ? bhTablet : bhMobile;
            let btMobile = parseFloat(config.layout.backup_offset_height_mobile);
            btMobile = isNaN(btMobile) ? btTablet : btMobile;

            let finalBackupHMain = isMobile ? bhMobile : (isTablet ? bhTablet : bhDesk);
            let finalBackupHTop = isMobile ? btMobile : (isTablet ? btTablet : btDesk);

            if (finalBackupHMain > 0) {
                hMain = finalBackupHMain;
            }
            if (finalBackupHTop > 0) {
                hTop = finalBackupHTop;
            }
        }

        const totalHeight = hMain + hTop;

        // Update cache heights in state
        if (window.HoomaSH && window.HoomaSH.state) {
            window.HoomaSH.state.headerHeight = hMain;
            window.HoomaSH.state.topHeaderHeight = hTop;
        }

        // Always update the dynamic height (real-time state)
        if (totalHeight > 0) {
            document.documentElement.style.setProperty('--hoo-dynamic-header-height', totalHeight + 'px');
        }

        // Only update the BASE height (compensation/placeholder) if safe
        if (totalHeight > 0 && (!isShrunken || force)) {
            this.compensationHeight = totalHeight;
            document.documentElement.style.setProperty('--hoo-header-height', totalHeight + 'px');
        }
    },

    applyLayoutCompensation: function (force = false) {
        const config = window.HoomaSHConfig;
        const elements = window.HoomaSH.elements;

        if (!elements || !elements.header) {
            document.body.classList.remove('hoo-sh-compensation');
            const ph = document.getElementById('hoo-sh-placeholder');
            if (ph) ph.style.display = 'none';
            return;
        }

        // Perform calculation exactly once here
        this.calculateHeight(force);

        const hMain = (window.HoomaSH && window.HoomaSH.state && window.HoomaSH.state.headerHeight) || elements.header.offsetHeight;

        // Placeholder visibility
        if (config.layout.placeholder === '1') {
            const placeholder = document.getElementById('hoo-sh-placeholder');
            if (placeholder) {
                placeholder.setAttribute('data-hoo-active', 'true');
            }
            document.body.classList.add('hoo-sh-compensation');
        } else {
            const ph = document.getElementById('hoo-sh-placeholder');
            if (ph) {
                ph.removeAttribute('data-hoo-active');
            }
            if (!this.isForceFixedActive()) {
                document.body.classList.remove('hoo-sh-compensation');
            } else {
                document.body.classList.add('hoo-sh-compensation');
            }
        }
        
        document.body.classList.remove('hooma-sh-has-placeholder');

        // Divi page-container padding-top fix
        const pageContainer = document.getElementById('page-container');
        if (pageContainer) {
            if (document.body.classList.contains('et_transparent_nav')) {
                if (pageContainer.style.getPropertyValue('padding-top')) {
                    pageContainer.style.removeProperty('padding-top');
                }
            } else {
                const finalPadding = this.compensationHeight + 'px';
                if (pageContainer.style.getPropertyValue('padding-top') !== finalPadding) {
                    const computedPadding = window.getComputedStyle(pageContainer).paddingTop;
                    if (computedPadding !== finalPadding) {
                        pageContainer.style.setProperty('padding-top', finalPadding, 'important');
                    }
                }
            }
        }

        // Negative Margin (Pull Up)
        const targetSelector = config.layout.target;
        if (targetSelector) {
            const targetEl = document.querySelector(targetSelector);
            if (targetEl) {
                let shouldRun = config.run_pull_up && !this.shouldDisablePullUp(config);

                if (shouldRun) {
                    targetEl.setAttribute('data-hoo-pull-up', 'true');
                    document.documentElement.style.setProperty('--hoo-pull-up-mt', '-' + this.compensationHeight + 'px');
                } else {
                    targetEl.removeAttribute('data-hoo-pull-up');
                    document.documentElement.style.setProperty('--hoo-pull-up-mt', '0px');
                }
            }
        }

        // Update cache dynamically (deferred)
        this.initCacheBridge();
    },

    isForceFixedActive: function () {
        const config = window.HoomaSHConfig;
        const layout = config.layout || {};
        const mobile = config.mobile || {};

        if (layout.force_fixed_global === '1') {
            return true;
        }

        const width = window.innerWidth;
        const bpMobile = parseInt(mobile.breakpoint) || 767;
        const bpTablet = parseInt(mobile.tablet_breakpoint) || 980;

        const isMobile = width <= bpMobile;
        const isTablet = width > bpMobile && width <= bpTablet;
        const isDesktop = width > bpTablet;

        if (isMobile && layout.force_fixed_mobile === '1') return true;
        if (isTablet && layout.force_fixed_tablet === '1') return true;
        if (isDesktop && layout.force_fixed_desktop === '1') return true;

        return false;
    },

    shouldDisablePullUp: function (config) {
        const settings = config.layout || {};
        const mobileSettings = config.mobile || {};

        const bpMobile = parseInt(mobileSettings.breakpoint) || parseInt(mobileSettings.mobile_breakpoint) || 768;
        const bpTablet = parseInt(mobileSettings.tablet_breakpoint) || 1024;

        const width = window.innerWidth;

        let isMobile = width <= bpMobile;
        let isTablet = width > bpMobile && width <= bpTablet;
        let isDesktop = width > bpTablet;

        if (isMobile && settings.pull_up_disable_mobile === '1') return true;
        if (isTablet && settings.pull_up_disable_tablet === '1') return true;
        if (isDesktop && settings.pull_up_disable_desktop === '1') return true;

        return false;
    },

    applyForcedFixed: function () {
        const config = window.HoomaSHConfig;
        const elements = window.HoomaSH.elements;
        const layout = config.layout || {};
        const mobile = config.mobile || {};

        if (!elements.header) return;

        const width = window.innerWidth;
        const bpMobile = parseInt(mobile.breakpoint) || 767;
        const bpTablet = parseInt(mobile.tablet_breakpoint) || 980;

        const isMobile = width <= bpMobile;
        const isTablet = width > bpMobile && width <= bpTablet;
        const isDesktop = width > bpTablet;

        let shouldForce = false;

        if (layout.force_fixed_global === '1') {
            shouldForce = true;
        } else {
            if (isMobile && layout.force_fixed_mobile === '1') shouldForce = true;
            if (isTablet && layout.force_fixed_tablet === '1') shouldForce = true;
            if (isDesktop && layout.force_fixed_desktop === '1') shouldForce = true;
        }

        if (shouldForce) {
            elements.header.classList.add('hoo-force-fixed');
            document.body.classList.add('hooma-sh-is-forced-fixed');
        } else {
            elements.header.classList.remove('hoo-force-fixed');
            document.body.classList.remove('hooma-sh-is-forced-fixed');
        }
    },

    initCacheBridge: function () {
        if (document.body.classList.contains('wp-admin') || 
            document.body.classList.contains('et-fb') || 
            document.body.classList.contains('elementor-editor-active') ||
            document.body.classList.contains('vc_editor')) {
            return;
        }

        const config = window.HoomaSHConfig;
        const elements = window.HoomaSH.elements;

        if (config.layout.height_mode === 'auto' && elements.header) {
            const actualHeight = (window.HoomaSH && window.HoomaSH.state && window.HoomaSH.state.headerHeight) || elements.header.offsetHeight;
            if (actualHeight > 0) {
                // Determine active device/viewport responsively to set the correct cookie name
                const width = window.innerWidth;
                const mobile = config.mobile || {};
                const bpMobile = parseInt(mobile.breakpoint) || 767;
                const bpTablet = parseInt(mobile.tablet_breakpoint) || 980;

                const isMobile = width <= bpMobile;
                const isTablet = width > bpMobile && width <= bpTablet;

                const lastSaved = config.last_saved || '0';
                let cookieName = 'hsh_cached_h_desk_' + lastSaved;
                if (isMobile) {
                    cookieName = 'hsh_cached_h_mob_' + lastSaved;
                } else if (isTablet) {
                    cookieName = 'hsh_cached_h_tab_' + lastSaved;
                }

                const updateCookie = () => {
                    const match = document.cookie.match(new RegExp('(^| )' + cookieName + '=([^;]+)'));
                    const currentCookie = match ? parseInt(match[2]) : 0;

                    if (Math.abs(actualHeight - currentCookie) > 2) {
                        document.cookie = cookieName + "=" + actualHeight + ";path=/;max-age=31536000;SameSite=Lax";
                    }
                };

                // Defer cookie write to idle time to avoid layout/render blocking
                if (typeof window.requestIdleCallback === 'function') {
                    window.requestIdleCallback(() => updateCookie());
                } else {
                    setTimeout(updateCookie, 1000);
                }
            }
        }
    }
};
