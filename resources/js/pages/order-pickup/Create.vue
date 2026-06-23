<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import { Field, FieldError, FieldLabel } from "@/components/ui/field";
import { Input } from "@/components/ui/input";
import { Separator } from "@/components/ui/separator";
import AppLayout from "@/layouts/AppLayout.vue";
import AppMain from "@/components/AppMain.vue";
import ButtonSubmit from "@/components/ButtonSubmit.vue";
import ButtonCancel from "@/components/ButtonCancel.vue";
import HeadingSmall from "@/components/HeadingSmall.vue";
import SearchBox from "@/components/SearchBox.vue";
import { debounce } from "lodash";
import axios from "axios";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";

const props = defineProps({
    couriers: Object,
});

const form = useForm({
    customer_name: "",
    customer_phone: "",
    customer_address: "",
    courier_id: "",
    scheduled_at: "",
    notes: "",
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
            .get(route("order-pickup.searchCustomer"), {
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

const submit = () => {
    form.clearErrors();
    form.transform((data) => ({
        ...data,
    })).post(route("order-pickup.store"));
};

const breadcrumbs = [
    { title: "Pickup Request", href: route("order-pickup.index") },
    { title: "Tambah Pickup Request", href: route("order-pickup.create") },
];
</script>

<template>
    <Head title="Tambah Pickup Request" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <AppMain>
            <div
                class="flex flex-col md:flex-row md:justify-between md:items-center gap-4"
            >
                <h2 class="text-lg md:text-xl font-bold">
                    Tambah Pickup Request
                </h2>
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
                <!-- Pengambilan -->
                <Separator class="my-3" />
                <HeadingSmall
                    title="Informasi Pengambilan"
                    description="Pilih kurir dan waktu pengambilan"
                />
                <div class="grid lg:grid-cols-2 gap-3 my-3">
                    <Field>
                        <FieldLabel for="courier_id"> Kurir </FieldLabel>
                        <Select v-model="form.courier_id" name="courier_id">
                            <SelectTrigger id="courier_id">
                                <SelectValue placeholder="Pilih Kurir" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="item in props.couriers"
                                    :key="item.id"
                                    :value="item.id"
                                >
                                    {{ item.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <FieldError>
                            {{ form.errors.courier_id }}
                        </FieldError>
                    </Field>
                    <Field>
                        <FieldLabel for="scheduled_at"
                            >Jadwal Pengambilan</FieldLabel
                        >
                        <Input
                            id="scheduled_at"
                            v-model="form.scheduled_at"
                            type="datetime-local"
                        />
                        <FieldError>
                            {{ form.errors.scheduled_at }}
                        </FieldError>
                    </Field>
                </div>
                <div class="grid mb-3">
                    <Field>
                        <FieldLabel for="notes">Catatan</FieldLabel>
                        <Input
                            id="notes"
                            placeholder="Masukkan catatan"
                            autocomplete="off"
                            v-model="form.notes"
                        />
                        <FieldError>
                            {{ form.errors.notes }}
                        </FieldError>
                    </Field>
                </div>
                <Separator class="my-3" />
                <Field orientation="horizontal">
                    <ButtonSubmit />
                    <ButtonCancel :href="route('order-pickup.index')" />
                </Field>
            </form>
        </AppMain>
    </AppLayout>
</template>
