<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import { Field, FieldError, FieldLabel } from "@/components/ui/field";
import { Input } from "@/components/ui/input";
import AppLayout from "@/layouts/AppLayout.vue";
import AppMain from "@/components/AppMain.vue";
import ButtonSubmit from "@/components/ButtonSubmit.vue";
import ButtonCancel from "@/components/ButtonCancel.vue";

const props = defineProps({
    income: Object,
});

const form = useForm({
    income_date: props.income.income_date ?? "",
    description: props.income.description ?? "",
    amount: props.income.amount ?? "",
});

const submit = () => {
    form.clearErrors();
    form.put(route("income.update", props.income.id), {
        preserveScroll: true,
    });
};

const breadcrumbs = [
    { title: "Pemasukan Lain", href: route("income.index") },
    {
        title: "Edit Pemasukan Lain",
        href: route("income.edit", props.income.id),
    },
];
</script>

<template>
    <Head title="Edit Pemasukan Lain" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <AppMain>
            <div
                class="flex flex-col md:flex-row md:justify-between md:items-center gap-4"
            >
                <h2 class="text-lg md:text-xl font-bold">
                    Edit Pemasukan Lain
                </h2>
            </div>
            <form @submit.prevent="submit" class="w-full">
                <div class="grid lg:grid-cols-2 gap-3 mb-3">
                    <Field>
                        <FieldLabel for="income_date">Tanggal</FieldLabel>
                        <Input
                            id="income_date"
                            placeholder="Masukkan tanggal"
                            autocomplete="off"
                            v-model="form.income_date"
                            type="date"
                        />
                        <FieldError>
                            {{ form.errors.income_date }}
                        </FieldError>
                    </Field>
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
                </div>
                <div class="grid lg:grid-cols-2 gap-3 mb-3">
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
                    <ButtonCancel :href="route('income.index')" />
                </Field>
            </form>
        </AppMain>
    </AppLayout>
</template>
