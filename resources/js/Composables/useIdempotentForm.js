import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

/**
 * Idempotent form helper for Laravel Inertia.js applications.
 * Automatically generates and manages idempotency keys for retry-safe mutations.
 * 
 * @param {Object} initialData - Initial form data
 * @returns {Object} { form, idempotencyKey, resetKey, submit }
 * 
 * @example
 * const { form, idempotencyKey, resetKey, submit } = useIdempotentForm({
 *   name: '',
 *   email: '',
 * });
 * 
 * // Use custom submit with callbacks
 * submit('post', users.store(), {
 *   onSuccess: (page) => {
 *     form.reset();
 *     router.visit(page.props.url);
 *   },
 * });
 */
export function useIdempotentForm(initialData = {}) {
    const idempotencyKey = ref(crypto.randomUUID());

    const form = useForm({
        ...initialData,
        idempotency_key: idempotencyKey.value,
    });

    const resetKey = () => {
        idempotencyKey.value = crypto.randomUUID();
        form.idempotency_key = idempotencyKey.value;
    };

    const submit = (method, url, options = {}) => {
        const existingOnSuccess = options.onSuccess;
        const existingOnError = options.onError;

        // Merge idempotency key into headers and payload
        options.headers = {
            ...options.headers,
            'X-Idempotency-Key': idempotencyKey.value,
        };

        options.onSuccess = (page) => {
            // 1. Transaction succeeded: generate a fresh key for future operations
            resetKey();
            if (existingOnSuccess) {
                existingOnSuccess(page);
            }
        };

        options.onError = (errors) => {
            // 2. Retain existing key on error or network drops to guard retries
            if (existingOnError) {
                existingOnError(errors);
            }
        };

        form.submit(method, url, options);
    };

    return {
        form,
        idempotencyKey,
        resetKey,
        submit,
    };
}