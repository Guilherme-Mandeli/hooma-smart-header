// Hooma Smart Header - Scroll Behavior (Phase 2)
import { isScrollBehaviorActive } from './hooma-helpers.js';

export const scrollModule = {
    handleScrollPhase2: function (delta, scrollingDown) {
        const config = window.HoomaSHConfig;
        const state = window.HoomaSH.state;
        const elements = window.HoomaSH.elements;

        if (isScrollBehaviorActive()) {
            let shouldBeHidden = false; // default maintain state
            let stateChanged = false; // flag to trigger change

            // Sensitivity
            const sensConfig = (config.scroll_behavior && config.scroll_behavior.sensitivity) ? config.scroll_behavior.sensitivity : config.mobile.sensitivity;
            const sensitivity = sensConfig ? parseInt(sensConfig) : 5;

            if (Math.abs(delta) > sensitivity) {
                if (scrollingDown) {
                    shouldBeHidden = true;
                    stateChanged = true;
                } else if (!scrollingDown) { // Up
                    shouldBeHidden = false;
                    stateChanged = true;
                }
            }

            if (stateChanged) {
                if (shouldBeHidden) {
                    if (!state.isHidden) {
                        elements.header.classList.add('hoo-is-hidden');
                        elements.header.classList.remove('hoo-is-visible');
                        state.isHidden = true;
                    }
                } else {
                    if (state.isHidden) {
                        elements.header.classList.remove('hoo-is-hidden');
                        elements.header.classList.add('hoo-is-visible');
                        state.isHidden = false;
                    }
                }
            }

            if (!state.isHidden) state.hasShown = true;

        } else {
            // If Scroll Behavior disabled but we passed Phase 1:
            // Ensure visible (Standard behavior fallback)
            if (state.isHidden) {
                elements.header.classList.remove('hoo-is-hidden');
                elements.header.classList.add('hoo-is-visible');
                state.isHidden = false;
            }
            state.hasShown = true;
        }
    }
};
