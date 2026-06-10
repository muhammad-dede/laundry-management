<script setup>
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { useInitials } from "@/composables/useInitials";
import { computed } from "vue";

const props = defineProps({
    user: Object,
    showEmail: Boolean,
});

const showEmail = props.showEmail ?? false;
const { getInitials } = useInitials();

const showAvatar = computed(
    () => props.user.avatar && props.user.avatar !== ""
);
</script>

<template>
    <Avatar class="h-8 w-8 overflow-hidden rounded-lg">
        <AvatarImage
            v-if="showAvatar"
            :src="props.user.avatar"
            :alt="props.user.name"
        />
        <AvatarFallback class="rounded-lg text-black dark:text-white">
            {{ getInitials(props.user.name) }}
        </AvatarFallback>
    </Avatar>

    <div class="grid flex-1 text-left text-sm leading-tight">
        <span class="truncate font-medium">{{ props.user.name }}</span>
        <span
            v-if="props.showEmail"
            class="truncate text-xs text-muted-foreground"
            >{{ props.user.email }}</span
        >
    </div>
</template>
