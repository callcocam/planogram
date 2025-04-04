<!-- Versão atualizada do Shelf.vue com ghost melhorado -->
<template>
    <div
        class="shelf relative flex items-end justify-around border-y border-gray-400 bg-gray-700 text-gray-50 dark:bg-gray-800"
        :style="shelfStyle"
        :data-shelf-id="shelf.id"
        @drop.prevent="onDrop"
        ref="shelfRef"
    >
        <!-- Implementação do draggable para os segmentos -->
        <draggable
            v-model="sortableSegments"
            item-key="id"
            handle=".drag-segment-handle"
            @change="onSegmentDragChange"
            class="relative flex w-full items-end justify-around"
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
            @dragenter="onDragEnter"
            @dragleave="onDragLeave"
        >
            <div class="absolute inset-0 flex items-center justify-center" v-if="draggingProduct">
                <span class="text-lg text-gray-100">Solte aqui</span>
            </div>
        </div>
    </div>

    <!-- Template oculto para o ghost -->
    <div ref="ghostTemplate" class="hidden">
        <div class="shelf-ghost rounded-md border border-gray-400 bg-gray-700 text-gray-50 dark:bg-gray-800">
            <!-- Simulação dos segmentos para o ghost -->
            <div class="flex w-full items-end justify-around">
                <template v-for="segment in sortableSegments" :key="segment.id">
                    <div
                        class="segment-ghost"
                        :style="{
                            width: `${segment.width * scaleFactor}px`,
                            height: `${segment.layer?.height * scaleFactor || 40}px`,
                            backgroundColor: getRandomColor(segment.id),
                        }"
                    ></div>
                </template>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
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
const ghostTemplate = ref(null);
const ghost = ref(null);

// Função para gerar cores aleatórias consistentes baseadas no ID
function getRandomColor(id) {
    // Converte o ID em um número para usar como seed
    let seed = 0;
    if (typeof id === 'string') {
        for (let i = 0; i < id.length; i++) {
            seed += id.charCodeAt(i);
        }
    } else if (typeof id === 'number') {
        seed = id;
    }

    // Usa o seed para gerar uma cor HSL com saturação e luminosidade controladas
    const hue = (seed * 137.5) % 360;
    return `hsl(${hue}, 70%, 60%)`;
}

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
        left: '-4px',
        width: `${props.sectionWidth * props.scaleFactor+4}px`,
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
    console.log(`Segmento ${eventData.segment.id} ${eventData.action}`);
};

// Função para lidar com alterações no arrasto de segmentos
const onSegmentDragChange = (event) => {
    console.log('Evento de drag change:', event);

    // Verificar se houve alguma alteração (moved, added ou removed)
    const hasChanged = event.moved || event.added || event.removed;

    if (!hasChanged) {
        console.log('Sem alteração na ordem');
        return;
    }

    // Informações de debug sobre a alteração
    if (event.moved) {
        console.log(`Movido de ${event.moved.oldIndex} para ${event.moved.newIndex}`);
    } else if (event.added) {
        console.log(`Adicionado em ${event.added.newIndex}`);
    } else if (event.removed) {
        console.log(`Removido de ${event.removed.oldIndex}`);
    }

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
                ordering: (sortableSegments.value.length || 0) + 1, // Garante que fique no final
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

// Criar ghost baseado no estado atual da prateleira
const createShelfGhost = () => {
    // Verifica se já existe um ghost e remove
    if (ghost.value) {
        document.body.removeChild(ghost.value);
        ghost.value = null;
    }

    // Cria um clone visual da prateleira para o ghost
    const ghostEl = document.createElement('div');
    ghostEl.className = 'shelf-drag-ghost';

    // Definir dimensões e estilos
    ghostEl.style.width = `${props.sectionWidth * props.scaleFactor}px`;
    ghostEl.style.height = `${props.shelf.shelf_height * props.scaleFactor}px`;
    ghostEl.style.backgroundColor = 'rgba(55, 65, 81, 0.9)'; // Cor similar à prateleira
    ghostEl.style.borderRadius = '4px';
    ghostEl.style.border = '1px solid rgba(75, 85, 99, 0.8)';
    ghostEl.style.position = 'absolute';
    ghostEl.style.zIndex = '999';
    ghostEl.style.pointerEvents = 'none';
    ghostEl.style.boxShadow = '0 4px 8px rgba(0, 0, 0, 0.3)';
    ghostEl.style.overflow = 'hidden';

    // Adicionar representação visual dos segmentos
    const segmentsContainer = document.createElement('div');
    segmentsContainer.className = 'flex w-full h-full items-end justify-around';
    segmentsContainer.style.padding = '2px';

    // Adiciona representações dos segmentos
    sortableSegments.value.forEach((segment) => {
        const segmentEl = document.createElement('div');

        // Calcula a largura e altura do segmento
        const width = segment.width * props.scaleFactor;
        const height = (segment.layer?.height || 40) * props.scaleFactor;

        // Define os estilos do segmento
        segmentEl.style.width = `${width}px`;
        segmentEl.style.height = `${height}px`;
        segmentEl.style.backgroundColor = getRandomColor(segment.id);
        segmentEl.style.margin = '0 2px';
        segmentEl.style.borderRadius = '2px';

        segmentsContainer.appendChild(segmentEl);
    });

    ghostEl.appendChild(segmentsContainer);

    // Adiciona ao body
    document.body.appendChild(ghostEl);
    ghost.value = ghostEl;

    return ghostEl;
};

// Função chamada quando o drag começa
const onDragstart = (event) => {
    event.target.style.opacity = '0.2'; // Diminui a opacidade da prateleira arrastada
    event.target.style.zIndex = '10'; // Aumenta o z-index da prateleira arrastada
    event.dataTransfer.setData('text/shelf', JSON.stringify(props.shelf));

    // Cria um ghost mais avançado que se parece com a prateleira
    const newGhost = createShelfGhost();

    // Define offset de arraste (para centralizar no cursor)
    const offsetX = (props.sectionWidth * props.scaleFactor) / 2;
    const offsetY = 20; // Um pouco acima do cursor

    // Define o ghost como imagem de arraste
    event.dataTransfer.setDragImage(newGhost, offsetX, offsetY);

    console.log('Iniciando arraste da prateleira:', ghost.value);
};

// Função chamada quando o drag termina
const onDragend = (event) => {
    draggingProduct.value = false; // Restaura o estado de arraste
    event.dataTransfer.clearData();
    event.target.style.opacity = '1'; // Restaura a opacidade da prateleira
    event.target.style.zIndex = '1'; // Restaura o z-index da prateleira

    // Limpa o ghost após um pequeno delay (para evitar flicker)
    setTimeout(() => {
        if (ghost.value) {
            document.body.removeChild(ghost.value);
            ghost.value = null;
        }
    }, 50);

    const shelf = props.shelf;
    // Pegar a posição da prateleira
    const position = event.target.getBoundingClientRect();
    const shelfPosition = {
        left: position.left,
        top: position.top,
        width: position.width,
        height: position.height,
    };

    // Atualiza a posição da prateleira se necessário
    // shelf.shelf_position = shelfPosition.top;
};

// Limpar recursos quando o componente é desmontado
onMounted(() => {
    // Se quiser usar a versão html2canvas para o ghost,
    // pode adicionar código aqui para pré-renderizar a prateleira
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

.drag-handle {
    cursor: move;
}

:global(.dark) .shelf-drag-handle {
    background-color: rgba(0, 0, 0, 0.5);
}

/* Estilo para o ghost da prateleira */
:global(.shelf-drag-ghost) {
    animation: ghost-appear 0.2s ease;
    opacity: 0.85;
    filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.4));
}

@keyframes ghost-appear {
    from {
        opacity: 0.4;
        transform: scale(0.95);
    }
    to {
        opacity: 0.85;
        transform: scale(1);
    }
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

:global(.dark) .shelf-drag-ghost {
    background-color: rgba(30, 41, 59, 0.9);
    border-color: #4b5563;
}
</style>
