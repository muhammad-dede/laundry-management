<script setup>
import { Head, router } from "@inertiajs/vue3";
import { ref } from "vue";
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
import usePermissions from "@/composables/usePermissions";
import useFormatter from "@/composables/useFormatter";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
const { can } = usePermissions();
const { currency, date } = useFormatter();
import { Field, FieldLabel } from "@/components/ui/field";
import { Printer } from "lucide-vue-next";

const props = defineProps({
    filters: Object,
    data: Object,
    summary: Object,
});

const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);

function filterData() {
    router.get(
        route("report.income.index"),
        {
            start_date: startDate.value,
            end_date: endDate.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
}

const exportData = () => {
    window.open(
        route("report.income.export", {
            start_date: startDate.value,
            end_date: endDate.value,
        }),
        "_blank",
    );
};

const breadcrumbs = [
    { title: "Laporan Pemasukan", href: route("report.income.index") },
];
</script>

<template>
    <Head title="Laporan Pemasukan" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <AppMain>
            <h2 class="text-lg md:text-xl font-bold">Laporan Pemasukan</h2>
            <div class="flex flex-col md:flex-row md:items-end gap-3">
                <Field class="w-full md:w-auto">
                    <FieldLabel for="start_date">Tanggal Mulai</FieldLabel>
                    <Input id="start_date" type="date" v-model="startDate" />
                </Field>
                <Field class="w-full md:w-auto">
                    <FieldLabel for="end_date">Tanggal Akhir</FieldLabel>
                    <Input id="end_date" type="date" v-model="endDate" />
                </Field>
                <Button class="w-full md:w-auto" @click="filterData">
                    Tampilkan
                </Button>
                <Button
                    v-if="can('report.income.export')"
                    variant="outline"
                    class="w-full md:w-auto"
                    @click="exportData"
                >
                    <Printer /> Export
                </Button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border rounded-lg p-4">
                    <div class="text-sm text-muted-foreground">
                        Total Pemasukan
                    </div>
                    <div class="text-2xl font-bold">
                        {{ props.summary.count }}
                    </div>
                </div>
                <div class="border rounded-lg p-4">
                    <div class="text-sm text-muted-foreground">Nominal</div>
                    <div class="text-2xl font-bold">
                        {{ currency(props.summary.total_amount) }}
                    </div>
                </div>
            </div>
            <Table>
                <TableHeader class="bg-muted/50">
                    <TableRow>
                        <TableHead class="w-10">No.</TableHead>
                        <TableHead>Tanggal</TableHead>
                        <TableHead>Deskripsi</TableHead>
                        <TableHead>Nominal</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="props.data.length === 0">
                        <TableCell
                            :colspan="4"
                            class="text-center py-4 text-muted-foreground"
                        >
                            Tidak ada data
                        </TableCell>
                    </TableRow>
                    <TableRow
                        v-else
                        v-for="(item, index) in props.data"
                        :key="item.id"
                    >
                        <TableCell>{{ index + 1 }}</TableCell>
                        <TableCell>
                            {{ date(item.income_date) ?? "-" }}
                        </TableCell>
                        <TableCell>{{ item.description ?? "-" }}</TableCell>
                        <TableCell>
                            {{ currency(item.amount) ?? "0" }}
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </AppMain>
    </AppLayout>
</template>
