<script setup>
const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: "",
    },
});
</script>

<template>
    <header>
        <h3 class="mb-0.5 text-base font-medium">{{ props.title }}</h3>
        <p v-if="props.description" class="text-sm text-muted-foreground">
            {{ props.description }}
        </p>
    </header>
</template>
