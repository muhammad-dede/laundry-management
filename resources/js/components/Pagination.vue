<script setup>
import { Link } from "@inertiajs/vue3";
import { ChevronLeft, ChevronRight } from "lucide-vue-next";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";

const props = defineProps({
    perPages: {
        type: Array,
        default: [5, 10, 25, 50, 100],
    },
    modelValue: {
        type: Number,
        required: true,
    },
    pagination: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["update:modelValue"]);
</script>

<template>
    <div
        class="w-full flex flex-col items-center gap-4 lg:justify-between lg:flex-row-reverse lg:gap-2"
    >
        <div class="flex items-center gap-2">
            <template
                v-for="(link, index) in props.pagination.links"
                :key="index"
            >
                <template
                    v-if="
                        link.label?.includes('Previous') ||
                        link.label?.includes('Sebelumnya')
                    "
                >
                    <component
                        :is="link.url ? Link : 'div'"
                        :class="[
                            'size-8 flex items-center justify-center',
                            link.url ? 'text-primary' : 'text-muted-foreground',
                        ]"
                        :href="link.url ?? ''"
                        preserve-scroll
                    >
                        <ChevronLeft class="size-5" />
                    </component>
                </template>
                <template
                    v-else-if="
                        link.label?.includes('Next') ||
                        link.label?.includes('Selanjutnya')
                    "
                >
                    <component
                        :is="link.url ? Link : 'div'"
                        :class="[
                            'size-8 flex items-center justify-center',
                            link.url ? 'text-primary' : 'text-muted-foreground',
                        ]"
                        :href="link.url ?? ''"
                        preserve-scroll
                    >
                        <ChevronRight class="size-5" />
                    </component>
                </template>
                <template v-else>
                    <component
                        :is="link.active ? 'div' : Link"
                        :class="[
                            'size-8 flex items-center justify-center rounded-lg text-sm',
                            link.active
                                ? 'bg-primary text-primary-foreground cursor-default'
                                : 'text-foreground',
                        ]"
                        :href="link.url ?? '#'"
                        preserve-scroll
                    >
                        {{ link.label }}
                    </component>
                </template>
            </template>
        </div>
        <div
            class="flex items-center gap-2 flex-col-reverse lg:flex-row lg:gap-4"
        >
            <div class="inline-flex items-center gap-2">
                <span class="text-base text-muted-foreground">
                    Row per page
                </span>
                <div class="bg-card rounded-lg">
                    <Select
                        :model-value="modelValue"
                        @update:model-value="emit('update:modelValue', $event)"
                    >
                        <SelectTrigger class="h-8 px-2 gap-1">
                            <SelectValue placeholder="Pilih" />
                        </SelectTrigger>
                        <SelectContent class="min-w-fit">
                            <SelectItem
                                v-for="item in perPages"
                                :key="item"
                                :value="item"
                            >
                                {{ item }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>
            <div class="text-muted-foreground text-base">
                Menampilkan
                <span class="font-medium">
                    {{ props.pagination.from ? props.pagination.from : 0 }}
                </span>
                -
                <span class="font-medium">
                    {{ props.pagination.to ? props.pagination.to : 0 }}
                </span>
                dari
                <span class="font-medium">
                    {{ props.pagination.total }}
                </span>
            </div>
        </div>
    </div>
</template>
