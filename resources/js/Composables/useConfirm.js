import { useForm } from "@inertiajs/vue3";

/**
 * Composable for password confirmed action
 * 
 * @param {string} route 
 * @param {Object} formFields 
 * @param {string} method 
 * @returns {Object} {form, submit}
 * 
 * @example
 * const { form, submit } = useConfirm(route('users.destroy', user.id), {
 *     price: '',
 * });
 */
export function useConfirm(route, formFields = {}, method = 'post') {
    const form = useForm({
        ...formFields,
        current_password: '',
    });

    const submit = () => {
        form[method](route, {
            preserveScroll: true,
            onError: () => {
                form.reset();
            },
        });
    };

    return {
        form,
        submit
    };
}
