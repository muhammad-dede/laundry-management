<script setup>
import { Link } from "@inertiajs/vue3";
import { buttonVariants } from "@/components/ui/button";

const props = defineProps({
    as: {
        type: [Object, String],
        default: Link,
    },
    title: {
        type: String,
        default: "Batal",
    },
});
</script>

<template>
    <component
        v-bind="$attrs"
        :is="props.as"
        :class="buttonVariants({ variant: 'outline' })"
    >
        {{ props.title }}
    </component>
</template>
