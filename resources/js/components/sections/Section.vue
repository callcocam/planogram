<template>
    <div :style="sectionStyle" @drop.prevent="onDrop" @dragover.prevent="onDragOver" :data-section-id="section.id" @dragleave="onDragleave">
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

const onDrop = (event: DragEvent) => {
    const jsonDataShelf = event.dataTransfer?.getData('text/shelf') as any; 
    if (jsonDataShelf) {
        try {
            const data = JSON.parse(jsonDataShelf); 
            // Obter a posição do mouse em relação à janela
            const mouseX = event.clientX;
            const mouseY = event.clientY;

            // Obter as coordenadas da seção
            const target = event.currentTarget as HTMLElement;
            const sectionRect = target.getBoundingClientRect();

            // Calcular a posição relativa do mouse dentro da seção
            const relativeX = mouseX - sectionRect.left;
            const relativeY = mouseY - sectionRect.top;

            // Converter para a posição em cm (dividindo pelo fator de escala)
            const positionXInCm = relativeX / props.scaleFactor;
            const positionYInCm = relativeY / props.scaleFactor;

            // Adicionar a posição ao objeto da prateleira
            const shelf = {
                ...data,
                section_id: props.section.id,
                shelf_position: positionYInCm,
            };

            // Emitir evento para mover a prateleira para a seção
            emit('move-shelf-to-section', shelf, props.section.id);
        } catch (error) {
            console.error('Erro ao processar dados do drop:', error);
        }
    }

    // Remove a classe CSS quando o mouse sai da seção
    const target = event.currentTarget as HTMLElement;
    target.classList.remove('drag-over');
};
/**
 * Gerencia o evento quando um item está sendo arrastado sobre a seção
 * @param event - O evento de arrastar (DragEvent)
 */
const onDragOver = (event: DragEvent) => {
    // Previne o comportamento padrão para permitir o drop
    event.preventDefault();

    // Verifica se dataTransfer está disponível
    if (!event.dataTransfer) return;

    // Obtém o elemento alvo (a seção)
    const target = event.currentTarget as HTMLElement;

    // Se já tiver a classe drag-over, não faz nada
    if (target.classList.contains('drag-over')) return;

    // Obtém a posição do mouse e do elemento para cálculos futuros
    const mouseY = event.clientY;
    const targetRect = target.getBoundingClientRect();

    // Registra informações para depuração
    // console.log('Posição da seção:', targetRect);
    // console.log('Evento de arrastar ativado na posição Y:', mouseY);

    // Adiciona a classe CSS para feedback visual
    target.classList.add('drag-over');
};

/**
 * Gerencia o evento quando o cursor sai da área da seção durante o arrasto
 * @param event - O evento de mouse (MouseEvent)
 */
const onDragleave = (event: MouseEvent) => {
    // Obtém o elemento alvo (a seção)
    const target = event.currentTarget as HTMLElement;

    // Remove a classe CSS de feedback visual
    target.classList.remove('drag-over');
};
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

.drag-over {
    background-color: rgba(59, 130, 246, 0.1);
    border: 2px dashed rgba(59, 130, 246, 0.5);
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
