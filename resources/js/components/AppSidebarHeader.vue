<script setup>
import Breadcrumbs from "@/components/Breadcrumbs.vue";
import { SidebarTrigger } from "@/components/ui/sidebar";

const props = defineProps({
    breadcrumbs: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="props.breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="props.breadcrumbs" />
            </template>
        </div>
    </header>
</template>
