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
import {
    LayoutGrid,
    User,
    Users,
    Shirt,
    ShoppingCart,
    CircleArrowDown,
    CircleArrowOutUpRight,
} from "lucide-vue-next";
import AppLogo from "./AppLogo.vue";

const mainNavItems = [
    {
        title: "Menu Utama",
        items: [
            {
                icon: LayoutGrid,
                title: "Dashboard",
                href: route("dashboard"),
                routeMatch: "dashboard",
                permission: "dashboard.view",
            },
        ],
    },
    {
        title: "Master Data",
        items: [
            {
                icon: User,
                title: "Pengguna",
                href: route("user.index"),
                routeMatch: "user.*",
                permission: "user.view",
            },
            {
                icon: Shirt,
                title: "Layanan",
                href: route("service.index"),
                routeMatch: "service.*",
                permission: "service.view",
            },
            {
                icon: Users,
                title: "Pelanggan",
                href: route("customer.index"),
                routeMatch: "customer.*",
                permission: "customer.view",
            },
        ],
    },
    {
        title: "Transaksi",
        items: [
            {
                icon: ShoppingCart,
                title: "Pesanan",
                href: route("order.index"),
                routeMatch: "order.*",
                permission: "order.view",
            },
            {
                icon: CircleArrowOutUpRight,
                title: "Pengeluaran",
                href: route("expense.index"),
                routeMatch: "expense.*",
                permission: "expense.view",
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
