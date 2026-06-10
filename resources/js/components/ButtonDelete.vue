<script setup>
import { Trash } from "lucide-vue-next";

const props = defineProps({
    as: {
        type: [Object, String],
        default: "button",
    },
    title: {
        type: String,
        default: "Hapus",
    },
});
</script>

<template>
    <component
        v-bind="$attrs"
        :is="props.as"
        :title="props.title"
        class="size-7 border border-border rounded-md inline-flex items-center justify-center cursor-pointer disabled:cursor-not-allowed"
    >
        <Trash class="size-3" />
    </component>
</template>
