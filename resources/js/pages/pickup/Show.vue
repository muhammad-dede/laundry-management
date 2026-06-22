<script setup>
import { Head } from "@inertiajs/vue3";
import { Field } from "@/components/ui/field";
import { Separator } from "@/components/ui/separator";
import AppLayout from "@/layouts/AppLayout.vue";
import AppMain from "@/components/AppMain.vue";
import ButtonCreate from "@/components/ButtonCreate.vue";
import ButtonCancel from "@/components/ButtonCancel.vue";
import HeadingSmall from "@/components/HeadingSmall.vue";
import { Card, CardContent } from "@/components/ui/card";
import useFormatter from "@/composables/useFormatter";
import { Badge } from "@/components/ui/badge";
import useStatusBadge from "@/composables/useStatusBadge";
import { computed } from "vue";

const { date, time } = useFormatter();
const { pickupStatusBadge } = useStatusBadge();

const props = defineProps({
    pickup: Object,
});

const canCreateOrder = computed(() => {
    return ["RECEIVED"].includes(props.pickup?.pickup_status);
});

const breadcrumbs = [
    { title: "Pengambilan", href: route("pickup.index") },
    {
        title: "Detail Pengambilan",
        href: route("pickup.show", props.pickup?.id),
    },
];
</script>

<template>
    <Head title="Detail Pengambilan" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <AppMain>
            <div
                class="flex flex-col md:flex-row md:justify-between md:items-center gap-4"
            >
                <h2 class="text-lg md:text-xl font-bold">Detail Pengambilan</h2>
            </div>
            <!-- Informasi Pengambilan Laundry -->
            <Card class="w-full py-4">
                <CardContent>
                    <HeadingSmall
                        title="Informasi Pengambilan Laundry"
                        description="Detail Pengambilan Laundry"
                    />
                    <Separator class="my-3" />
                    <div class="grid mb-3">
                        <p class="text-sm text-muted-foreground">
                            Pickup Number
                        </p>
                        <p class="font-medium">
                            {{ props.pickup?.pickup_number ?? "-" }}
                        </p>
                    </div>
                    <div class="grid mb-3">
                        <p class="text-sm text-muted-foreground">
                            Nama Pelanggan
                        </p>
                        <p class="font-medium">
                            {{ props.pickup?.customer?.name ?? "-" }}
                        </p>
                    </div>
                    <div class="grid mb-3">
                        <p class="text-sm text-muted-foreground">No. Telepon</p>
                        <p class="font-medium">
                            {{ props.pickup?.customer?.phone ?? "-" }}
                        </p>
                    </div>
                    <div class="grid mb-3">
                        <p class="text-sm text-muted-foreground">Alamat</p>
                        <p class="font-medium">
                            {{ props.pickup?.customer?.address ?? "-" }}
                        </p>
                    </div>
                    <div class="grid mb-3">
                        <p class="text-sm text-muted-foreground">Kurir</p>
                        <p class="font-medium">
                            {{ props.pickup?.courier?.name ?? "-" }}
                        </p>
                    </div>
                    <div class="grid mb-3">
                        <p class="text-sm text-muted-foreground">
                            Jadwal Pengambilan
                        </p>
                        <p class="font-medium">
                            {{ date(props.pickup?.pickup_at) ?? "-" }} .
                            {{ time(props.pickup?.pickup_at) ?? "-" }}
                        </p>
                    </div>
                    <div class="grid mb-3">
                        <p class="text-sm text-muted-foreground">Catatan</p>
                        <p class="font-medium">
                            {{ props.pickup?.notes ?? "-" }}
                        </p>
                    </div>
                    <div class="grid mb-3">
                        <p class="text-sm text-muted-foreground mb-1">Status</p>
                        <Badge
                            :class="
                                pickupStatusBadge(props.pickup?.pickup_status)
                                    .class
                            "
                        >
                            {{
                                pickupStatusBadge(props.pickup?.pickup_status)
                                    .label
                            }}
                        </Badge>
                    </div>
                </CardContent>
            </Card>
            <Separator class="my-2" />
            <Field orientation="horizontal">
                <ButtonCancel title="Kembali" :href="route('pickup.index')" />
                <ButtonCreate
                    v-if="canCreateOrder"
                    title="Buat Pesanan"
                    :href="route('order.create', { pickup: props.pickup?.id })"
                />
            </Field>
        </AppMain>
    </AppLayout>
</template>
