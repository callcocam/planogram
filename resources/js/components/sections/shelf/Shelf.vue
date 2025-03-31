<!-- Versão atualizada do Shelf.vue para drag and drop -->
<template>
    <div
        class="shelf group relative border border-gray-400 bg-gray-300"
        :style="shelfStyle"
        :data-shelf-id="shelf.id"
        @click.stop="$emit('click', shelf)"
    >
        <!-- Conteúdo da prateleira -->
        <div class="shelf-content shelf-drag-handle flex h-full w-full items-center justify-center" :class="{ 'has-products': hasProducts }">
            <!-- Indicador de produtos -->
            <div v-if="hasProducts" class="product-indicator flex items-center">
                <PackageIcon class="h-3 w-3 text-primary" />
                <span class="ml-1 text-xs font-medium text-gray-700">{{ productsCount }}</span>
            </div>
            <div v-else class="shelf-empty text-xs text-gray-500">
                Vazia - {{ shelf.ordering }}
                <span class="ml-2 text-xs opacity-50">({{ shelf.shelf_position.toFixed(1) }}cm)</span>
            </div>
        </div>

        <!-- Posição em cm da prateleira -->
        <div class="shelf-position absolute -right-12 bottom-0 top-0 flex items-center text-xs text-gray-500">
            {{ shelf.shelf_position.toFixed(1) }}cm
        </div>
    </div>
</template>

<script setup>
import { PackageIcon } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps({
    shelf: {
        type: Object,
        required: true,
    },
    scaleFactor: {
        type: Number,
        required: true,
    },
    sectionWidth: {
        type: Number,
        required: true,
    },
    sectionHeight: {
        type: Number,
        required: true,
    },
    baseHeight: {
        type: Number,
        required: true,
    },
    numberOfShelves: {
        type: Number,
        default: 5,
    },
    currentIndex: {
        type: Number,
        default: 0,
    },
    holes: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['click']);

// Computed property para calcular o estilo da prateleira, incluindo posicionamento
const shelfStyle = computed(() => {
    // Convertemos a posição da prateleira para pixels usando o fator de escala
    const topPosition = props.shelf.shelf_position * props.scaleFactor;

    // Retornamos o estilo final
    return {
        position: 'absolute',
        left: '0',
        width: `${props.sectionWidth * props.scaleFactor}px`,
        height: `${props.shelf.shelf_height * props.scaleFactor}px`,
        top: `${topPosition}px`,
        zIndex: '1',
    };
});

// Verifica se a prateleira tem produtos
const hasProducts = computed(() => {
    return props.shelf.products && props.shelf.products.length > 0;
});

// Conta o total de produtos na prateleira (somando quantidades)
const productsCount = computed(() => {
    if (!props.shelf.products) return 0;

    return props.shelf.products.reduce((acc, product) => {
        return acc + (product.quantity || 1);
    }, 0);
});
</script>

<style scoped>
.shelf {
    transition: all 0.2s ease;
    cursor: grab;
}

.shelf:hover {
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
    z-index: 2;
}

.shelf:active {
    cursor: grabbing;
}

.shelf-content {
    transition: background-color 0.2s ease;
}

.shelf-content.has-products {
    background-color: rgba(79, 70, 229, 0.1);
}

.product-indicator {
    padding: 2px 4px;
    border-radius: 4px;
    background-color: rgba(255, 255, 255, 0.7);
}

/* Estilos para alça de arraste */
.shelf-drag-handle {
    z-index: 3;
    cursor: grab;
}

.shelf-drag-handle:active {
    cursor: grabbing;
}

:global(.dark) .shelf-drag-handle {
    background-color: rgba(0, 0, 0, 0.5);
}

/* Estilo para tema escuro */
:global(.dark) .shelf {
    background-color: #374151;
    border-color: #4b5563;
}

:global(.dark) .shelf-content.has-products {
    background-color: rgba(79, 70, 229, 0.2);
}

:global(.dark) .product-indicator {
    background-color: rgba(0, 0, 0, 0.3);
}
</style>
