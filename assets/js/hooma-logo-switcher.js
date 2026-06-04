// Hooma Smart Header - Logo Switcher Module

export const logoModule = {
    init: function () {
        const config = window.HoomaSHConfig;

        // 1. Check if enabled
        if (!config || !config.logo_switcher || config.logo_switcher.enabled !== '1') {
            return;
        }

        // 1.1 Check Display Conditions (Server-side flag)
        if (!config.run_logo_switcher) {
            return;
        }

        const settings = config.logo_switcher;
        const triggerSelector = settings.trigger_selector || 'header';
        const stateClass = settings.state_class ? settings.state_class.replace('.', '') : 'hoo-is-sticky';

        // 2. Find Elements
        const triggerEl = document.querySelector(triggerSelector);
        const logoSelector = config.selectors.logo || '.custom-logo, .site-logo img';
        const defaultLogoEl = document.querySelector(logoSelector);

        if (!triggerEl || !defaultLogoEl) {
            return;
        }

        // Capture original attributes for safe viewport-fallback
        if (!defaultLogoEl.hasAttribute('data-hsh-orig-src')) {
            defaultLogoEl.setAttribute('data-hsh-orig-src', defaultLogoEl.getAttribute('src') || '');
            defaultLogoEl.setAttribute('data-hsh-orig-srcset', defaultLogoEl.getAttribute('srcset') || '');
            defaultLogoEl.setAttribute('data-hsh-orig-sizes', defaultLogoEl.getAttribute('sizes') || '');
        }

        // Check if there is at least one alt logo defined in any breakpoint
        const hasAltLogo = settings.alt_logo || settings.alt_logo_tablet || settings.alt_logo_mobile;
        if (!hasAltLogo) {
            return;
        }

        // 3. Setup Structure for No CLS
        let wrapper = defaultLogoEl.closest('.hoo-logo-wrapper');
        if (!wrapper) {
            wrapper = document.createElement('div');
            wrapper.className = 'hoo-logo-wrapper';
            wrapper.style.position = 'relative';
            wrapper.style.display = 'inline-block';

            defaultLogoEl.parentNode.insertBefore(wrapper, defaultLogoEl);
            wrapper.appendChild(defaultLogoEl);
        }

        // Apply Max Width if defined
        const maxWidth = settings.max_width;
        if (maxWidth && parseFloat(maxWidth) > 0) {
            wrapper.style.maxWidth = maxWidth + 'px';
            defaultLogoEl.style.maxWidth = '100%';
            defaultLogoEl.style.height = 'auto';
        }

        // Create Alt Logo Image if it doesn't exist
        let altLogoEl = wrapper.querySelector('.hoo-logo-alt');
        if (!altLogoEl) {
            altLogoEl = document.createElement('img');
            altLogoEl.className = 'hoo-logo-alt';
            
            // Resolve initial alt logo URL to put as first src
            const { alt: resolvedAlt } = this.resolveLogos(config);
            altLogoEl.src = resolvedAlt || '';
            
            altLogoEl.alt = defaultLogoEl.alt ? defaultLogoEl.alt + ' (Alt)' : 'Logo (Alt)';
            altLogoEl.style.position = 'absolute';
            altLogoEl.style.top = '0';
            altLogoEl.style.left = '0';
            altLogoEl.style.width = '100%';
            altLogoEl.style.height = '100%';
            altLogoEl.style.objectFit = 'contain';
            altLogoEl.style.opacity = '0';
            altLogoEl.style.transition = 'opacity 0.4s ease';
            altLogoEl.style.zIndex = '2';

            defaultLogoEl.classList.add('hoo-logo-default');
            defaultLogoEl.style.transition = 'opacity 0.4s ease';
            defaultLogoEl.style.zIndex = '1';
            defaultLogoEl.style.position = 'relative';
            defaultLogoEl.style.setProperty('opacity', '1', 'important');

            wrapper.appendChild(altLogoEl);
        }

        // 4. Initial Check
        this.checkState(triggerEl, stateClass, wrapper);

        // 5. MutationObserver for Class Changes
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    this.checkState(triggerEl, stateClass, wrapper);
                }
            });
        });

        observer.observe(triggerEl, {
            attributes: true,
            attributeFilter: ['class']
        });

        // 6. Resize Event Listener for Responsive Updates
        window.addEventListener('resize', () => {
            this.checkState(triggerEl, stateClass, wrapper);
        });
    },

    resolveLogos: function (config) {
        const settings = config.logo_switcher || {};
        const mobileSettings = config.mobile || {};

        const bpMobile = parseInt(mobileSettings.breakpoint) || parseInt(mobileSettings.mobile_breakpoint) || 768;
        const bpTablet = parseInt(mobileSettings.tablet_breakpoint) || 1024;
        const width = window.innerWidth;

        let isMobile = width <= bpMobile;
        let isTablet = width > bpMobile && width <= bpTablet;

        let initial = '';
        let alt = '';

        if (isMobile) {
            initial = settings.initial_logo_mobile || settings.initial_logo_tablet || settings.initial_logo;
            alt = settings.alt_logo_mobile || settings.alt_logo_tablet || settings.alt_logo;
        } else if (isTablet) {
            initial = settings.initial_logo_tablet || settings.initial_logo;
            alt = settings.alt_logo_tablet || settings.alt_logo;
        } else {
            initial = settings.initial_logo;
            alt = settings.alt_logo;
        }

        return { initial, alt };
    },

    shouldDisableOnDevice: function (config) {
        const settings = config.logo_switcher;
        const mobileSettings = config.mobile || {};

        const bpMobile = parseInt(mobileSettings.breakpoint) || parseInt(mobileSettings.mobile_breakpoint) || 768;
        const bpTablet = parseInt(mobileSettings.tablet_breakpoint) || 1024;

        const width = window.innerWidth;

        let isMobile = width <= bpMobile;
        let isTablet = width > bpMobile && width <= bpTablet;
        let isDesktop = width > bpTablet;

        if (isMobile && settings.disable_mobile === '1') return true;
        if (isTablet && settings.disable_tablet === '1') return true;
        if (isDesktop && settings.disable_desktop === '1') return true;

        return false;
    },

    checkState: function (trigger, stateClass, wrapper) {
        const config = window.HoomaSHConfig;
        const alt = wrapper.querySelector('.hoo-logo-alt');
        const def = wrapper.querySelector('.hoo-logo-default');

        if (!def) return;

        const isDisabled = this.shouldDisableOnDevice(config);
        const { initial: resolvedInit, alt: resolvedAlt } = this.resolveLogos(config);

        // Fallback to original src if resolved is empty
        const originalSrc = def.getAttribute('data-hsh-orig-src');
        const finalInitSrc = resolvedInit || originalSrc;

        // Update default logo source if it has changed
        if (finalInitSrc && def.getAttribute('src') !== finalInitSrc) {
            def.src = finalInitSrc;
            def.removeAttribute('srcset');
            def.removeAttribute('sizes');
        }

        // Update alt logo source if it has changed
        if (resolvedAlt) {
            if (alt && alt.getAttribute('src') !== resolvedAlt) {
                alt.src = resolvedAlt;
                alt.removeAttribute('srcset');
                alt.removeAttribute('sizes');
            }
        }

        // Determine if we should show alt logo
        const isActive = trigger.classList.contains(stateClass);
        const shouldShowAlt = isActive && !isDisabled && resolvedAlt;

        if (shouldShowAlt) {
            wrapper.classList.add('hoo-show-alt');
            if (alt) alt.style.setProperty('opacity', '1', 'important');
            def.style.setProperty('opacity', '0', 'important');
        } else {
            wrapper.classList.remove('hoo-show-alt');
            if (alt) alt.style.setProperty('opacity', '0', 'important');
            def.style.setProperty('opacity', '1', 'important');
        }
    }
};
