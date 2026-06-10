<script setup>
import { Input } from "@/components/ui/input";
import { Search } from "lucide-vue-next";

const props = defineProps({
    modelValue: {
        type: String,
        default: "",
    },
    placeholder: {
        type: String,
        default: "Cari...",
    },
});

const emit = defineEmits(["update:modelValue"]);
</script>

<template>
    <div class="relative w-full">
        <label
            for="search"
            class="absolute start-0 inset-y-0 flex items-center justify-center pl-3 rounded-br-lg rounded-tr-lg"
        >
            <Search class="size-4 text-muted-foreground" />
        </label>
        <Input
            id="search"
            type="search"
            name="search"
            :placeholder="placeholder"
            class="pl-9"
            autocomplete="off"
            :model-value="modelValue"
            @input="emit('update:modelValue', $event.target.value)"
        />
    </div>
</template>
