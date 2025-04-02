<!-- Versão atualizada do Shelf.vue para suportar update de quantidade -->
<template>
    <div
        class="shelf relative flex items-end justify-around border border-gray-400 bg-gray-700 text-gray-50 dark:bg-gray-800"
        :style="shelfStyle"
        :data-shelf-id="shelf.id"
        ref="shelfRef"
    >
        <!-- Implementação do draggable para os segmentos -->
        <draggable
            v-model="sortableSegments"
            item-key="id"
            handle=".drag-handle"
            @end="onSegmentDragEnd"
            class="relative flex w-full items-end justify-between px-4"
            :style="segmentsContainerStyle"
        >
            <template #item="{ element: segment }">
                <Segment
                    :key="segment.id"
                    :shelf="shelf"
                    :segment="segment"
                    :scale-factor="scaleFactor"
                    :selected-category="selectedCategory"
                    @segment-select="$emit('segment-select', $event)"
                    @segment-drag="handleSegmentDrag"
                    @update:quantity="$emit('update:quantity', $event)"
                />
            </template>
        </draggable>

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
import draggable from 'vuedraggable';
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

const emit = defineEmits(['click', 'drop:product', 'segment-select', 'update:segments', 'update:quantity']);

const draggingProduct = ref(false);
const shelfRef = ref(null);

// Garantir que todos os segmentos tenham IDs
const ensureSegmentIds = (segments) => {
    return segments.map((segment, index) => ({
        ...segment,
        id: segment.id || `segment-${Date.now()}-${index}`,
    }));
};

// Referência local aos segmentos para o draggable
const sortableSegments = computed({
    get() {
        // Garantir que todos os segmentos tenham IDs
        return ensureSegmentIds(props.shelf.segments || []);
    },
    set(newSegments) {
        // Garantir que a ordenação está atualizada antes de emitir o evento
        const reorderedSegments = newSegments.map((segment, index) => ({
            ...segment,
            ordering: index + 1,
        }));

        // Emitir o evento para atualizar os segmentos no componente pai
        emit('update:segments', {
            shelfId: props.shelf.id,
            segments: reorderedSegments,
        });
    },
});

// Computed property para estilo do container de segmentos
const segmentsContainerStyle = computed(() => {
    return {
        height: `${props.shelf.shelf_height * props.scaleFactor}px`,
    };
});

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

// Tratar eventos de arrasto do segmento
const handleSegmentDrag = (eventData) => {
    // Você pode usar isso para rastreamento ou lógica adicional
    // console.log(`Segmento ${eventData.segment.id} ${eventData.action}`);
};
 

// Função para lidar com o fim do arraste de segmentos
const onSegmentDragEnd = (event) => {
    // Se não houver mudança na ordem, não fazemos nada
    if (!event.moved) {
        console.log('Sem alteração na ordem');
        return;
    }

    // Aqui temos certeza que a ordem mudou
    const oldIndex = event.moved.oldIndex;
    const newIndex = event.moved.newIndex;
    console.log(`Movido de ${oldIndex} para ${newIndex}`);

    // A reordenação já foi aplicada ao v-model (sortableSegments),
    // mas precisamos garantir que o campo ordering reflita a nova ordem
    const reorderedSegments = sortableSegments.value.map((segment, index) => ({
        ...segment,
        ordering: index + 1,
    }));

    // Emitir evento com os segmentos reordenados
    emit('update:segments', {
        shelfId: props.shelf.id,
        segments: reorderedSegments,
    });
};

// Função chamada quando o produto é solto na prateleira
const onDrop = (event) => {
    draggingProduct.value = false; // Restaura o estado de arraste

    // Verificar se temos um produto ou um segmento sendo solto
    const productData = event.dataTransfer.getData('text/product');
    const segmentData = event.dataTransfer.getData('text/segment');
    if (productData) {
        handleProductDrop(productData);
    } else if (segmentData) {
        handleSegmentDrop(segmentData);
    }
};

// Função para lidar com a soltura de um produto
const handleProductDrop = (productData) => {
    try {
        const product = JSON.parse(productData);
        // Verifica se o produto é válido
        if (product && product.id) {
            const newSegment = {
                width: parseInt(props.sectionWidth),
                ordering: (sortableSegments.value.length || 0) + 1,
                quantity: 1,
                spacing: 0,
                position: 0,
                preserveState: false,
                status: 'published',
                // Create layer with product information
                layer: {
                    product_id: product.id,
                    product_name: product.name,
                    product_image: product.image,
                    product: product,
                    height: product.height,
                    spacing: 0,
                    quantity: 1,
                    status: 'published',
                },
            };

            // Emite o evento com os segmentos atualizados
            emit('drop:product', {
                ...props.shelf,
                segment: newSegment,
            });
        }
    } catch (error) {
        console.error('Erro ao processar produto:', error);
    }
};

// Função para lidar com a soltura de um segmento
const handleSegmentDrop = (segmentData) => {
    try {
        const segment = JSON.parse(segmentData);
        // Verificar se o segmento já existe nesta prateleira
        const existingIndex = sortableSegments.value.findIndex((s) => s.id === segment.id);

        if (existingIndex >= 0) {
            // O segmento já existe, atualizar apenas sua ordem
            const updatedSegments = [...sortableSegments.value];
            updatedSegments[existingIndex] = {
                ...segment,
                ordering: existingIndex + 1,
            };

            // Reordena todos os segmentos corretamente
            const reorderedSegments = updatedSegments.map((seg, idx) => ({
                ...seg,
                ordering: idx + 1,
            }));

            emit('update:segments', {
                shelfId: props.shelf.id,
                segments: reorderedSegments,
            });
        } else { 
            const newSegment = {
                id: segment.id,
                width: parseInt(segment.width),
                ordering: segment.ordering,
                quantity: segment.quantity,
                spacing: segment.spacing,
                position: segment.position,
                preserveState: false,
                status: 'published',
                // Create layer with product information
                layer: segment.layer,
            };
           
            emit('drop:product', {
                ...props.shelf,
                segment: newSegment,
            });
        }
    } catch (error) {
        console.error('Erro ao processar segmento:', error);
    }
};

// Função chamada quando o mouse entra na área de soltar
const onDragEnter = (event) => {
    if (event.dataTransfer.types.includes('text/product') || event.dataTransfer.types.includes('text/segment')) {
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

.drag-handle {
    cursor: move;
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
