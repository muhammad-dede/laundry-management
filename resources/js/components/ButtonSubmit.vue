<script setup>
import { Button } from "@/components/ui/button";

const props = defineProps({
    type: {
        type: String,
        default: "submit",
    },
    title: {
        type: String,
        default: "Submit",
    },
    variant: {
        type: String,
        default: "default",
    },
});
</script>

<template>
    <Button :type="props.type" :variant="props.variant" class="cursor-pointer">
        {{ props.title }}
    </Button>
</template>
