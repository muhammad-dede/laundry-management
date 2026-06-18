<script setup>
import { Head, router, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import { Field } from "@/components/ui/field";
import { Separator } from "@/components/ui/separator";
import { Button } from "@/components/ui/button";
import AppLayout from "@/layouts/AppLayout.vue";
import AppMain from "@/components/AppMain.vue";
import ButtonCancel from "@/components/ButtonCancel.vue";
import HeadingSmall from "@/components/HeadingSmall.vue";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import { Card, CardContent } from "@/components/ui/card";
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from "@/components/ui/dialog";
import useFormatter from "@/composables/useFormatter";
import { Badge } from "@/components/ui/badge";
import useStatusBadge from "@/composables/useStatusBadge";
import {
    LoaderCircle,
    MoveRight,
    Clock3,
    PackageCheck,
    CircleCheckBig,
} from "lucide-vue-next";
import { Label } from "@/components/ui/label";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";

const { currency, date, time } = useFormatter();
const { orderStatusBadge, paymentStatusBadge } = useStatusBadge();

const props = defineProps({
    order: Object,
    paymentMethodOptions: Object,
});

const formPayment = useForm({
    payment_method: "CASH",
});

const openUpdateStatus = ref(false);
const openPayment = ref(false);

const grandTotal = computed(() => {
    return (props.order?.order_details ?? []).reduce((total, item) => {
        return total + Number(item.subtotal);
    }, 0);
});

const statusConfig = {
    QUEUED: {
        label: "Antrian",
        icon: Clock3,
        next: "PROCESS",
    },
    PROCESS: {
        label: "Proses",
        icon: LoaderCircle,
        next: "READY",
    },
    READY: {
        label: "Siap Diambil",
        icon: PackageCheck,
        next: "COMPLETED",
    },
    COMPLETED: {
        label: "Selesai",
        icon: CircleCheckBig,
        next: null,
    },
};

const currentStatus = computed(() => {
    return statusConfig[props.order?.order_status] ?? null;
});

const nextStatus = computed(() => {
    const nextCode = currentStatus.value?.next;

    return nextCode ? statusConfig[nextCode] : null;
});

const updateStatus = () => {
    if (!nextStatus.value) {
        return;
    }
    router.put(
        route("order.update.status", props.order.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                openUpdateStatus.value = false;
            },
        },
    );
};

const payment = () => {
    formPayment.clearErrors();
    formPayment.put(route("order.payment", props.order.id), {
        preserveScroll: true,
        onSuccess: () => {
            openPayment.value = false;
        },
    });
};

const canUpdateStatus = computed(() => {
    const orderStatus = props.order?.order_status;
    const paymentStatus = props.order?.payment_status;

    if (orderStatus === "COMPLETED") {
        return false;
    }

    if (orderStatus === "READY" && paymentStatus !== "PAID") {
        return false;
    }

    return true;
});

const canPayment = computed(() => {
    return props.order?.payment_status === "UNPAID";
});

const breadcrumbs = [
    { title: "Pesanan", href: route("order.index") },
    { title: "Detail Pesanan", href: route("order.show", props.order?.id) },
];
</script>

<template>
    <Head title="Detail Pesanan" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <AppMain>
            <div
                class="flex flex-col md:flex-row md:justify-between md:items-center gap-4"
            >
                <h2 class="text-lg md:text-xl font-bold">Detail Pesanan</h2>
            </div>
            <!-- Informasi Pesanan -->
            <Card class="w-full py-4">
                <CardContent>
                    <HeadingSmall
                        title="Informasi Pesanan"
                        description="Detail pesanan laundry"
                    />
                    <Separator class="my-3" />
                    <div class="grid mb-3">
                        <p class="text-sm text-muted-foreground">Invoice</p>
                        <p class="font-medium">
                            {{ props.order?.invoice_number ?? "-" }}
                        </p>
                    </div>
                    <div class="grid mb-3">
                        <p class="text-sm text-muted-foreground">
                            Nama Pelanggan
                        </p>
                        <p class="font-medium">
                            {{ props.order?.customer?.name ?? "-" }}
                        </p>
                    </div>
                    <div class="grid mb-3">
                        <p class="text-sm text-muted-foreground">No. Telepon</p>
                        <p class="font-medium">
                            {{ props.order?.customer?.phone ?? "-" }}
                        </p>
                    </div>
                    <div class="grid mb-3">
                        <p class="text-sm text-muted-foreground">Address</p>
                        <p class="font-medium">
                            {{ props.order?.customer?.address ?? "-" }}
                        </p>
                    </div>
                    <div class="grid mb-3">
                        <p class="text-sm text-muted-foreground">
                            Tanggal Pesanan
                        </p>
                        <p class="font-medium">
                            {{ date(props.order?.order_date) ?? "-" }} .
                            {{ time(props.order?.order_date) ?? "-" }}
                        </p>
                    </div>
                    <div class="grid mb-3">
                        <p class="text-sm text-muted-foreground">
                            Estimasi Selesai
                        </p>
                        <p class="font-medium">
                            {{
                                date(props.order?.estimated_finish_date) ?? "-"
                            }}
                            .
                            {{
                                time(props.order?.estimated_finish_date) ?? "-"
                            }}
                        </p>
                    </div>
                    <div class="grid mb-3">
                        <p class="text-sm text-muted-foreground mb-1">
                            Status Pembayaran
                        </p>
                        <Badge
                            :class="
                                paymentStatusBadge(props.order?.payment_status)
                                    .class
                            "
                        >
                            {{
                                paymentStatusBadge(props.order?.payment_status)
                                    .label
                            }}
                        </Badge>
                    </div>
                    <div class="grid">
                        <p class="text-sm text-muted-foreground mb-1">
                            Status Pesanan
                        </p>
                        <Badge
                            :class="
                                orderStatusBadge(props.order?.order_status)
                                    .class
                            "
                        >
                            {{
                                orderStatusBadge(props.order?.order_status)
                                    .label
                            }}
                        </Badge>
                    </div>
                </CardContent>
            </Card>
            <!-- Informasi Detail -->
            <Card class="w-full py-4">
                <CardContent>
                    <HeadingSmall
                        title="Informasi Detail Layanan"
                        description="Detail Layanan Pesanan"
                    />
                    <Separator class="my-3" />
                    <Table>
                        <TableHeader class="bg-muted/50">
                            <TableRow>
                                <TableHead class="w-10">No.</TableHead>
                                <TableHead>Layanan</TableHead>
                                <TableHead>Qty</TableHead>
                                <TableHead>Estimasi Pengerjaan</TableHead>
                                <TableHead>Harga</TableHead>
                                <TableHead class="text-right">
                                    Sub Total
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-if="
                                    (props.order?.order_details ?? [])
                                        .length === 0
                                "
                            >
                                <TableCell
                                    :colspan="6"
                                    class="text-center py-4 text-muted-foreground"
                                >
                                    Tidak ada data
                                </TableCell>
                            </TableRow>
                            <TableRow
                                v-else
                                v-for="(item, index) in props.order
                                    ?.order_details"
                                :key="index"
                            >
                                <TableCell class="text-left space-x-2">
                                    {{ index + 1 }}
                                </TableCell>
                                <TableCell>
                                    {{ item.service?.name ?? "-" }}
                                </TableCell>
                                <TableCell>
                                    {{ item.quantity ?? 0 }}
                                    {{ item.service?.unit_type ?? "-" }}
                                </TableCell>
                                <TableCell>
                                    {{ item.service?.estimated_days ?? 0 }} hari
                                </TableCell>
                                <TableCell>
                                    {{ currency(item.price) ?? 0 }}
                                </TableCell>
                                <TableCell class="text-right">
                                    {{ currency(item.subtotal) ?? 0 }}
                                </TableCell>
                            </TableRow>
                            <TableRow
                                v-if="
                                    (props.order?.order_details ?? []).length >
                                    0
                                "
                            >
                                <TableCell
                                    colspan="5"
                                    class="text-left font-semibold bg-muted"
                                >
                                    Grand Total
                                </TableCell>
                                <TableCell
                                    class="font-bold text-right bg-muted"
                                >
                                    {{ currency(grandTotal) }}
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
            <Card class="w-full py-4">
                <CardContent>
                    <HeadingSmall
                        title="Ringkasan Pembayaran"
                        description="Informasi total tagihan pesanan"
                    />
                    <Separator class="my-3" />
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground">Subtotal</span>
                            <span class="font-medium">
                                {{ currency(grandTotal) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground">Diskon</span>
                            <span class="font-medium">
                                {{ currency(props.order?.discount ?? 0) }}
                            </span>
                        </div>
                        <Separator />
                        <div class="flex items-center justify-between">
                            <span class="font-semibold">Total Dibayar</span>
                            <span class="text-lg font-bold">
                                {{ currency(props.order?.grand_total) ?? 0 }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font-semibold">Jenis Pembayaran</span>
                            <span class="text-base font-medium">
                                {{ props.order?.payment_type_label ?? "-" }}
                            </span>
                        </div>
                        <div
                            v-if="props.order?.payment"
                            class="flex items-center justify-between"
                        >
                            <span class="font-semibold">Metode Pembayaran</span>
                            <span class="text-base font-medium">
                                {{
                                    props.order?.payment
                                        ?.payment_method_label ?? "-"
                                }}
                            </span>
                        </div>
                    </div>
                </CardContent>
            </Card>
            <Separator class="my-2" />
            <Field orientation="horizontal">
                <ButtonCancel title="Kembali" :href="route('order.index')" />
                <Button
                    as="a"
                    :href="route('order.print', props.order.id)"
                    target="_blank"
                >
                    Cetak Nota
                </Button>
                <Dialog v-model:open="openUpdateStatus">
                    <DialogTrigger as-child>
                        <Button type="button" v-if="canUpdateStatus"
                            >Update Status</Button
                        >
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-[425px]">
                        <DialogHeader>
                            <DialogTitle> Update Status Pesanan </DialogTitle>
                            <DialogDescription>
                                Apakah Anda yakin ingin mengubah status pesanan
                                ini?
                            </DialogDescription>
                        </DialogHeader>
                        <div class="flex items-center justify-center gap-4">
                            <div
                                class="space-y-1 flex flex-col justify-center items-center gap-1"
                            >
                                <div class="border rounded-full p-3">
                                    <component
                                        :is="currentStatus?.icon"
                                        class="size-5"
                                    />
                                </div>
                                <small class="text-muted-foreground">
                                    Saat Ini
                                </small>
                                <Badge class="uppercase">
                                    {{ currentStatus?.label }}
                                </Badge>
                            </div>
                            <MoveRight />
                            <div
                                class="space-y-1 flex flex-col justify-center items-center gap-1"
                            >
                                <div class="border rounded-full p-3">
                                    <component
                                        :is="nextStatus?.icon"
                                        class="size-5"
                                    />
                                </div>
                                <small class="text-muted-foreground">
                                    Berikutnya
                                </small>
                                <Badge class="uppercase">
                                    {{ nextStatus?.label ?? "-" }}
                                </Badge>
                            </div>
                        </div>
                        <DialogFooter class="mt-4">
                            <DialogClose as-child>
                                <Button variant="outline">Batal</Button>
                            </DialogClose>
                            <Button type="button" @click="updateStatus"
                                >Update</Button
                            >
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
                <Dialog v-model:open="openPayment">
                    <DialogTrigger as-child>
                        <Button type="button" v-if="canPayment">Bayar</Button>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-[425px]">
                        <DialogHeader>
                            <DialogTitle>Bayar Tagihan</DialogTitle>
                            <DialogDescription>
                                Pesanan harus dibayar terlebih dahulu sebelum
                                dapat diselesaikan.
                            </DialogDescription>
                        </DialogHeader>
                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span>Total Tagihan</span>

                                <span class="font-bold">
                                    {{ currency(props.order?.grand_total) }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span>Status Pembayaran</span>
                                <Badge
                                    :class="
                                        paymentStatusBadge(
                                            props.order?.payment_status,
                                        ).class
                                    "
                                >
                                    {{
                                        paymentStatusBadge(
                                            props.order?.payment_status,
                                        ).label
                                    }}
                                </Badge>
                            </div>
                            <RadioGroup v-model="formPayment.payment_method">
                                <template
                                    v-for="item in props.paymentMethodOptions"
                                    :key="item.value"
                                >
                                    <div class="flex items-center space-x-2">
                                        <RadioGroupItem
                                            :id="item.value"
                                            :value="item.value"
                                        />
                                        <Label :for="item.value">{{
                                            item.label
                                        }}</Label>
                                    </div>
                                </template>
                            </RadioGroup>
                        </div>
                        <DialogFooter class="mt-4">
                            <DialogClose as-child>
                                <Button variant="outline">Batal</Button>
                            </DialogClose>
                            <Button type="button" @click="payment"
                                >Bayar</Button
                            >
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </Field>
        </AppMain>
    </AppLayout>
</template>
