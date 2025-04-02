<template>
    <div class="px-10">
        <Header
            :planogram="record"
            :show-gondola-modal-prop="showGondolaModal"
            @close="showGondolaModal = false"
            @gondola-added="showGondolaModal = false"
        />
        <div>
            <Gondolas :planogram="record" @add-gondola="openGondolaModal" />
        </div>
        <Toaster position="top-right" />
    </div>
</template>
<script setup lang="ts">
import { ref } from 'vue';
import Gondolas from './Gondolas.vue';
import Header from './Header.vue';
// @ts-ignore
import { Toaster } from '@/components/ui/toast'

const props = defineProps({
    record: {
        type: Object,
        required: true,
    },
});

const showGondolaModal = ref<any>(null);

function openGondolaModal() {
    showGondolaModal.value = true;
} 
</script>
