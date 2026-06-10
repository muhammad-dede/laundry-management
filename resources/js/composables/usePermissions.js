import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

export default function usePermissions() {
    const page = usePage();

    const permissions = computed(
        () => page.props.auth?.user?.permissions || []
    );
    const roles = computed(() => page.props.auth?.user?.roles || []);

    const can = (...list) => {
        return list.some((p) => permissions.value.includes(p));
    };

    const hasRole = (...targetRoles) => {
        return targetRoles.some((r) => roles.value.includes(r));
    };

    return {
        permissions,
        roles,
        can,
        hasRole,
    };
}
