<template>
     <div :style="sectionStyle">
         {{ section.name }}
     </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import Shelf from './Shelf.vue';

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
});

const emit = defineEmits(['select-shelf', 'add-shelf']);

const gondola = ref<any>(props.section.gondola);

const sectionStyle = computed(() => {
    return {
        width: `${props.section.width * props.scaleFactor}px`,
        height: `${props.section.height * props.scaleFactor}px`, 
        position: 'relative' as const,
        overflow: 'hidden',
    };
});

// Altura da base em pixels
const baseHeight = computed(() => {
    const baseHeightCm = props.section.gondola.base_height || 0;
    if (baseHeightCm <= 0) return 0;
    return baseHeightCm * props.scaleFactor;
});
</script>

<style scoped>
.section {
    transition: all 0.2s ease;
}

.section:hover {
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
}

/* Estilo para tema escuro */
:global(.dark) .section {
    background-color: #1f2937;
    border-color: #374151;
}

:global(.dark) .section-header {
    background-color: #111827;
    border-color: #374151;
}

:global(.dark) .section-base {
    background-color: #374151;
    border-color: #4b5563;
}

:global(.dark) .empty-shelves-indicator button {
    background-color: #374151;
    border-color: #4b5563;
}

:global(.dark) .empty-shelves-indicator button:hover {
    background-color: #4b5563;
}
</style>
