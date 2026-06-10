<script setup>
import NavMain from "@/components/NavMain.vue";
import NavUser from "@/components/NavUser.vue";
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from "@/components/ui/sidebar";
import { Link } from "@inertiajs/vue3";
import { LayoutGrid, Database } from "lucide-vue-next";
import AppLogo from "./AppLogo.vue";

const mainNavItems = [
    {
        icon: LayoutGrid,
        title: "Dashboard",
        href: route("dashboard"),
        routeMatch: "dashboard",
        permission: "dashboard.view",
    },
    {
        icon: Database,
        title: "Master Data",
        subMenus: [
            {
                title: "Pengguna",
                href: route("users.index"),
                routeMatch: "users.*",
                permission: "users.view-any",
            },
        ],
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
