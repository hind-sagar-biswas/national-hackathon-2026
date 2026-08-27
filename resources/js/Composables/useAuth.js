import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

/**
 * Composable for easy access to authenticated user properties, roles, and permissions.
 *
 * @returns {Object} { can, hasRole, user, roles, permissions, account, can, hasRole }
 */
export function useAuth() {
    const page = usePage()

    const user = computed(() => page.props.auth?.user || null)
    const account = computed(() => page.props.acc || [])
    const roles = computed(() => page.props.roles || [])
    const permissions = computed(() => page.props.permissions || [])

    const can = (permission) => permissions.value.includes(permission)
    const hasRole = (role) => roles.value.includes(role)

    return { user, roles, permissions, account, can, hasRole }
}