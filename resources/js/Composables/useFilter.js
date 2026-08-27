import { reactive, watch } from 'vue';
import { router } from '@inertiajs/vue3'
import { debounce, throttle } from 'lodash-es';

/**
 * Composable for managing filter state with URL synchronization.
 * Automatically handles debouncing, throttling, and dependent filters.
 *
 * @param {string} routeName - The route name to navigate to (e.g., 'questions.index')
 * @param {Object} initialFilters - Initial filter values from props
 * @param {Object} options - Configuration options
 * @param {Object} options.debounceMs - Debounce delays by field name: { search: 500 }
 * @param {Object} options.throttleMs - Throttle delays by field name: { fieldName: 300 }
 * @param {Object} options.dependents - Cascade clearing: { standardId: ['chapterId'] }
 * @param {boolean} options.preserveState - Keep component state (default: true)
 * @param {boolean} options.replace - Replace history entry (default: true)
 * @returns {Object} { filters, reload, reset }
 *
 * @example
 * const { filters, reload, reset } = useFilter(
 *   route('questions.index'),
 *   {
 *     search: props.filters.search,
 *     standard: props.filters.standard,
 *     chapter: props.filters.chapter,
 *   },
 *   {
 *     debounceMs: { search: 500 },
 *     dependents: { standard: ['chapter'] },
 *   }
 * );
 */
export function useFilter(routeName, initialFilters = {}, options = {}) {
    const {
        debounceMs = {},
        throttleMs = {},
        dependents = {},
        preserveState = true,
        replace = true,
    } = options;

    const filters = reactive({ ...initialFilters });

    /**
     * Build query object with only non-empty filter values.
     */
    const buildQuery = () => {
        return Object.fromEntries(
            Object.entries(filters).filter(([_, value]) => value !== null && value !== '')
        );
    };

    /**
     * Reload the current page.
     */
    const reload = () => {
        router.get(routeName, buildQuery(), {
            preserveState,
            replace,
        });
    };

    /**
     * Reset all filters to empty strings.
     */
    const reset = () => {
        Object.keys(filters).forEach(key => {
            filters[key] = '';
        });
        reload();
    };

    // 2. Setup individual watchers for each property in the reactive object
    Object.keys(filters).forEach((key) => {
        const callback = (newValue) => {
            // Cascade clear dependents
            if (dependents[key] && newValue !== '') {
                dependents[key].forEach((dep) => {
                    if (filters.hasOwnProperty(dep)) {
                        filters[dep] = '';
                    }
                });
            }
            reload();
        };

        // Determine timing strategy
        let finalCallback = callback;
        if (throttleMs[key]) {
            finalCallback = throttle(callback, throttleMs[key]);
        } else if (debounceMs[key]) {
            finalCallback = debounce(callback, debounceMs[key]);
        }

        // Watch the specific property of the reactive object
        watch(() => filters[key], finalCallback);
    });

    return {
        filters,
        reload,
        reset,
    };
}