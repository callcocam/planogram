<template>
    <div :style="sectionStyle">
        <Shelf
            v-for="(shelf, index) in sortableShelves"
            :shelf="shelf"
            :scaleFactor="props.scaleFactor"
            :sectionWidth="props.section.width"
            :sectionHeight="props.section.height"
            :baseHeight="baseHeight"
            :numberOfShelves="sortableShelves.length"
            :currentIndex="index"
            @click="$emit('select-shelf', shelf)"
        >
        </Shelf>
    </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import Shelf from './shelf/Shelf.vue';
// @ts-ignore

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

const emit = defineEmits(['select-shelf', 'add-shelf', 'update-shelves', 'move-shelf-to-section']);

const gondola = ref<any>(props.section.gondola);
console.log('gondola', props.section);

// Criar ref para prateleiras que podemos modificar com draggable
const sortableShelves = ref<any[]>([]);

// Inicializar e atualizar sortableShelves quando props.section.shelves mudar
watch(
    () => props.section.shelves,
    (newShelves) => {
        sortableShelves.value = [...(newShelves || [])];
    },
    { immediate: true, deep: true },
);

const isEmpty = computed(() => {
    return sortableShelves.value.length === 0;
});

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
    const baseHeightCm = props.section.base_height || 0;
    if (baseHeightCm <= 0) return 0;
    return baseHeightCm * props.scaleFactor;
});
</script>

<style scoped>
.empty-placeholder {
    background-color: rgba(229, 231, 235, 0.2);
    border: 1px dashed #d1d5db;
    border-radius: 4px;
    z-index: 0;
}

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

:global(.dark) .empty-placeholder {
    background-color: rgba(55, 65, 81, 0.2);
    border-color: #4b5563;
}
</style>
