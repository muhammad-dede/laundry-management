<script setup>
import { defineProps, ref, watch, onMounted, onUnmounted } from "vue";

const props = defineProps({
    type: {
        type: String,
        default: "success",
    },
    message: String,
    autoClose: {
        type: Boolean,
        default: true,
    },
    duration: {
        type: Number,
        default: 5000,
    },
});

const visible = ref(true);
const isLeaving = ref(false);
let timeout = null;

const close = () => {
    isLeaving.value = true;
    setTimeout(() => {
        visible.value = false;
    }, 300);
};

const resetFlashMessage = () => {
    visible.value = true;
    isLeaving.value = false;
    clearTimeout(timeout);

    if (props.autoClose) {
        timeout = setTimeout(close, props.duration);
    }
};

// Watch untuk message changes - reset ketika ada message baru
watch(
    () => props.message,
    (newMessage) => {
        if (newMessage) {
            resetFlashMessage();
        }
    },
    { immediate: true }
);

onMounted(() => {
    resetFlashMessage();
});

onUnmounted(() => {
    clearTimeout(timeout);
});
</script>

<template>
    <div
        v-if="visible && message"
        :class="[
            'fixed bottom-5 right-5 bg-white border rounded-xl shadow-lg p-4 z-50',
            isLeaving ? 'animate-fade-out' : 'animate-fade-in',
        ]"
    >
        <div class="flex gap-2 items-start">
            <svg
                v-if="type === 'success'"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                class="w-6 h-6 text-green-400"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                />
            </svg>
            <svg
                v-else
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                class="w-6 h-6 text-red-500"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 9v3m0 3h.01M12 6a9 9 0 1 0 0 18 9 9 0 0 0 0-18z"
                />
            </svg>

            <div class="w-xs text-sm flex flex-col gap-0.5">
                <span class="font-semibold">
                    {{ type === "success" ? "Berhasil!" : "Gagal!" }}
                </span>
                <span class="text-gray-500">{{ message }}</span>
            </div>

            <button @click="close">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                    aria-hidden="true"
                    class="w-4 h-4 text-gray-400 hover:text-gray-600"
                >
                    <path
                        d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"
                    />
                </svg>
            </button>
        </div>
    </div>
</template>

<style scoped>
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fade-out {
    from {
        opacity: 1;
        transform: translateY(0);
    }
    to {
        opacity: 0;
        transform: translateY(20px);
    }
}

.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}

.animate-fade-out {
    animation: fade-out 0.3s ease-out;
}
</style>
