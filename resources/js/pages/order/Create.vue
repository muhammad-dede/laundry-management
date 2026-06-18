<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
import { Field, FieldError, FieldLabel } from "@/components/ui/field";
import { Input } from "@/components/ui/input";
import { Separator } from "@/components/ui/separator";
import { Button } from "@/components/ui/button";
import AppLayout from "@/layouts/AppLayout.vue";
import AppMain from "@/components/AppMain.vue";
import ButtonSubmit from "@/components/ButtonSubmit.vue";
import ButtonCancel from "@/components/ButtonCancel.vue";
import HeadingSmall from "@/components/HeadingSmall.vue";
import SearchBox from "@/components/SearchBox.vue";
import { debounce } from "lodash";
import axios from "axios";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import ButtonDelete from "@/components/ButtonDelete.vue";
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import useFormatter from "@/composables/useFormatter";

const { currency, date } = useFormatter();

const props = defineProps({
    services: Object,
    paymentTypeOptions: Object,
    paymentMethodOptions: Object,
});

const form = useForm({
    customer_name: "",
    customer_phone: "",
    customer_address: "",
    notes: "",
    discount: 0,
    payment_type: "",
    payment_method: "",
    order_detail: [],
});

const defaultOrderDetail = () => ({
    service_id: null,
    service_name: null,
    service_unit_type: null,
    service_estimated_days: 0,
    quantity: 1,
    price: 0,
});

const searchCustomer = ref(null);
const customers = ref([]);
watch(
    searchCustomer,
    debounce(() => {
        if (!searchCustomer.value) {
            customers.value = [];
            return;
        }
        axios
            .get(route("order.searchCustomer"), {
                params: {
                    search: searchCustomer.value,
                },
            })
            .then((response) => {
                customers.value = response.data;
            });
    }, 1000),
);

const selectCustomer = (customer) => {
    form.customer_name = customer.name;
    form.customer_phone = customer.phone;
    form.customer_address = customer.address;
    searchCustomer.value = null;
    customers.value = [];
};

const showOrderDetail = ref(false);
const formOrderDetail = ref(defaultOrderDetail());
const addOrderDetail = () => {
    showOrderDetail.value = true;
};
const resetOrderDetailForm = () => {
    formOrderDetail.value = defaultOrderDetail();
    showOrderDetail.value = false;
};
watch(
    () => formOrderDetail.value.service_id,
    (value) => {
        const service = props.services.find((item) => item.id === value);

        formOrderDetail.value.service_name = service?.name ?? "-";
        formOrderDetail.value.service_unit_type = service?.unit_type ?? "-";
        formOrderDetail.value.service_estimated_days =
            service?.estimated_days ?? 0;
        formOrderDetail.value.price = service?.price ?? 0;
    },
);
const subtotal = computed(() => {
    return (
        Number(formOrderDetail.value.quantity) *
        Number(formOrderDetail.value.price)
    );
});
const saveOrderDetail = () => {
    if (!formOrderDetail.value.service_id) {
        return;
    }

    const existing = form.order_detail.find(
        (item) => item.service_id === formOrderDetail.value.service_id,
    );

    if (existing) {
        existing.quantity += Number(formOrderDetail.value.quantity);
        existing.subtotal = existing.quantity * existing.price;

        resetOrderDetailForm();
        return;
    }

    form.order_detail.push({
        service_id: formOrderDetail.value.service_id,
        service_name: formOrderDetail.value.service_name,
        service_unit_type: formOrderDetail.value.service_unit_type,
        service_estimated_days: formOrderDetail.value.service_estimated_days,
        quantity: Number(formOrderDetail.value.quantity),
        price: Number(formOrderDetail.value.price),
        subtotal: subtotal.value,
    });

    resetOrderDetailForm();
};
const removeOrderDetail = (index) => {
    form.order_detail.splice(index, 1);
};
const grandTotal = computed(() => {
    return form.order_detail.reduce((total, item) => {
        return total + Number(item.subtotal);
    }, 0);
});
const totalAfterDiscount = computed(() => {
    const discount = Math.min(Number(form.discount || 0), grandTotal.value);

    return grandTotal.value - discount;
});
const estimatedDays = computed(() => {
    if (form.order_detail.length === 0) {
        return 0;
    }

    return Math.max(
        ...form.order_detail.map((item) =>
            Number(item.service_estimated_days || 0),
        ),
    );
});
const estimatedFinishDate = computed(() => {
    if (estimatedDays.value === 0) {
        return null;
    }

    const finishDate = new Date();

    finishDate.setDate(finishDate.getDate() + Number(estimatedDays.value));

    return finishDate;
});

watch(
    () => form.payment_type,
    (value) => {
        if (value === "PAY_LATER") {
            form.payment_method = "";
        }
    },
);

const submit = () => {
    if (form.order_detail.length === 0) {
        alert("Minimal 1 layanan harus dipilih");
        return;
    }
    form.clearErrors();
    form.transform((data) => ({
        ...data,
        estimated_finish_date: estimatedFinishDate
            ? estimatedFinishDate.value
            : null,
    })).post(route("order.store"));
};

const breadcrumbs = [
    { title: "Pesanan", href: route("order.index") },
    { title: "Tambah Pesanan", href: route("order.create") },
];
</script>

<template>
    <Head title="Tambah Pesanan" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <AppMain>
            <div
                class="flex flex-col md:flex-row md:justify-between md:items-center gap-4"
            >
                <h2 class="text-lg md:text-xl font-bold">Tambah Pesanan</h2>
            </div>
            <form @submit.prevent="submit" class="w-full">
                <!-- Customer -->
                <HeadingSmall
                    title="Informasi Pelanggan"
                    description="Cari pelanggan yang sudah terdaftar atau buat pelanggan baru"
                />
                <div class="grid my-3">
                    <div class="flex items-center gap-2">
                        <div class="relative w-full">
                            <SearchBox
                                placeholder="Cari pelanggan..."
                                v-model="searchCustomer"
                            />
                            <div
                                v-if="customers.length"
                                class="absolute z-50 mt-1 w-full bg-white border rounded-lg shadow"
                            >
                                <button
                                    v-for="customer in customers"
                                    :key="customer.id"
                                    type="button"
                                    class="w-full text-left p-3 hover:bg-gray-100"
                                    @click="selectCustomer(customer)"
                                >
                                    <div class="font-medium">
                                        {{ customer.name }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ customer.phone }}
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="grid lg:grid-cols-2 gap-3 mb-3">
                    <Field>
                        <FieldLabel for="customer_name">Nama</FieldLabel>
                        <Input
                            id="customer_name"
                            placeholder="Masukkan nama"
                            autocomplete="off"
                            v-model="form.customer_name"
                        />
                        <FieldError>
                            {{ form.errors.customer_name }}
                        </FieldError>
                    </Field>
                    <Field>
                        <FieldLabel for="customer_phone"
                            >No. Telepon</FieldLabel
                        >
                        <Input
                            id="customer_phone"
                            placeholder="Masukkan no. telepon"
                            autocomplete="off"
                            v-model="form.customer_phone"
                        />
                        <FieldError>
                            {{ form.errors.customer_phone }}
                        </FieldError>
                    </Field>
                </div>
                <div class="grid mb-3">
                    <Field>
                        <FieldLabel for="customer_address">Alamat</FieldLabel>
                        <Input
                            id="customer_address"
                            placeholder="Masukkan alamat"
                            autocomplete="off"
                            v-model="form.customer_address"
                        />
                        <FieldError>
                            {{ form.errors.customer_address }}
                        </FieldError>
                    </Field>
                </div>
                <!-- Layanan -->
                <Separator class="my-3" />
                <HeadingSmall
                    title="Informasi Pesanan"
                    description="Pilih layanan dan jumlah yang diinginkan"
                />
                <div class="flex my-3">
                    <Button type="button" @click="addOrderDetail">
                        Tambah Layanan
                    </Button>
                </div>
                <Table>
                    <TableHeader class="bg-muted/50">
                        <TableRow>
                            <TableHead class="w-10"></TableHead>
                            <TableHead>Layanan</TableHead>
                            <TableHead>Qty</TableHead>
                            <TableHead>Estimasi Pengerjaan</TableHead>
                            <TableHead>Harga</TableHead>
                            <TableHead class="text-right">Sub Total</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="form.order_detail.length === 0">
                            <TableCell
                                :colspan="6"
                                class="text-center py-4 text-muted-foreground"
                            >
                                Tidak ada data
                            </TableCell>
                        </TableRow>
                        <TableRow
                            v-else
                            v-for="(item, index) in form.order_detail"
                            :key="index"
                        >
                            <TableCell class="text-left space-x-2">
                                <ButtonDelete
                                    @click="removeOrderDetail(index)"
                                />
                            </TableCell>
                            <TableCell>
                                {{ item.service_name ?? "" }}
                            </TableCell>
                            <TableCell>
                                {{ item.quantity ?? 0 }}
                                {{ item.service_unit_type ?? "" }}
                            </TableCell>
                            <TableCell>
                                {{ item.service_estimated_days ?? 0 }} hari
                            </TableCell>
                            <TableCell>
                                {{ currency(item.price) ?? 0 }}
                            </TableCell>
                            <TableCell class="text-right">
                                {{ currency(item.subtotal) ?? 0 }}
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="form.order_detail.length > 0">
                            <TableCell
                                colspan="5"
                                class="text-left font-semibold bg-muted"
                            >
                                Grand Total
                            </TableCell>
                            <TableCell class="font-bold text-right bg-muted">
                                {{ currency(grandTotal) }}
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="form.order_detail.length > 0">
                            <TableCell
                                colspan="5"
                                class="text-left font-semibold bg-muted"
                            >
                                Estimasi Selesai
                            </TableCell>
                            <TableCell class="font-bold text-right bg-muted">
                                {{
                                    estimatedFinishDate
                                        ? date(estimatedFinishDate)
                                        : "-"
                                }}
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
                <div class="grid lg:grid-cols-2 gap-3 mt-3">
                    <Field>
                        <FieldLabel for="discount">Diskon (Rupiah)</FieldLabel>
                        <Input
                            id="discount"
                            placeholder="Masukkan diskon (optional)"
                            autocomplete="off"
                            v-model="form.discount"
                            type="number"
                        />
                        <FieldError>
                            {{ form.errors.discount }}
                        </FieldError>
                    </Field>
                    <Field>
                        <FieldLabel for="notes">Catatan</FieldLabel>
                        <Input
                            id="notes"
                            placeholder="Masukkan catatan (Optional)"
                            autocomplete="off"
                            v-model="form.notes"
                        />
                        <FieldError>
                            {{ form.errors.notes }}
                        </FieldError>
                    </Field>
                </div>
                <div
                    class="rounded-lg border p-4 space-y-2"
                    v-if="form.order_detail.length > 0"
                >
                    <div class="flex justify-between text-lg">
                        <span>Total Tagihan</span>
                        <strong>
                            {{ currency(totalAfterDiscount) }}
                        </strong>
                    </div>
                </div>
                <!-- Pembayaran -->
                <Separator class="my-3" />
                <HeadingSmall
                    title="Pembayaran"
                    description="Pilih metode pembayaran yang digunakan"
                />
                <div class="grid lg:grid-cols-2 gap-3 my-3">
                    <Field>
                        <FieldLabel for="payment_type">
                            Jenis Pembayaran
                        </FieldLabel>
                        <Select v-model="form.payment_type" name="payment_type">
                            <SelectTrigger id="payment_type">
                                <SelectValue
                                    placeholder="Pilih Jenis Pembayaran"
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="item in props.paymentTypeOptions"
                                    :key="item.value"
                                    :value="item.value"
                                >
                                    {{ item.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <FieldError>
                            {{ form.errors.payment_type }}
                        </FieldError>
                    </Field>
                    <Field v-if="form.payment_type === 'FULL_PAYMENT'">
                        <FieldLabel for="payment_method">
                            Metode Pembayaran
                        </FieldLabel>
                        <Select
                            v-model="form.payment_method"
                            name="payment_method"
                        >
                            <SelectTrigger id="payment_method">
                                <SelectValue
                                    placeholder="Pilih Metode Pembayaran"
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="item in props.paymentMethodOptions"
                                    :key="item.value"
                                    :value="item.value"
                                >
                                    {{ item.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <FieldError>
                            {{ form.errors.payment_method }}
                        </FieldError>
                    </Field>
                </div>
                <Separator class="my-3" />
                <Field orientation="horizontal">
                    <ButtonSubmit />
                    <ButtonCancel :href="route('order.index')" />
                </Field>
            </form>
        </AppMain>
    </AppLayout>
    <Dialog v-model:open="showOrderDetail">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Tambah Layanan</DialogTitle>
                <DialogDescription>
                    Pilih Layanan dan jumlah yang diinginkan
                </DialogDescription>
            </DialogHeader>
            <div class="grid gap-4">
                <Field>
                    <FieldLabel for="service_id">Layanan</FieldLabel>
                    <Select
                        v-model="formOrderDetail.service_id"
                        name="service_id"
                    >
                        <SelectTrigger id="service_id">
                            <SelectValue placeholder="Pilih Layanan" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="item in props.services"
                                :key="item.id"
                                :value="item.id"
                            >
                                {{ item.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </Field>
            </div>
            <div class="grid gap-4">
                <Field>
                    <FieldLabel for="quantity">Qty</FieldLabel>
                    <Input
                        id="quantity"
                        placeholder="Quantity"
                        autocomplete="off"
                        type="number"
                        min="1"
                        step="0.1"
                        v-model="formOrderDetail.quantity"
                    />
                </Field>
            </div>
            <Separator />
            <div class="grid gap-4">
                <div class="inline-flex justify-between gap-2">
                    <span class="text-muted-foreground"
                        >Estimasi Pengerjaan</span
                    >
                    <span class="font-medium">
                        {{ formOrderDetail.service_estimated_days ?? "0" }} hari
                    </span>
                </div>
                <div class="inline-flex justify-between gap-2">
                    <span class="text-muted-foreground">Tipe Unit</span>
                    <span class="font-medium">
                        {{ formOrderDetail.service_unit_type ?? "-" }}
                    </span>
                </div>
                <div class="inline-flex justify-between gap-2">
                    <span class="text-muted-foreground">Harga</span>
                    <span class="font-medium">
                        {{ currency(formOrderDetail.price) ?? "0" }}
                    </span>
                </div>
                <div class="inline-flex justify-between gap-2">
                    <span class="text-muted-foreground">Sub Total</span>
                    <span class="font-medium">
                        {{ currency(subtotal) ?? "0" }}
                    </span>
                </div>
            </div>
            <DialogFooter>
                <DialogClose as-child>
                    <Button variant="outline">Batal</Button>
                </DialogClose>
                <Button type="button" @click="saveOrderDetail">Simpan</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
