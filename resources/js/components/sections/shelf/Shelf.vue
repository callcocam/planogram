<!-- Versão atualizada do Shelf.vue para drag and drop -->
<template>
    <div
        class="shelf group relative flex items-end justify-around border border-gray-400 bg-gray-700 text-gray-50 dark:bg-gray-800"
        :style="shelfStyle"
        :data-shelf-id="shelf.id"
        @click.stop="$emit('click', shelf)"
        ref="shelfRef"
    >
        <Segment
            v-for="(segment, index) in shelf.segments"
            :key="index"
            :shelf="shelf"
            :segment="segment"
            :scale-factor="scaleFactor"
            :selected-category="selectedCategory"
            @segment-select="$emit('segment-select', $event)"
        />
        <!-- Area de soltar produto -->
        <div
            class="absolute inset-0 bottom-0 z-0 rounded-md bg-gray-200/25"
            :style="dragStyle"
            draggable="true"
            @dragstart="onDragstart"
            @dragend="onDragend"
            @drop.prevent="onDrop"
            @dragenter="onDragEnter"
            @dragleave="onDragLeave"
        >
            <div class="absolute inset-0 flex items-center justify-center" v-if="draggingProduct">
                <span class="text-lg text-gray-100">Solte aqui</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import Segment from './segment/Segment.vue';

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
    selectedCategory: {
        type: [Object, null],
        default: null,
    },
    holes: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['click', 'drop-product', 'segment-select']);

const draggingProduct = ref(false);

const shelfRef = ref(null);

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

const dragStyle = computed(() => {
    if (draggingProduct.value) {
        return {
            width: `${props.sectionWidth * props.scaleFactor}px`,
            height: '44px', // Altura fixa para a área de soltar
            borderColor: 'rgba(59, 130, 246, 0.5)', // Cor da borda quando arrastando
            borderWidth: '2px',
            borderStyle: 'dashed',
            boxShadow: '0 0 0 2px rgba(59, 130, 246, 0.5)', // Sombra quando arrastando
            transition: 'all 0.2s ease',
            transform: 'translateY(-50%)', // Move a área de soltar para cima
            zIndex: '10', // Aumenta o z-index quando arrastando
        };
    }
    return {
        width: `${props.sectionWidth * props.scaleFactor}px`,
        height: `${props.shelf.shelf_height * props.scaleFactor}px`,
        backgroundColor: 'transparent', // Cor de fundo padrão
    };
});

// Função chamada quando o produto é solto na prateleira
const onDrop = (event) => {
    draggingProduct.value = false; // Restaura o estado de arraste
    const productData = event.dataTransfer.getData('text/product');
    if (productData) {
        const product = JSON.parse(productData);
        // Verifica se o produto é válido
        if (product && product.id) {
            const newSegment = {
                width: parseInt(props.sectionWidth),
                ordering: (props.shelf.segments?.length || 0) + 1,
                quantity: 1,
                spacing: 0,
                position: 0,
                preserveState: false,
                status: 'published',
                // Create layer with product information
                layer: {
                    id: `layer-${Date.now()}`,
                    product_id: product.id,
                    product_name: product.name,
                    product_image: product.image,
                    height: product.height,
                    spacing: 0,
                    quantity: 1,
                    status: 'published',
                },
            };

            // Update the shelf with the new segment
            const updatedShelf = {
                ...props.shelf,
                segment: newSegment,
            };
            // Emite o evento com o produto e a prateleira
            emit('drop-product', updatedShelf);
        }
    }
};

// Função chamada quando o mouse entra na área de soltar
const onDragEnter = (event) => {
    if (event.dataTransfer.types.includes('text/product')) {
        draggingProduct.value = true; // Define o estado de arraste
        event.preventDefault(); // Previne o comportamento padrão
        event.target.style.borderColor = 'rgba(59, 130, 246, 0.5)'; // Altera a cor da borda
    }
};
// Função chamada quando o mouse sai da área de soltar
const onDragLeave = (event) => {
    if (!shelfRef.value.contains(event.relatedTarget)) {
        draggingProduct.value = false; // Restaura o estado de arraste
        event.target.style.borderColor = 'transparent'; // Restaura a cor da borda
    }
};

// Função chamada quando o drag começa
const onDragstart = (event) => {
    event.target.style.opacity = '0.2'; // Diminui a opacidade da prateleira arrastada
    event.target.style.zIndex = '10'; // Aumenta o z-index da prateleira arrastada
    event.dataTransfer.setData('text/shelf', JSON.stringify(props.shelf));
    // Vamos criar um ghost para o arraste da prateleira
    const ghost = document.createElement('div');
    ghost.className = 'shelf-drag-handle';
    ghost.style.width = `${props.sectionWidth * props.scaleFactor}px`;
    ghost.style.height = `${props.shelf.shelf_height * props.scaleFactor}px`;
    ghost.style.backgroundColor = 'rgba(59, 130, 246, 0.5)';
    ghost.style.position = 'absolute';
    ghost.style.top = '0';
    ghost.style.left = '-50%';
    ghost.style.zIndex = '10';
    ghost.style.pointerEvents = 'none'; // Desabilita eventos de ponteiro
    ghost.style.opacity = '0.5'; // Diminui a opacidade do ghost
    ghost.style.borderRadius = '8px'; // Adiciona borda arredondada
    ghost.style.boxShadow = '0 0 10px rgba(59, 130, 246, 0.5)'; // Adiciona sombra
    ghost.style.transition = 'all 0.2s ease'; // Adiciona transição suave
    document.body.appendChild(ghost); // Adiciona o ghost ao body
    event.dataTransfer.setDragImage(ghost, 0, 0); // Define o ghost como imagem de arraste
    setTimeout(() => {
        ghost.remove(); // Remove o ghost após o arraste
    }, 0);
};
// Função chamada quando o drag termina
const onDragend = (event) => {
    draggingProduct.value = false; // Restaura o estado de arraste
    event.dataTransfer.clearData();
    event.target.style.opacity = '1'; // Restaura a opacidade da prateleira
    event.target.style.zIndex = '1'; // Restaura o z-index da prateleira
    const shelf = props.shelf;
    //pegar a posição da prateleira
    const position = event.target.getBoundingClientRect();
    const shelfPosition = {
        left: position.left,
        top: position.top,
        width: position.width,
        height: position.height,
    };

    // Atualiza a posição da prateleira
    shelf.shelf_position = shelfPosition.top;
};
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
