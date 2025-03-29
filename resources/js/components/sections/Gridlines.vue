<template>
    <div v-if="showGrid" class="grid-lines">
        <div
            v-for="i in gridLines"
            :key="i"
            class="section-grid-line"
            :style="{
                position: 'absolute',
                left: 0,
                right: 0,
                height: '1px',
                backgroundColor: 'rgba(200, 200, 200, 0.5)',
                top: `${i * gridSpacing}px`,
            }"
        ></div>
    </div>
</template>
<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps({
    section: {
        type: Object,
        required: true,
    },
    scaleFactor: {
        type: Number,
        required: true,
    },
    showGrid: {
        type: Boolean,
        default: true,
    },
    baseHeight: {
        type: Number,
        default: 0,
    },
});

// Configuração do grid visual para representar possíveis posições de prateleiras
const gridSpacing = computed(() => Math.floor(10 * props.scaleFactor)); // Espaçamento de 10cm
const gridLines = computed(() => {
    const availableHeight = props.section.height * props.scaleFactor - props.baseHeight - 6; // 6px do header
    return Math.floor(availableHeight / gridSpacing.value);
});
</script>
