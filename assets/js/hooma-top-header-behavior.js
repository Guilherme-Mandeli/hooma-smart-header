// Hooma Smart Header - Top Header Behavior
export const topHeaderModule = {
    init: function () {
        const topHeader = document.querySelector('#top-header');
        const config = window.HoomaSHConfig;
        const headerSelector = (config && config.selectors && config.selectors.header) ? config.selectors.header : '#main-header';
        const header = document.querySelector(headerSelector);
        const body = document.body;

        if (topHeader && header) {
            topHeader.setAttribute('data-hoo-top-header', 'true');
            let layout = (config && config.layout) ? config.layout : {};
            let mobileSettings = config.mobile || {};
            let bpMobile = parseInt(mobileSettings.breakpoint) || parseInt(mobileSettings.mobile_breakpoint) || 768;
            let bpTablet = parseInt(mobileSettings.tablet_breakpoint) || 1024;

            let getBackupHTop = (width) => {
                let isMobile = width <= bpMobile;
                let isTablet = width > bpMobile && width <= bpTablet;

                let btDesk = parseFloat(layout.backup_offset_height);
                btDesk = isNaN(btDesk) ? 0 : btDesk;

                let btTablet = parseFloat(layout.backup_offset_height_tablet);
                btTablet = isNaN(btTablet) ? btDesk : btTablet;

                let btMobile = parseFloat(layout.backup_offset_height_mobile);
                btMobile = isNaN(btMobile) ? btTablet : btMobile;

                return isMobile ? btMobile : (isTablet ? btTablet : btDesk);
            };

            let initWidth = window.innerWidth;
            let backupHTop = getBackupHTop(initWidth);
            let initTime = Date.now();

            if (backupHTop > 0) {
                topHeader.style.maxHeight = backupHTop + 'px';
            }

            let topHeaderHeight = backupHTop > 0 ? backupHTop : topHeader.offsetHeight;

            const toggleTopHeader = () => {
                const isMobile = window.innerWidth <= 981;
                
                const state = window.HoomaSH && window.HoomaSH.state;
                let adminBarHeight = 0;
                
                if (state && typeof state.adminBarHeight === 'number') {
                    adminBarHeight = state.adminBarHeight;
                } else {
                    const adminBar = document.getElementById('wpadminbar');
                    adminBarHeight = adminBar ? adminBar.offsetHeight : 0;
                }
                
                if (state && typeof state.topHeaderHeight === 'number') {
                    topHeaderHeight = state.topHeaderHeight;
                } else {
                    topHeaderHeight = topHeader.offsetHeight;
                }

                let currentBackupHTop = getBackupHTop(window.innerWidth);

                // Force exact backup value during first 500ms to prevent jumping from partial DOM/CSS loads
                if (currentBackupHTop > 0 && Date.now() - initTime < 500) {
                    topHeaderHeight = currentBackupHTop;
                }

                // Calculamos el valor para ocultar o mostrar
                let value = -topHeaderHeight; 

                // Mostrar si estamos arriba o subiendo
                if (window.scrollY === 0 || body.classList.contains('hoo-is-scrolling-up')) {
                    value = 0; 
                }

                // Desktop → variables
                if (!isMobile) {
                    document.documentElement.style.setProperty('--hoo-top-header-mt', `${value}px`);
                    document.documentElement.style.setProperty('--hoo-main-header-mt', `${value}px`);
                    document.documentElement.style.setProperty('--hoo-header-top', `${adminBarHeight + topHeaderHeight}px`);
                } 
                // Mobile → variables
                else {
                    document.documentElement.style.setProperty('--hoo-top-header-mt', `${value}px`);
                    document.documentElement.style.setProperty('--hoo-main-header-mt', '0px');
                    
                    let mobileTop = value === 0 ? (adminBarHeight + topHeaderHeight) : (adminBarHeight - topHeaderHeight);
                    document.documentElement.style.setProperty('--hoo-header-top', `${mobileTop}px`);
                }
            };

            toggleTopHeader();
            window.addEventListener('resize', toggleTopHeader);
            
            // Re-validation after a delay to catch layout shifts (100% width fix)
            setTimeout(() => {
                toggleTopHeader();
            }, 500);

            setTimeout(() => {
                toggleTopHeader();
            }, 1500);
            
            // Expose for external calls if needed
            this.toggleTopHeader = toggleTopHeader;

            // Remove temporary max-height restriction after initialization
            if (backupHTop > 0) {
                setTimeout(() => {
                    topHeader.style.maxHeight = '';
                    document.documentElement.style.setProperty('--hoo-top-header-max-height', 'none');
                    document.documentElement.style.setProperty('--hoo-top-header-overflow', 'visible');
                    toggleTopHeader(); // Re-measure without restriction
                }, 500);
            }
        }
    }
};
