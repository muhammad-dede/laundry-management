<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import { Head } from "@inertiajs/vue3";

const breadcrumbs = [
    {
        title: "Dashboard",
        href: "/dashboard",
    },
];
</script>

<template>
    <Head title="Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <h1>Dashboard</h1>
        </div>
    </AppLayout>
</template>
