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
const { orderStatusBadge, paymentStatusBadge, pickupStatusBadge } =
    useStatusBadge();

const props = defineProps({
    orderPickup: Object,
});

const breadcrumbs = [
    { title: "Pickup Request", href: route("order-pickup.index") },
    {
        title: "Detail Pickup Request",
        href: route("order-pickup.show", props.orderPickup?.id),
    },
];
</script>

<template>
    <Head title="Detail Pickup Request" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <AppMain>
            <div
                class="flex flex-col md:flex-row md:justify-between md:items-center gap-4"
            >
                <h2 class="text-lg md:text-xl font-bold">
                    Detail Pickup Request
                </h2>
            </div>
            <!-- Informasi Pesanan -->
            <Card class="w-full py-4">
                <CardContent>
                    <HeadingSmall
                        title="Informasi Pickup Request"
                        description="Detail pickup request"
                    />
                    <Separator class="my-3" />
                    <div class="grid mb-3">
                        <p class="text-sm text-muted-foreground">
                            Nama Pelanggan
                        </p>
                        <p class="font-medium">
                            {{ props.orderPickup?.customer?.name ?? "-" }}
                        </p>
                    </div>
                    <div class="grid mb-3">
                        <p class="text-sm text-muted-foreground">No. Telepon</p>
                        <p class="font-medium">
                            {{ props.orderPickup?.customer?.phone ?? "-" }}
                        </p>
                    </div>
                    <div class="grid mb-3">
                        <p class="text-sm text-muted-foreground">Address</p>
                        <p class="font-medium">
                            {{ props.orderPickup?.customer?.address ?? "-" }}
                        </p>
                    </div>
                    <div class="grid mb-3">
                        <p class="text-sm text-muted-foreground">
                            Jadwal Pengambilan
                        </p>
                        <p class="font-medium">
                            {{ date(props.orderPickup?.scheduled_at) ?? "-" }} .
                            {{ time(props.orderPickup?.scheduled_at) ?? "-" }}
                        </p>
                    </div>
                    <div class="grid mb-3">
                        <p class="text-sm text-muted-foreground">
                            Tanggal Diterima
                        </p>
                        <p class="font-medium">
                            {{ date(props.orderPickup?.pickup_at) ?? "-" }}
                            .
                            {{ time(props.orderPickup?.pickup_at) ?? "-" }}
                        </p>
                    </div>
                    <div class="grid mb-3">
                        <p class="text-sm text-muted-foreground">Catatan</p>
                        <p class="font-medium">
                            {{ props.orderPickup?.notes ?? "-" }}
                        </p>
                    </div>
                    <div class="grid">
                        <p class="text-sm text-muted-foreground mb-1">Status</p>
                        <Badge
                            :class="
                                pickupStatusBadge(
                                    props.orderPickup?.pickup_status,
                                ).class
                            "
                        >
                            {{
                                pickupStatusBadge(
                                    props.orderPickup?.pickup_status,
                                ).label
                            }}
                        </Badge>
                    </div>
                </CardContent>
            </Card>
            <Separator class="my-2" />
            <Field orientation="horizontal">
                <ButtonCancel
                    title="Kembali"
                    :href="route('order-pickup.index')"
                />
            </Field>
        </AppMain>
    </AppLayout>
</template>
