<template>
    <div
        :style="sectionStyle"
        @drop.prevent="onDrop"
        @dragover.prevent="onDragOver"
        :data-section-id="section.id"
        @dragleave="onDragleave"
        @dragenter="onDragEnter"
    >
        <Shelf
            v-for="(shelf, index) in sortableShelves"
            :shelf="shelf"
            :scaleFactor="props.scaleFactor"
            :sectionWidth="props.section.width"
            :sectionHeight="props.section.height"
            :baseHeight="baseHeight"
            :numberOfShelves="sortableShelves.length"
            :currentIndex="index"
            :selected-category="selectedCategory"
            :holes="holes"
            @drop:product="onDropProduct"
            @click="$emit('select-shelf', shelf)"
            @segment-select="$emit('segment-select', $event)"
            @update:quantity="$emit('update:quantity', $event)"
            @update:segments="$emit('update:segments', $event)"
        >
        </Shelf>
    </div>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import Shelf from './shelf/Shelf.vue';

// Define the Category interface to match what Shelf component expects
interface Category {
    id: string | number;
    name: string;
}

const props = defineProps({
    section: {
        type: Object,
        required: true,
    },
    selectedCategory: {
        type: Object as () => Category | null,
        default: null,
    },
    showGrid: {
        type: Boolean,
        default: true,
    },
    scaleFactor: {
        type: Number,
        default: 1,
    },
});

const emit = defineEmits([
    'select-shelf',
    'add-shelf',
    'update-shelves',
    'move-shelf-to-section',
    'segment-select',
    'update:quantity',
    'update:segments',
]);

// Criar ref para prateleiras que podemos modificar com draggable
const sortableShelves = ref<any[]>([]);

const draggingSection = ref(false);
 

const holes = computed(() => {
    return props.section.settings.holes || [];
});

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
        borderWidth: '2px',
        borderStyle: draggingSection.value ? 'dashed' : 'solid',
        borderColor: draggingSection.value ? 'rgba(59, 130, 246, 0.5)' : 'transparent',
        backgroundColor: draggingSection.value ? 'rgba(59, 130, 246, 0.1)' : 'transparent',
        overflow: 'visible' as const,
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
    // const target = event.currentTarget as HTMLElement;
    draggingSection.value = false;
};

/**
 * Gerencia o evento quando um item é arrastado para a seção
 * @param event - O evento de arrastar (DragEvent)
 */
const onDragEnter = (event: DragEvent) => {
    // Previne o comportamento padrão para permitir o drop
    event.preventDefault();
    if (event.dataTransfer?.types.includes('text/segment')) {
        return;
    }
    // Obtém o elemento alvo (a seção)
    const target = event.currentTarget as HTMLElement;

    // Se já tiver a classe drag-over, não faz nada
    if (target.classList.contains('drag-over')) return;
    if (draggingSection.value) return;
    // Verifica se dataTransfer está disponível
    draggingSection.value = true;

    // Adiciona a classe CSS para feedback visual
    // target.classList.add('drag-over');
    // Registra informações para depuração
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

    if (event.dataTransfer?.types.includes('text/segment')) {
        return;
    }
    // Obtém o elemento alvo (a seção)
    const target = event.currentTarget as HTMLElement;

    // Se já tiver a classe drag-over, não faz nada
    if (target.classList.contains('drag-over')) return;
    if (draggingSection.value) return;

    // Registra informações para depuração
    // console.log('Posição da seção:', targetRect);
    // console.log('Evento de arrastar ativado na posição Y:', mouseY);

    // Adiciona a classe CSS para feedback visual
    draggingSection.value = true;
    // target.classList.add('drag-over');
};

/**
 * Gerencia o evento quando o cursor sai da área da seção durante o arrasto
 * @param event - O evento de mouse (MouseEvent)
 */
const onDragleave = (event: MouseEvent) => {
    // Obtém o elemento alvo (a seção)
    const target = event.currentTarget as HTMLElement;
    // Remove a classe CSS de feedback visual
    // target.classList.remove('drag-over');
    draggingSection.value = false;
};

/**
 * Gerencia o evento de drop de um produto na seção
 * @param product - O produto a ser adicionado
 */
const onDropProduct = (data: any) => {
    // Adiciona o produto à seção
    console.log('Produto adicionado à seção:', data);
    // Remove a classe CSS quando o mouse sai da seção
    // const target = event.currentTarget as HTMLElement;
    draggingSection.value = false;
    // target.classList.remove('drag-over');
    router.put(
        // @ts-ignore
        route('planogram.shelves.update', data.id),
        data,
        {
            preserveState: false,
            preserveScroll: true,
            onSuccess: () => {
                // Handle success if needed
            },
            onError: () => {
                // Handle error if needed
            },
            onFinish: () => {
                // Reset the state if needed
            },
        },
    );
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
