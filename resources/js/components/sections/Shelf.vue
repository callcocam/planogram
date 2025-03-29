<template>
    <div 
        class="shelf relative bg-gray-300 border border-gray-400"
        :style="shelfStyle"
        @click.stop="$emit('click', shelf)"
    >
        <!-- Conteúdo da prateleira -->
        <div class="shelf-content h-full w-full flex items-center justify-center" :class="{ 'has-products': hasProducts }">
            <!-- Indicador de produtos -->
            <div v-if="hasProducts" class="product-indicator flex items-center">
                <PackageIcon class="h-3 w-3 text-primary" />
                <span class="text-xs ml-1 font-medium text-gray-700">{{ productsCount }}</span>
            </div>
            <div v-else class="shelf-empty text-xs text-gray-500">Vazia</div>
        </div>
        
        <!-- Posição em cm da prateleira -->
        <div class="shelf-position absolute -right-12 top-0 bottom-0 flex items-center text-xs text-gray-500">
            {{ shelf.position }}cm
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
    }
});

const emit = defineEmits(['click']);

// Estilo da prateleira
const shelfStyle = computed(() => {
    return {
        position: 'absolute',
        left: '0',
        width: `${props.sectionWidth * props.scaleFactor}px`,
        height: `${(props.shelf.height || 2) * props.scaleFactor}px`,
        top: `${props.shelf.position * props.scaleFactor}px`,
    };
});

// Verifica se a prateleira tem produtos
const hasProducts = computed(() => {
    return props.shelf.products && props.shelf.products.length > 0;
});

// Conta quantos produtos tem na prateleira
const productsCount = computed(() => {
    if (!props.shelf.products) return 0;
    
    // Soma todas as quantidades de produtos
    return props.shelf.products.reduce((acc, product) => {
        return acc + (product.quantity || 1);
    }, 0);
});
</script>

<style scoped>
.shelf {
    transition: all 0.2s ease;
    z-index: 1;
}

.shelf:hover {
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
    z-index: 2;
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