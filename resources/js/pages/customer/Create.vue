<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import { Field, FieldError, FieldLabel } from "@/components/ui/field";
import { Input } from "@/components/ui/input";
import AppLayout from "@/layouts/AppLayout.vue";
import AppMain from "@/components/AppMain.vue";
import ButtonSubmit from "@/components/ButtonSubmit.vue";
import ButtonCancel from "@/components/ButtonCancel.vue";

const form = useForm({
    name: "",
    phone: "",
    address: "",
});

const submit = () => {
    form.clearErrors();
    form.post(route("customer.store"), {
        preserveScroll: true,
    });
};

const breadcrumbs = [
    { title: "Pelanggan", href: route("customer.index") },
    { title: "Tambah Pelanggan", href: route("customer.create") },
];
</script>

<template>
    <Head title="Tambah Pelanggan" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <AppMain>
            <div
                class="flex flex-col md:flex-row md:justify-between md:items-center gap-4"
            >
                <h2 class="text-lg md:text-xl font-bold">Tambah Pelanggan</h2>
            </div>
            <form @submit.prevent="submit" class="w-full">
                <div class="grid lg:grid-cols-2 gap-3 mb-3">
                    <Field>
                        <FieldLabel for="name">Nama</FieldLabel>
                        <Input
                            id="name"
                            placeholder="Masukkan nama"
                            autocomplete="off"
                            v-model="form.name"
                        />
                        <FieldError>
                            {{ form.errors.name }}
                        </FieldError>
                    </Field>
                    <Field>
                        <FieldLabel for="phone">No. Telepon</FieldLabel>
                        <Input
                            id="phone"
                            placeholder="Masukkan no. telepon"
                            autocomplete="off"
                            v-model="form.phone"
                        />
                        <FieldError>
                            {{ form.errors.phone }}
                        </FieldError>
                    </Field>
                </div>
                <div class="grid gap-3 mb-3">
                    <Field>
                        <FieldLabel for="address">Alamat</FieldLabel>
                        <Input
                            id="address"
                            placeholder="Masukkan alamat"
                            autocomplete="off"
                            v-model="form.address"
                        />
                        <FieldError>
                            {{ form.errors.address }}
                        </FieldError>
                    </Field>
                </div>
                <Field orientation="horizontal">
                    <ButtonSubmit />
                    <ButtonCancel :href="route('customer.index')" />
                </Field>
            </form>
        </AppMain>
    </AppLayout>
</template>
