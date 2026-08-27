import { ref, computed } from 'vue';

/**
 * Composable for managing async operations with loading and error states.
 * Ideal for API calls, data fetching, and async tasks.
 *
 * @param {Function} asyncFn - Async function/promise to execute
 * @returns {Object} { data, loading, error, execute, reset }
 *
 * @example
 * const { data, loading, error, execute } = useAsync(async () => {
 *   const response = await fetch('/api/users');
 *   return response.json();
 * });
 *
 * await execute();
 * if (error.value) console.error(error.value);
 */
export function useAsync(asyncFn) {
    const data = ref(null);
    const loading = ref(false);
    const error = ref(null);

    /**
     * Execute the async function.
     * @returns {Promise} Resolves with the result data
     */
    const execute = async () => {
        loading.value = true;
        error.value = null;

        try {
            data.value = await asyncFn();
            return data.value;
        } catch (err) {
            error.value = err;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Reset all state.
     */
    const reset = () => {
        data.value = null;
        loading.value = false;
        error.value = null;
    };

    return {
        data,
        loading,
        error,
        execute,
        reset,
    };
}
