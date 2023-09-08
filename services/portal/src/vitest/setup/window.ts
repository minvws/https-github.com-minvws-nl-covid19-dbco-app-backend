import { vi } from 'vitest';

// ---- Start window.location mock ----
const oldWindowLocation = window.location;

delete (window as any).location;

window.location = Object.defineProperties(
    // start with an empty object on which to define properties
    {},
    {
        // grab all of the property descriptors for the
        // `jsdom` `Location` object
        ...Object.getOwnPropertyDescriptors(oldWindowLocation),

        // overwrite a mocked method for `window.location.assign`
        assign: {
            configurable: true,
            value: vi.fn(),
        },

        replace: {
            value: vi.fn(),
        },

        // more mocked methods here as needed
    }
) as any;
// ---- End window.location mock ----

// Mock env vars
window.config = {
    version: 'latest',
    intake: {
        match_case_enabled: true,
    },
    misc: {
        admin_view_enabled: true,
        planner_caselists_stats_0_enabled: true,
        case_metrics_enabled: true,
        hpzone_operational: true,
        covid_case_age_filter_enabled: true,
        place_visited_tab_enabled: true,
        add_case_button_distributor_enabled: true,
        add_case_button_user_enabled: true,
    },
    outsourcing: {
        outsourcing_to_regional_ggd_enabled: true,
    },
};
