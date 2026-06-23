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
import ButtonCreate from "@/components/ButtonCreate.vue";
import ButtonEdit from "@/components/ButtonEdit.vue";
import ButtonDelete from "@/components/ButtonDelete.vue";
import ButtonShow from "@/components/ButtonShow.vue";
import usePermissions from "@/composables/usePermissions";
import useFormatter from "@/composables/useFormatter";
import useStatusBadge from "@/composables/useStatusBadge";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";

const { can } = usePermissions();
const { date, time } = useFormatter();
const { pickupStatusBadge } = useStatusBadge();

const props = defineProps({
    filters: Object,
    data: Object,
});

const perPage = ref(Number(props.filters.per_page) || 5);
const search = ref(props.filters.search || null);
const orderPickup = ref(null);
const showDeleteModal = ref(false);

watch([perPage], updateData);
watch(search, debounce(updateData, 500));

function updateData() {
    const query = {
        ...(search.value ? { search: search.value } : {}),
        ...(perPage.value && perPage.value !== 5
            ? { per_page: perPage.value }
            : {}),
    };
    router.get(route("order-pickup.index"), query, {
        preserveState: true,
        replace: true,
    });
}

const confirmDelete = (item) => {
    orderPickup.value = item;
    showDeleteModal.value = true;
};
const closeDeleteModal = () => {
    showDeleteModal.value = false;
    orderPickup.value = null;
};
const destroy = () => {
    if (!orderPickup.value) return;
    router.delete(route("order-pickup.destroy", orderPickup.value.id), {
        preserveScroll: true,
        onFinish: () => {
            closeDeleteModal();
        },
    });
};

const canEdit = (item) => {
    return item.pickup_status === "ASSIGNED";
};

const canDelete = (item) => {
    return item.pickup_status === "ASSIGNED";
};

const breadcrumbs = [
    { title: "Pickup Request", href: route("order-pickup.index") },
];
</script>

<template>
    <Head title="Pickup Request" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <AppMain>
            <div
                class="flex flex-col md:flex-row md:justify-between md:items-center gap-4"
            >
                <h2 class="text-lg md:text-xl font-bold">
                    Kelola Pickup Request
                </h2>
                <div class="flex items-center gap-2">
                    <SearchBox v-model="search" />
                    <ButtonCreate
                        v-if="can('order.create')"
                        :href="route('order-pickup.create')"
                    />
                </div>
            </div>
            <Table>
                <TableHeader class="bg-muted/50">
                    <TableRow>
                        <TableHead class="w-10">No.</TableHead>
                        <TableHead>Pelanggan</TableHead>
                        <TableHead>Kurir</TableHead>
                        <TableHead>Jadwal Pengambilan</TableHead>
                        <TableHead>Tanggal Diterima</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead class="text-right">Action</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="props.data.data.length === 0">
                        <TableCell
                            :colspan="7"
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
                        <TableCell>{{ item.courier?.name ?? "-" }}</TableCell>
                        <TableCell>
                            {{ date(item.scheduled_at) ?? "-" }} .
                            {{ time(item.scheduled_at) ?? "-" }}</TableCell
                        >
                        <TableCell>
                            {{ date(item.pickup_at) ?? "-" }} .
                            {{ time(item.pickup_at) ?? "-" }}
                        </TableCell>
                        <TableCell>
                            <Badge
                                :class="
                                    pickupStatusBadge(item.pickup_status).class
                                "
                            >
                                {{
                                    pickupStatusBadge(item.pickup_status).label
                                }}
                            </Badge>
                        </TableCell>
                        <TableCell class="text-right space-x-2">
                            <ButtonShow
                                v-if="can('order.detail')"
                                :href="route('order-pickup.show', item.id)"
                            />
                            <ButtonEdit
                                v-if="can('order.update') && canEdit(item)"
                                :href="route('order-pickup.edit', item.id)"
                            />
                            <ButtonDelete
                                v-if="can('order.delete') && canDelete(item)"
                                @click="confirmDelete(item)"
                            />
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
            <Pagination v-model="perPage" :pagination="props.data" />
        </AppMain>
    </AppLayout>
    <Dialog v-model:open="showDeleteModal">
        <DialogContent>
            <DialogHeader>
                <DialogTitle> Apakah Anda benar-benar yakin?</DialogTitle>
                <DialogDescription>
                    Tindakan ini tidak dapat dibatalkan. Ini akan secara
                    permanen menghapus data terkait dari server kami.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <DialogClose as-child>
                    <Button variant="outline" @click="closeDeleteModal">
                        Batal
                    </Button>
                </DialogClose>
                <Button type="button" @click="destroy">Hapus</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
