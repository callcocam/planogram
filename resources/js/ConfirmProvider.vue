<template>
    <slot></slot>

    <!-- Modal de confirmação global -->
     <!-- @vue-ignore -->
    <ConfirmModal v-model:isOpen="confirmModal.isOpen" v-bind="confirmModal.config" @confirm="confirmModal.onConfirm" @cancel="confirmModal.onCancel">
        <template v-if="slots.content" #description>
            <slot name="content"></slot>
        </template>
    </ConfirmModal>
</template>

<script setup lang="ts">
import { provide, useSlots } from 'vue';
import ConfirmModal from './Confirm.vue';
import { useConfirm } from './composables/useConfirm';
// @ts-ignore
import { confirmKey } from './utils/injectionKeys';

const confirmModal = useConfirm();
const slots = useSlots();

// Forneça o composable para componentes filhos
provide(confirmKey, confirmModal);
</script>
