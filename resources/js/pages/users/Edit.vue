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

const props = defineProps({
    user: Object,
    roles: Object,
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    role: props.user.roles.length ? props.user.roles[0].name : "",
    password: "",
    password_confirmation: "",
});

const submit = () => {
    form.clearErrors();
    form.put(route("users.update", props.user.id), {
        preserveScroll: true,
    });
};

const breadcrumbs = [
    { title: "Pengguna", href: route("users.index") },
    { title: "Ubah Pengguna", href: route("users.edit", props.user.id) },
];
</script>

<template>
    <Head title="Ubah Pengguna" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <AppMain>
            <div
                class="flex flex-col md:flex-row md:justify-between md:items-center gap-4"
            >
                <h2 class="text-lg md:text-xl font-bold">Ubah Pengguna</h2>
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
                        <FieldLabel for="email">Email</FieldLabel>
                        <Input
                            id="email"
                            type="email"
                            placeholder="Masukkan email"
                            autocomplete="off"
                            v-model="form.email"
                        />
                        <FieldError>
                            {{ form.errors.email }}
                        </FieldError>
                    </Field>
                </div>
                <div class="grid lg:grid-cols-2 gap-3 mb-3">
                    <Field>
                        <FieldLabel for="role">Role</FieldLabel>
                        <Select v-model="form.role" name="role">
                            <SelectTrigger id="role">
                                <SelectValue placeholder="Pilih Role" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="role in props.roles"
                                    :key="role.id"
                                    :value="role.name"
                                >
                                    {{ role.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <FieldError>{{ form.errors.role }}</FieldError>
                    </Field>
                </div>
                <div class="grid lg:grid-cols-2 gap-3 mb-3">
                    <Field>
                        <FieldLabel for="password">Password</FieldLabel>
                        <Input
                            id="password"
                            type="password"
                            placeholder="Masukkan password"
                            autocomplete="off"
                            v-model="form.password"
                        />
                        <FieldError>
                            {{ form.errors.password }}
                        </FieldError>
                    </Field>
                    <Field>
                        <FieldLabel for="password_confirmation">
                            Konfirmasi Password
                        </FieldLabel>
                        <Input
                            id="password_confirmation"
                            type="password"
                            placeholder="Masukkan konfirmasi password"
                            autocomplete="off"
                            v-model="form.password_confirmation"
                        />
                        <FieldError>
                            {{ form.errors.password_confirmation }}
                        </FieldError>
                    </Field>
                </div>
                <Field orientation="horizontal">
                    <ButtonSubmit />
                    <ButtonCancel :href="route('users.index')" />
                </Field>
            </form>
        </AppMain>
    </AppLayout>
</template>
