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
const { currency, date, time } = useFormatter();
const { orderStatusBadge, paymentStatusBadge } = useStatusBadge();

const props = defineProps({
    filters: Object,
    data: Object,
});

const perPage = ref(Number(props.filters.per_page) || 5);
const search = ref(props.filters.search || null);
const order = ref(null);
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
    router.get(route("order.index"), query, {
        preserveState: true,
        replace: true,
    });
}

const confirmDelete = (item) => {
    order.value = item;
    showDeleteModal.value = true;
};
const closeDeleteModal = () => {
    showDeleteModal.value = false;
    order.value = null;
};
const destroy = () => {
    if (!order.value) return;
    router.delete(route("order.destroy", order.value.id), {
        preserveScroll: true,
        onFinish: () => {
            closeDeleteModal();
        },
    });
};

const canEdit = (item) => {
    return item.order_status === "QUEUED";
};

const canDelete = (item) => {
    return item.order_status === "QUEUED" && item.payment_status === "UNPAID";
};

const breadcrumbs = [{ title: "Transaksi", href: route("order.index") }];
</script>

<template>
    <Head title="Transaksi" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <AppMain>
            <div
                class="flex flex-col md:flex-row md:justify-between md:items-center gap-4"
            >
                <h2 class="text-lg md:text-xl font-bold">Kelola Transaksi</h2>
                <div class="flex items-center gap-2">
                    <SearchBox v-model="search" />
                    <ButtonCreate
                        v-if="can('order.create')"
                        :href="route('order.create')"
                    />
                </div>
            </div>
            <Table>
                <TableHeader class="bg-muted/50">
                    <TableRow>
                        <TableHead class="w-10">No.</TableHead>
                        <TableHead>Invoice</TableHead>
                        <TableHead>Pelanggan</TableHead>
                        <TableHead>Tgl Transaksi</TableHead>
                        <TableHead>Estimasi</TableHead>
                        <TableHead>Total</TableHead>
                        <TableHead>Status Pembayaran</TableHead>
                        <TableHead>Status Transaksi</TableHead>
                        <TableHead class="text-right">Action</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="props.data.data.length === 0">
                        <TableCell
                            :colspan="9"
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
                        <TableCell>{{ item.invoice_number ?? "-" }}</TableCell>
                        <TableCell>{{ item.customer?.name ?? "-" }}</TableCell>
                        <TableCell>
                            {{ date(item.order_date) ?? "-" }} .
                            {{ time(item.order_date) ?? "-" }}</TableCell
                        >
                        <TableCell>
                            {{ date(item.estimated_finish_date) ?? "-" }} .
                            {{ time(item.estimated_finish_date) ?? "-" }}
                        </TableCell>
                        <TableCell>{{
                            currency(item.grand_total) ?? "-"
                        }}</TableCell>
                        <TableCell>
                            <Badge
                                :class="
                                    paymentStatusBadge(item.payment_status)
                                        .class
                                "
                            >
                                {{
                                    paymentStatusBadge(item.payment_status)
                                        .label
                                }}
                            </Badge>
                        </TableCell>
                        <TableCell>
                            <Badge
                                :class="[
                                    orderStatusBadge(item.order_status).class,
                                ]"
                            >
                                {{ orderStatusBadge(item.order_status).label }}
                            </Badge>
                        </TableCell>
                        <TableCell class="text-right space-x-2">
                            <ButtonShow
                                v-if="can('order.detail')"
                                :href="route('order.show', item.id)"
                            />
                            <ButtonEdit
                                v-if="can('order.update') && canEdit(item)"
                                :href="route('order.edit', item.id)"
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
