// Hooma Smart Header - Helpers

// Helper: Device Active Check
export const isDeviceActive = (mobileConfig) => {
    const config = window.HoomaSHConfig;
    if (!config) return true;

    const mConfig = mobileConfig || config.mobile;
    if (!mConfig) return true;

    const width = window.innerWidth;
    const isMobileRange = width <= config.mobile.breakpoint;
    const isTabletRange = width > config.mobile.breakpoint && width <= config.mobile.tablet_breakpoint;
    const isDesktopRange = width > config.mobile.tablet_breakpoint;

    // Strict String '1' checks
    if (isMobileRange && mConfig.disable_mobile === '1') return false;
    if (isTabletRange && mConfig.disable_tablet === '1') return false;
    if (isDesktopRange && mConfig.disable_desktop === '1') return false;

    return true;
};

// Helper: Initial Behavior Active Check
export const isInitialBehaviorActive = () => {
    const config = window.HoomaSHConfig;
    if (!config) return true;

    // 1. PHP Condition Check (Display Conditions)
    if (config.run_behavior !== undefined && !config.run_behavior) {
        return false;
    }
    // 2. Device Check (using behavior mobile config)
    const mobileConfig = config.behavior.mobile || {};
    return isDeviceActive(mobileConfig);
};

// Helper: Scroll Behavior Active Check
export const isScrollBehaviorActive = () => {
    const config = window.HoomaSHConfig;
    if (!config) return false;

    // Check Enabled
    if (!config.scroll_behavior || config.scroll_behavior.enabled !== '1') return false;

    // Check Display Conditions (passed from PHP)
    if (config.run_scroll_behavior !== undefined && !config.run_scroll_behavior) {
        return false;
    }

    // Check Device Settings (overrides)
    const mobileConfig = config.scroll_behavior.mobile || {};
    return isDeviceActive(mobileConfig);
};

// Helper: Get Threshold
export const getThreshold = () => {
    const config = window.HoomaSHConfig;
    if (!config) return 0;

    let threshold = parseInt(config.behavior.scroll_min);
    if (isNaN(threshold)) threshold = 0;

    if (config.behavior.trigger_type === 'element' && config.behavior.trigger_selector) {
        const el = document.querySelector(config.behavior.trigger_selector);
        if (el) {
            const rect = el.getBoundingClientRect();
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            threshold = rect.top + scrollTop;
        }
    }
    return threshold;
};
// Helper: Get Correct Top Offset (Admin Bar + Top Header)
export const getCorrectTop = () => {
    const state = window.HoomaSH && window.HoomaSH.state;
    if (state && typeof state.adminBarHeight === 'number' && typeof state.topHeaderHeight === 'number') {
        return state.adminBarHeight + state.topHeaderHeight;
    }

    let top = 0;
    const adminBar = document.getElementById('wpadminbar');
    const topHeader = document.getElementById('top-header');
    
    if (adminBar) {
        top += adminBar.offsetHeight;
    }
    
    if (topHeader) {
        top += topHeader.offsetHeight;
    }
    
    return top;
};
