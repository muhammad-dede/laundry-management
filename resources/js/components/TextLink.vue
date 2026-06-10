<script setup>
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    href: String,
    tabindex: Number,
    method: String,
    as: String,
});
</script>

<template>
    <Link
        :href="props.href"
        :tabindex="props.tabindex"
        :method="props.method"
        :as="props.as"
        class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
    >
        <slot />
    </Link>
</template>
