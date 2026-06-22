<script setup>
import { Head, router } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import { Field } from "@/components/ui/field";
import { Separator } from "@/components/ui/separator";
import { Button } from "@/components/ui/button";
import AppLayout from "@/layouts/AppLayout.vue";
import AppMain from "@/components/AppMain.vue";
import ButtonCancel from "@/components/ButtonCancel.vue";
import HeadingSmall from "@/components/HeadingSmall.vue";
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

const { date, time } = useFormatter();
const { pickupStatusBadge } = useStatusBadge();

const props = defineProps({
    pickup: Object,
});

const openUpdateStatus = ref(false);

const statusConfig = {
    ASSIGNED: {
        label: "Kurir Ditugaskan",
        icon: Clock3,
        next: "ON_THE_WAY",
    },
    ON_THE_WAY: {
        label: "Dalam Perjalanan",
        icon: LoaderCircle,
        next: "PICKED_UP",
    },
    PICKED_UP: {
        label: "Cucian Dijemput",
        icon: PackageCheck,
        next: "RECEIVED",
    },
    RECEIVED: {
        label: "Cucian Diterima",
        icon: CircleCheckBig,
        next: null,
    },
};

const currentStatus = computed(() => {
    return statusConfig[props.pickup?.pickup_status] ?? null;
});

const nextStatus = computed(() => {
    const nextCode = currentStatus.value?.next;

    return nextCode ? statusConfig[nextCode] : null;
});

const canUpdateStatus = computed(() => {
    return props.pickup?.pickup_status !== "RECEIVED";
});

const updateStatus = () => {
    if (!nextStatus.value) {
        return;
    }
    router.put(
        route("pickup-task.update-status", props.pickup?.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                openUpdateStatus.value = false;
            },
        },
    );
};

const breadcrumbs = [
    { title: "Pengambilan Laundry", href: route("pickup-task.index") },
    {
        title: "Detail Pengambilan Sata",
        href: route("pickup-task.show", props.pickup?.id),
    },
];
</script>

<template>
    <Head title="Detail Pengambilan Laundry" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <AppMain>
            <div
                class="flex flex-col md:flex-row md:justify-between md:items-center gap-4"
            >
                <h2 class="text-lg md:text-xl font-bold">
                    Detail Pengambilan Laundry
                </h2>
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
                <ButtonCancel
                    title="Kembali"
                    :href="route('pickup-task.index')"
                />
                <Dialog v-model:open="openUpdateStatus">
                    <DialogTrigger as-child>
                        <Button type="button" v-if="canUpdateStatus">
                            Update Status
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-[425px]">
                        <DialogHeader>
                            <DialogTitle>Update Status</DialogTitle>
                            <DialogDescription>
                                Apakah Anda yakin ingin mengubah status
                                pengambilan ini?
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
            </Field>
        </AppMain>
    </AppLayout>
</template>
