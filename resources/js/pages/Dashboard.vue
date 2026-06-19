<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import { Head } from "@inertiajs/vue3";
import AppMain from "@/components/AppMain.vue";
import {
    Table,
    TableHeader,
    TableRow,
    TableHead,
    TableBody,
    TableCell,
} from "@/components/ui/table";
import { ShoppingBag, Wallet, Clock3, CheckCircle2 } from "lucide-vue-next";
import useFormatter from "@/composables/useFormatter";
import {
    Card,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";

const { currency } = useFormatter();

const props = defineProps({
    stats: Object,
    latest_orders: Array,
});

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
        <AppMain>
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <Card
                    class="@container/card transition-all hover:shadow-md hover:-translate-y-1"
                >
                    <CardHeader
                        class="flex flex-row items-center justify-between"
                    >
                        <div>
                            <CardDescription class="font-medium text-foreground"
                                >Pesanan Hari Ini</CardDescription
                            >
                            <CardTitle
                                class="text-2xl font-semibold tabular-nums @[250px]/card:text-3xl"
                            >
                                {{ props.stats.today_orders }}
                            </CardTitle>
                        </div>
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10"
                        >
                            <ShoppingBag class="size-6 text-primary" />
                        </div>
                    </CardHeader>
                    <CardFooter>
                        <p class="text-sm text-muted-foreground">
                            Total pesanan yang dibuat hari ini
                        </p>
                    </CardFooter>
                </Card>
                <Card
                    class="@container/card transition-all hover:shadow-md hover:-translate-y-1"
                >
                    <CardHeader
                        class="flex flex-row items-center justify-between"
                    >
                        <div>
                            <CardDescription class="font-medium text-foreground"
                                >Pendapatan Hari Ini</CardDescription
                            >
                            <CardTitle
                                class="text-2xl font-semibold tabular-nums @[250px]/card:text-3xl"
                            >
                                {{ currency(props.stats.today_revenue) }}
                            </CardTitle>
                        </div>

                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-green-500/10"
                        >
                            <Wallet class="size-6 text-green-500" />
                        </div>
                    </CardHeader>

                    <CardFooter>
                        <p class="text-sm text-muted-foreground">
                            Total pendapatan dari transaksi hari ini
                        </p>
                    </CardFooter>
                </Card>

                <Card
                    class="@container/card transition-all hover:shadow-md hover:-translate-y-1"
                >
                    <CardHeader
                        class="flex flex-row items-center justify-between"
                    >
                        <div>
                            <CardDescription class="font-medium text-foreground"
                                >Dalam Proses</CardDescription
                            >
                            <CardTitle
                                class="text-2xl font-semibold tabular-nums @[250px]/card:text-3xl"
                            >
                                {{ props.stats.processing_orders }}
                            </CardTitle>
                        </div>

                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-yellow-500/10"
                        >
                            <Clock3 class="size-6 text-yellow-500" />
                        </div>
                    </CardHeader>

                    <CardFooter>
                        <p class="text-sm text-muted-foreground">
                            Pesanan yang sedang dicuci atau diproses
                        </p>
                    </CardFooter>
                </Card>

                <Card
                    class="@container/card transition-all hover:shadow-md hover:-translate-y-1"
                >
                    <CardHeader
                        class="flex flex-row items-center justify-between"
                    >
                        <div>
                            <CardDescription class="font-medium text-foreground"
                                >Selesai Hari Ini</CardDescription
                            >
                            <CardTitle
                                class="text-2xl font-semibold tabular-nums @[250px]/card:text-3xl"
                            >
                                {{ props.stats.completed_today }}
                            </CardTitle>
                        </div>

                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-500/10"
                        >
                            <CheckCircle2 class="size-6 text-blue-500" />
                        </div>
                    </CardHeader>

                    <CardFooter>
                        <p class="text-sm text-muted-foreground">
                            Pesanan yang selesai hari ini
                        </p>
                    </CardFooter>
                </Card>
            </div>
            <div class="mt-6">
                <h3 class="font-semibold mb-3">Pesanan Terbaru</h3>
                <Table>
                    <TableHeader class="bg-muted/50">
                        <TableRow>
                            <TableHead class="w-10">No.</TableHead>
                            <TableHead>Invoice</TableHead>
                            <TableHead>Pelanggan</TableHead>
                            <TableHead>Total</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="props.latest_orders.length === 0">
                            <TableCell
                                :colspan="4"
                                class="text-center py-4 text-muted-foreground"
                            >
                                Tidak ada data
                            </TableCell>
                        </TableRow>
                        <TableRow
                            v-else
                            v-for="(item, index) in props.latest_orders"
                            :key="item.id"
                        >
                            <TableCell>{{ index + 1 }}</TableCell>
                            <TableCell>
                                {{ item.invoice_number }}
                            </TableCell>
                            <TableCell>
                                {{ item.customer?.name }}
                            </TableCell>
                            <TableCell>
                                {{ item.grand_total }}
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </AppMain>
    </AppLayout>
</template>
