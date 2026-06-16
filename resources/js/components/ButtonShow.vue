<script setup>
import { Link } from "@inertiajs/vue3";
import { Eye } from "lucide-vue-next";

const props = defineProps({
    as: {
        type: [Object, String],
        default: Link,
    },
    title: {
        type: String,
        default: "Detail",
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
        <Eye class="size-3" />
    </component>
</template>
