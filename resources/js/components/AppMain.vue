<script setup>
import { cn } from "@/lib/utils";

const props = defineProps({
    class: { type: null, required: false },
});
</script>

<template>
    <div
        :class="
            cn('flex h-full flex-1 flex-col gap-4 rounded-xl p-4', props.class)
        "
    >
        <slot />
    </div>
</template>
