<script setup>
import { Head } from "@inertiajs/vue3";
import { onMounted, computed } from "vue";
import useFormatter from "@/composables/useFormatter";

const { currency, date, time } = useFormatter();

const props = defineProps({
    order: Object,
});

const grandTotal = computed(() => {
    return props.order?.order_details?.reduce((total, item) => {
        return total + Number(item.subtotal);
    }, 0);
});

onMounted(() => {
    setTimeout(() => {
        window.print();
    }, 500);
});
</script>

<template>
    <Head :title="`Invoice ${order.invoice_number}`" />

    <div class="receipt">
        <div class="header">
            <h2>LAUNDRY</h2>
            <p>Jl. Contoh No. 123</p>
            <p>08123456789</p>
        </div>

        <div class="divider"></div>

        <div class="section">
            <div class="row">
                <span>Invoice</span>
                <span>{{ order.invoice_number }}</span>
            </div>

            <div class="row">
                <span>Tanggal</span>
                <span>
                    {{ date(order.order_date) }}
                </span>
            </div>

            <div class="row">
                <span>Jam</span>
                <span>
                    {{ time(order.order_date) }}
                </span>
            </div>

            <div class="row">
                <span>Pelanggan</span>
                <span>{{ order.customer?.name }}</span>
            </div>

            <div class="row">
                <span>Telp</span>
                <span>{{ order.customer?.phone }}</span>
            </div>
        </div>

        <div class="divider"></div>

        <div v-for="item in order.order_details" :key="item.id" class="service">
            <div class="service-name">
                {{ item.service?.name }}
            </div>

            <div class="row">
                <span>
                    {{ item.quantity }}
                    {{ item.service?.unit_type }}
                    ×
                    {{ currency(item.price) }}
                </span>

                <span>
                    {{ currency(item.subtotal) }}
                </span>
            </div>
        </div>

        <div class="divider"></div>

        <div class="row">
            <span>Subtotal</span>
            <span>{{ currency(grandTotal) }}</span>
        </div>

        <div class="row">
            <span>Diskon</span>
            <span>{{ currency(order.discount) }}</span>
        </div>

        <div class="row total">
            <span>TOTAL</span>
            <span>{{ currency(order.grand_total) }}</span>
        </div>

        <div class="divider"></div>

        <div class="section">
            <div class="row">
                <span>Status</span>
                <span>{{ order.order_status_label }}</span>
            </div>

            <div class="row">
                <span>Pembayaran</span>
                <span>{{ order.payment_status_label }}</span>
            </div>

            <div class="row">
                <span>Estimasi</span>
                <span>
                    {{ date(order.estimated_finish_date) }}
                </span>
            </div>
        </div>

        <div class="divider"></div>

        <div class="footer">
            <p>Terima Kasih</p>
            <p>Simpan nota ini saat pengambilan</p>
        </div>
    </div>
</template>

<style>
.receipt {
    width: 76mm;
    margin: 0 auto;
    padding: 2mm;
    font-family: monospace;
    font-size: 11px;
    color: #000;
}

.header {
    text-align: center;
}

.header h2 {
    margin: 0;
    font-size: 16px;
}

.header p {
    margin: 2px 0;
}

.section {
    margin: 4px 0;
}

.service {
    margin-bottom: 6px;
}

.service-name {
    font-weight: bold;
    margin-bottom: 2px;
}

.row {
    display: flex;
    justify-content: space-between;
    gap: 8px;
}

.row span:last-child {
    text-align: right;
}

.total {
    font-size: 13px;
    font-weight: bold;
}

.divider {
    border-top: 1px dashed #000;
    margin: 6px 0;
}

.footer {
    text-align: center;
    margin-top: 8px;
}

.footer p {
    margin: 2px 0;
}

@page {
    size: 80mm auto;
    margin: 0;
}

@media print {
    html,
    body {
        margin: 0;
        padding: 0;
        width: 80mm;
        background: white;
    }

    .receipt {
        width: 76mm;
        margin: 0;
        padding: 2mm;
    }
}
</style>
