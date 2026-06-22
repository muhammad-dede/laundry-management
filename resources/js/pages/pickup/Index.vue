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
const pickup = ref(null);
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
    router.get(route("pickup.index"), query, {
        preserveState: true,
        replace: true,
    });
}

const confirmDelete = (item) => {
    pickup.value = item;
    showDeleteModal.value = true;
};
const closeDeleteModal = () => {
    showDeleteModal.value = false;
    pickup.value = null;
};
const destroy = () => {
    if (!pickup.value) return;
    router.delete(route("pickup.destroy", pickup.value.id), {
        preserveScroll: true,
        onFinish: () => {
            closeDeleteModal();
        },
    });
};

const canEdit = (item) => {
    return ["ASSIGNED"].includes(item.pickup_status);
};

const canDelete = (item) => {
    return ["ASSIGNED"].includes(item.pickup_status);
};

const breadcrumbs = [{ title: "Pengambilan", href: route("pickup.index") }];
</script>

<template>
    <Head title="Pengambilan" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <AppMain>
            <div
                class="flex flex-col md:flex-row md:justify-between md:items-center gap-4"
            >
                <h2 class="text-lg md:text-xl font-bold">Kelola Pengambilan</h2>
                <div class="flex items-center gap-2">
                    <SearchBox v-model="search" />
                    <ButtonCreate
                        v-if="can('pickup.create')"
                        :href="route('pickup.create')"
                    />
                </div>
            </div>
            <Table>
                <TableHeader class="bg-muted/50">
                    <TableRow>
                        <TableHead class="w-10">No.</TableHead>
                        <TableHead>Pickup Number</TableHead>
                        <TableHead>Pelanggan</TableHead>
                        <TableHead>Kurir</TableHead>
                        <TableHead>Alamat</TableHead>
                        <TableHead>Waktu</TableHead>
                        <TableHead>Catatan</TableHead>
                        <TableHead>Status</TableHead>
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
                        <TableCell>{{ item.pickup_number ?? "-" }}</TableCell>
                        <TableCell>{{ item.customer?.name ?? "-" }}</TableCell>
                        <TableCell>{{ item.courier?.name ?? "-" }}</TableCell>
                        <TableCell>{{ item.address ?? "-" }}</TableCell>
                        <TableCell>
                            {{ date(item.pickup_at) ?? "-" }} .
                            {{ time(item.pickup_at) ?? "-" }}
                        </TableCell>
                        <TableCell>{{ item.notes ?? "-" }}</TableCell>
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
                            <ButtonEdit
                                v-if="can('pickup.update') && canEdit(item)"
                                :href="route('pickup.edit', item.id)"
                            />
                            <ButtonDelete
                                v-if="can('pickup.delete') && canDelete(item)"
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
