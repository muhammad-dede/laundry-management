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
    BanknoteArrowUp,
    BanknoteArrowDown,
    ChartColumn,
    TrendingDown,
    TrendingUp,
    Truck,
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
                icon: Truck,
                title: "Pengambilan",
                href: route("pickup.index"),
                routeMatch: "pickup.*",
                permission: "pickup.view",
            },
            {
                icon: ShoppingCart,
                title: "Pesanan",
                href: route("order.index"),
                routeMatch: "order.*",
                permission: "order.view",
            },
            {
                icon: BanknoteArrowDown,
                title: "Pengeluaran",
                href: route("expense.index"),
                routeMatch: "expense.*",
                permission: "expense.view",
            },
            {
                icon: BanknoteArrowUp,
                title: "Pemasukan Lain",
                href: route("income.index"),
                routeMatch: "income.*",
                permission: "income.view",
            },
        ],
    },
    {
        title: "Kurir",
        items: [
            {
                icon: Truck,
                title: "Pengambilan Saya",
                href: route("pickup-task.index"),
                routeMatch: "pickup-task.*",
                permission: "courier-task.view",
            },
        ],
    },
    {
        title: "Laporan",
        items: [
            {
                icon: ChartColumn,
                title: "Laporan Pesanan",
                href: route("report.order.index"),
                routeMatch: "report.order.*",
                permission: "report.order.view",
            },
            {
                icon: TrendingDown,
                title: "Laporan Pengeluaran",
                href: route("report.expense.index"),
                routeMatch: "report.expense.*",
                permission: "report.expense.view",
            },
            {
                icon: TrendingUp,
                title: "Laporan Pemasukan",
                href: route("report.income.index"),
                routeMatch: "report.income.*",
                permission: "report.income.view",
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
