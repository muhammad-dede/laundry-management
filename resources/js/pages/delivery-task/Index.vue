<script setup>
import { Head, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import { debounce } from "lodash";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import AppLayout from "@/layouts/AppLayout.vue";
import AppMain from "@/components/AppMain.vue";
import SearchBox from "@/components/SearchBox.vue";
import Pagination from "@/components/Pagination.vue";
import ButtonShow from "@/components/ButtonShow.vue";
import usePermissions from "@/composables/usePermissions";
import useFormatter from "@/composables/useFormatter";
import useStatusBadge from "@/composables/useStatusBadge";
import { Badge } from "@/components/ui/badge";

const { can } = usePermissions();
const { date, time } = useFormatter();
const { deliveryStatusBadge } = useStatusBadge();

const props = defineProps({
    filters: Object,
    data: Object,
});

const perPage = ref(Number(props.filters.per_page) || 10);
const search = ref(props.filters.search || null);

watch([perPage], updateData);
watch(search, debounce(updateData, 500));

function updateData() {
    const query = {
        ...(search.value ? { search: search.value } : {}),
        ...(perPage.value && perPage.value !== 5
            ? { per_page: perPage.value }
            : {}),
    };
    router.get(route("delivery-task.index"), query, {
        preserveState: true,
        replace: true,
    });
}

const breadcrumbs = [
    { title: "Pengiriman Laundry", href: route("delivery-task.index") },
];
</script>

<template>
    <Head title="Pengiriman Laundry" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <AppMain>
            <div
                class="flex flex-col md:flex-row md:justify-between md:items-center gap-4"
            >
                <h2 class="text-lg md:text-xl font-bold">Pengiriman Laundry</h2>
                <div class="flex items-center gap-2">
                    <SearchBox v-model="search" />
                </div>
            </div>
            <Table>
                <TableHeader class="bg-muted/50">
                    <TableRow>
                        <TableHead class="w-10">No.</TableHead>
                        <TableHead>Pelanggan</TableHead>
                        <TableHead>Telepon</TableHead>
                        <TableHead>Alamat</TableHead>
                        <TableHead>Jadwal</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead class="text-right">Action</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="props.data.data.length === 0">
                        <TableCell
                            :colspan="6"
                            class="text-center py-4 text-muted-foreground"
                        >
                            Tidak ada data
                        </TableCell>
                    </TableRow>
                    <TableRow
                        v-else
                        v-for="(item, index) in props.data.data"
                        :key="item.id"
                    >
                        <TableCell>{{ index + 1 }}</TableCell>
                        <TableCell>{{ item.customer?.name ?? "-" }}</TableCell>
                        <TableCell>{{ item.customer?.phone ?? "-" }}</TableCell>
                        <TableCell>{{
                            item.customer?.address ?? "-"
                        }}</TableCell>
                        <TableCell>
                            {{ date(item.scheduled_at) ?? "-" }} .
                            {{ time(item.scheduled_at) ?? "-" }}
                        </TableCell>
                        <TableCell>
                            <Badge
                                :class="
                                    deliveryStatusBadge(item.delivery_status)
                                        .class
                                "
                            >
                                {{
                                    deliveryStatusBadge(item.delivery_status)
                                        .label
                                }}
                            </Badge>
                        </TableCell>
                        <TableCell class="text-right space-x-2">
                            <ButtonShow
                                v-if="can('courier-task.update-status')"
                                :href="route('delivery-task.show', item.id)"
                            />
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
            <Pagination v-model="perPage" :pagination="props.data" />
        </AppMain>
    </AppLayout>
</template>
