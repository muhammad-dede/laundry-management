<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import { Field, FieldError, FieldLabel } from "@/components/ui/field";
import { Input } from "@/components/ui/input";
import AppLayout from "@/layouts/AppLayout.vue";
import AppMain from "@/components/AppMain.vue";
import ButtonSubmit from "@/components/ButtonSubmit.vue";
import ButtonCancel from "@/components/ButtonCancel.vue";

const form = useForm({
    expense_date: "",
    category: "",
    description: "",
    amount: "",
});

const submit = () => {
    form.clearErrors();
    form.post(route("expense.store"), {
        preserveScroll: true,
    });
};

const breadcrumbs = [
    { title: "Pengeluaran", href: route("expense.index") },
    { title: "Tambah Pengeluaran", href: route("expense.create") },
];
</script>

<template>
    <Head title="Tambah Pengeluaran" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <AppMain>
            <div
                class="flex flex-col md:flex-row md:justify-between md:items-center gap-4"
            >
                <h2 class="text-lg md:text-xl font-bold">Tambah Pengeluaran</h2>
            </div>
            <form @submit.prevent="submit" class="w-full">
                <div class="grid lg:grid-cols-2 gap-3 mb-3">
                    <Field>
                        <FieldLabel for="expense_date">Tanggal</FieldLabel>
                        <Input
                            id="expense_date"
                            placeholder="Masukkan tanggal"
                            autocomplete="off"
                            v-model="form.expense_date"
                            type="date"
                        />
                        <FieldError>
                            {{ form.errors.expense_date }}
                        </FieldError>
                    </Field>
                    <Field>
                        <FieldLabel for="category">Kategori</FieldLabel>
                        <Input
                            id="category"
                            placeholder="Masukkan kategori"
                            autocomplete="off"
                            v-model="form.category"
                        />
                        <FieldError>
                            {{ form.errors.category }}
                        </FieldError>
                    </Field>
                </div>
                <div class="grid lg:grid-cols-2 gap-3 mb-3">
                    <Field>
                        <FieldLabel for="description">Deskripsi</FieldLabel>
                        <Input
                            id="description"
                            placeholder="Masukkan deskripsi"
                            autocomplete="off"
                            v-model="form.description"
                        />
                        <FieldError>
                            {{ form.errors.description }}
                        </FieldError>
                    </Field>
                    <Field>
                        <FieldLabel for="amount">Nominal</FieldLabel>
                        <Input
                            id="amount"
                            placeholder="Masukkan nominal"
                            autocomplete="off"
                            v-model="form.amount"
                            type="number"
                        />
                        <FieldError>
                            {{ form.errors.amount }}
                        </FieldError>
                    </Field>
                </div>
                <Field orientation="horizontal">
                    <ButtonSubmit />
                    <ButtonCancel :href="route('expense.index')" />
                </Field>
            </form>
        </AppMain>
    </AppLayout>
</template>
