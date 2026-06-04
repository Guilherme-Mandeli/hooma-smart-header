// Hooma Smart Header - Initial Behavior (Phase 1)
import { isInitialBehaviorActive, getThreshold } from './hooma-helpers.js';

export const initialModule = {
    init: function () {
        this.checkInitialState();
    },

    checkInitialState: function () {
        const config = window.HoomaSHConfig;
        const state = window.HoomaSH.state;
        const elements = window.HoomaSH.elements;

        if (!isInitialBehaviorActive()) {
            if (elements.body) elements.body.classList.remove('hooma-sh-pre-init');
            elements.header.classList.add('hoo-is-visible');
            elements.header.classList.remove('hoo-is-hidden');
            state.hasShown = false;
            return;
        }

        // Defensive: show_once support
        if (config.behavior.show_once === '1' && state.hasShown) {
            elements.header.classList.add('hoo-is-visible');
            elements.header.classList.remove('hoo-is-hidden');
            state.isHidden = false;
            if (elements.body) elements.body.classList.remove('hooma-sh-pre-init');
            return;
        }

        const initScroll = window.scrollY;
        const initThreshold = getThreshold();

        // Logic 3.1: Initial "Hide until Scroll" Behavior (Behavior Tab: hide_on_scroll)
        if (config.behavior.hide_on_scroll === '1') {
            if (initScroll >= initThreshold) {
                // Phase 2 Territory
                elements.header.classList.add('hoo-is-visible');
                elements.header.classList.remove('hoo-is-hidden');
                state.isHidden = false;
                state.hasShown = true;
            } else {
                // Phase 1: Below threshold
                elements.header.classList.add('hoo-is-hidden');
                elements.header.classList.remove('hoo-is-visible');
                state.isHidden = true;
            }
        } else {
            // Not hiding initially
            elements.header.classList.add('hoo-is-visible');
            elements.header.classList.remove('hoo-is-hidden');
        }
        if (elements.body) elements.body.classList.remove('hooma-sh-pre-init');
    },

    // Helper to check if we are still in phase 1 (should hide?)
    // Returns true if handled (hidden), false if we should proceed to Phase 2
    handleScrollPhase1: function (currentScrollY) {
        const config = window.HoomaSHConfig;
        const state = window.HoomaSH.state;
        const elements = window.HoomaSH.elements;

        if (config.behavior.hide_on_scroll === '1' && currentScrollY < getThreshold()) {
            if (config.behavior.show_once === '1' && state.hasShown) {
                return false; 
            } else {
                if (!state.isHidden) {
                    elements.header.classList.add('hoo-is-hidden');
                    elements.header.classList.remove('hoo-is-visible');
                    state.isHidden = true;
                }
                return true; // Handled, stay in Phase 1
            }
        }
        return false; // Not Phase 1 condition
    }
};
