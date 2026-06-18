<script setup>
import {
    Sidebar,
    SidebarContent,
    SidebarGroup,
    SidebarGroupContent,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarRail,
} from "@/components/ui/sidebar";

import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from "@/components/ui/collapsible";

import { ChevronDown } from "lucide-vue-next";
import { Link } from "@inertiajs/vue3";
import { computed } from "vue";
import usePermissions from "@/composables/usePermissions";

const props = defineProps({
    items: {
        type: Array,
        default: () => [],
    },
});

const { can } = usePermissions();

function isRouteActive(routeMatch) {
    return routeMatch ? route().current(routeMatch) : false;
}

function hasPermission(permission) {
    return permission ? can(permission) : true;
}

const menus = computed(() => {
    return props.items
        .map((menu) => {
            if (menu.subMenus) {
                const subMenus = menu.subMenus.filter((subMenu) =>
                    hasPermission(subMenu.permission),
                );

                return {
                    ...menu,
                    subMenus,
                    isActive: subMenus.some((subMenu) =>
                        isRouteActive(subMenu.routeMatch),
                    ),
                };
            }

            return {
                ...menu,
                isActive: isRouteActive(menu.routeMatch),
            };
        })
        .filter((menu) => {
            if (menu.subMenus) {
                return menu.subMenus.length > 0;
            }

            return hasPermission(menu.permission);
        });
});

const groups = computed(() => {
    return props.items
        .map((group) => {
            const items = group.items.filter((item) =>
                item.permission ? can(item.permission) : true,
            );

            return {
                ...group,
                items,
                isActive: items.some((item) =>
                    route().current(item.routeMatch),
                ),
            };
        })
        .filter((group) => group.items.length > 0);
});
</script>

<template>
    <SidebarContent class="gap-0">
        <Collapsible
            v-for="group in groups"
            :key="group.title"
            default-open
            class="group/collapsible"
        >
            <SidebarGroup>
                <SidebarGroupLabel as-child class="cursor-pointer">
                    <CollapsibleTrigger>
                        {{ group.title }}

                        <ChevronDown
                            class="ml-auto transition-transform group-data-[state=open]/collapsible:rotate-180"
                        />
                    </CollapsibleTrigger>
                </SidebarGroupLabel>

                <CollapsibleContent>
                    <SidebarGroupContent>
                        <SidebarMenu>
                            <SidebarMenuItem
                                v-for="item in group.items"
                                :key="item.title"
                            >
                                <SidebarMenuButton
                                    :as="Link"
                                    :href="item.href"
                                    :is-active="
                                        route().current(item.routeMatch)
                                    "
                                >
                                    <component
                                        v-if="item.icon"
                                        :is="item.icon"
                                    />

                                    <span>
                                        {{ item.title }}
                                    </span>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                        </SidebarMenu>
                    </SidebarGroupContent>
                </CollapsibleContent>
            </SidebarGroup>
        </Collapsible>
    </SidebarContent>
</template>
