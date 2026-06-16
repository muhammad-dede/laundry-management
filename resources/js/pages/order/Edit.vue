<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import { Field, FieldError, FieldLabel } from "@/components/ui/field";
import { Input } from "@/components/ui/input";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import AppLayout from "@/layouts/AppLayout.vue";
import AppMain from "@/components/AppMain.vue";
import ButtonSubmit from "@/components/ButtonSubmit.vue";
import ButtonCancel from "@/components/ButtonCancel.vue";
import Checkbox from "@/components/ui/checkbox/Checkbox.vue";
import Label from "@/components/ui/label/Label.vue";

const props = defineProps({
    unitTypeOptions: Object,
    service: Object,
});

const form = useForm({
    name: props.service.name,
    unit_type: props.service.unit_type,
    price: props.service.price,
    estimated_days: props.service.estimated_days,
    is_active: props.service.is_active ? true : false,
});

const submit = () => {
    form.clearErrors();
    form.put(route("service.update", props.service.id), {
        preserveScroll: true,
    });
};

const breadcrumbs = [
    { title: "Layanan", href: route("service.index") },
    { title: "Edit Layanan", href: route("service.edit", props.service.id) },
];
</script>

<template>
    <Head title="Edit Layanan" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <AppMain>
            <div
                class="flex flex-col md:flex-row md:justify-between md:items-center gap-4"
            >
                <h2 class="text-lg md:text-xl font-bold">Edit Layanan</h2>
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
                        <FieldLabel for="unit_type">Jenis Unit</FieldLabel>
                        <Select v-model="form.unit_type" name="unit_type">
                            <SelectTrigger id="unit_type">
                                <SelectValue placeholder="Pilih Jenis Unit" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="unitType in props.unitTypeOptions"
                                    :key="unitType.value"
                                    :value="unitType.value"
                                >
                                    {{ unitType.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <FieldError>{{ form.errors.unit_type }}</FieldError>
                    </Field>
                </div>
                <div class="grid lg:grid-cols-2 gap-3 mb-3">
                    <Field>
                        <FieldLabel for="price">Harga</FieldLabel>
                        <Input
                            id="price"
                            type="number"
                            placeholder="Masukkan harga"
                            autocomplete="off"
                            v-model="form.price"
                        />
                        <FieldError>
                            {{ form.errors.price }}
                        </FieldError>
                    </Field>
                    <Field>
                        <FieldLabel for="estimated_days"
                            >Estimasi Hari</FieldLabel
                        >
                        <Input
                            id="estimated_days"
                            type="number"
                            placeholder="Masukkan estimasi hari"
                            autocomplete="off"
                            v-model="form.estimated_days"
                        />
                        <FieldError>
                            {{ form.errors.estimated_days }}
                        </FieldError>
                    </Field>
                </div>
                <div class="grid lg:grid-cols-2 gap-3 mb-5">
                    <div class="flex items-center justify-between">
                        <Label for="is_active" class="flex items-center">
                            <Checkbox id="is_active" v-model="form.is_active" />
                            <span>Aktif</span>
                        </Label>
                    </div>
                </div>
                <Field orientation="horizontal">
                    <ButtonSubmit />
                    <ButtonCancel :href="route('service.index')" />
                </Field>
            </form>
        </AppMain>
    </AppLayout>
</template>
