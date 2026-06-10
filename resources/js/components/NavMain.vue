<script setup>
import {
    SidebarContent,
    SidebarGroup,
    SidebarGroupContent,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
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
</script>

<template>
    <SidebarContent>
        <SidebarGroup>
            <SidebarGroupContent>
                <SidebarMenu>
                    <SidebarMenuItem v-for="menu in menus" :key="menu.title">
                        <!-- MENU DENGAN SUBMENU -->
                        <template v-if="menu.subMenus">
                            <Collapsible
                                :default-open="menu.isActive"
                                class="group/collapsible"
                            >
                                <CollapsibleTrigger as-child>
                                    <SidebarMenuButton
                                        :tooltip="menu.title"
                                        :is-active="menu.isActive"
                                        class="cursor-pointer"
                                    >
                                        <component
                                            :is="menu.icon"
                                            v-if="menu.icon"
                                        />

                                        <span>
                                            {{ menu.title }}
                                        </span>

                                        <ChevronDown
                                            class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-180"
                                        />
                                    </SidebarMenuButton>
                                </CollapsibleTrigger>

                                <CollapsibleContent>
                                    <SidebarMenuSub>
                                        <SidebarMenuSubItem
                                            v-for="subMenu in menu.subMenus"
                                            :key="subMenu.title"
                                        >
                                            <SidebarMenuSubButton
                                                :as="Link"
                                                :href="subMenu.href"
                                                :is-active="
                                                    isRouteActive(
                                                        subMenu.routeMatch,
                                                    )
                                                "
                                            >
                                                <span>
                                                    {{ subMenu.title }}
                                                </span>
                                            </SidebarMenuSubButton>
                                        </SidebarMenuSubItem>
                                    </SidebarMenuSub>
                                </CollapsibleContent>
                            </Collapsible>
                        </template>

                        <!-- MENU BIASA -->
                        <template v-else>
                            <SidebarMenuButton
                                :as="Link"
                                :href="menu.href"
                                :tooltip="menu.title"
                                :is-active="menu.isActive"
                            >
                                <component :is="menu.icon" v-if="menu.icon" />

                                <span>
                                    {{ menu.title }}
                                </span>
                            </SidebarMenuButton>
                        </template>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroupContent>
        </SidebarGroup>
    </SidebarContent>
</template>
