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
import AppLayout from "@/layouts/AppLayout.vue";
import AppMain from "@/components/AppMain.vue";
import SearchBox from "@/components/SearchBox.vue";
import Pagination from "@/components/Pagination.vue";
import ButtonCreate from "@/components/ButtonCreate.vue";
import ButtonEdit from "@/components/ButtonEdit.vue";
import ButtonDelete from "@/components/ButtonDelete.vue";
import usePermissions from "@/composables/usePermissions";
import useFormatter from "@/composables/useFormatter";

const { can } = usePermissions();
const { currency } = useFormatter();

const props = defineProps({
    filters: Object,
    data: Object,
});

const perPage = ref(Number(props.filters.per_page) || 5);
const search = ref(props.filters.search || null);
const service = ref(null);
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
    router.get(route("service.index"), query, {
        preserveState: true,
        replace: true,
    });
}

const confirmDelete = (item) => {
    service.value = item;
    showDeleteModal.value = true;
};
const closeDeleteModal = () => {
    showDeleteModal.value = false;
    service.value = null;
};
const destroy = () => {
    if (!service.value) return;
    router.delete(route("service.destroy", service.value.id), {
        preserveScroll: true,
        onFinish: () => {
            closeDeleteModal();
        },
    });
};

const breadcrumbs = [{ title: "Layanan", href: route("service.index") }];
</script>

<template>
    <Head title="Layanan" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <AppMain>
            <div
                class="flex flex-col md:flex-row md:justify-between md:items-center gap-4"
            >
                <h2 class="text-lg md:text-xl font-bold">Kelola Layanan</h2>
                <div class="flex items-center gap-2">
                    <SearchBox v-model="search" />
                    <ButtonCreate
                        v-if="can('service.create')"
                        :href="route('service.create')"
                    />
                </div>
            </div>
            <Table>
                <TableHeader class="bg-muted/50">
                    <TableRow>
                        <TableHead class="w-10">No.</TableHead>
                        <TableHead>Nama</TableHead>
                        <TableHead>Tipe Unit</TableHead>
                        <TableHead>Harga</TableHead>
                        <TableHead>Estimasi Hari</TableHead>
                        <TableHead>Aktif</TableHead>
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
                        <TableCell>{{ item.name ?? "-" }}</TableCell>
                        <TableCell>
                            {{
                                item.unit_type_label ?? item.unit_type
                            }}</TableCell
                        >
                        <TableCell>{{ currency(item.price) ?? "-" }}</TableCell>
                        <TableCell>{{ item.estimated_days ?? "-" }}</TableCell>
                        <TableCell>{{
                            item.is_active ? "Ya" : "Tidak"
                        }}</TableCell>
                        <TableCell class="text-right space-x-2">
                            <ButtonEdit
                                v-if="can('service.update')"
                                :href="route('service.edit', item.id)"
                            />
                            <ButtonDelete
                                v-if="can('service.delete')"
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
