<!-- Versão TypeScript do Shelf.vue com tipagem completa -->
<template>
    <div
        class="shelf relative flex items-end justify-around border-y border-gray-400 bg-gray-700 text-gray-50 dark:bg-gray-800"
        :style="shelfStyle"
        @drop.prevent="onDrop"
        @click="shelfClick"
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
            class="select-shelf absolute inset-0 bottom-0 z-0"
            :class="{
                'border-2 border-dashed border-blue-500': shelfSelected,
            }"
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
                            height: `${segment.layer && segment.layer.height ? segment.layer.height * scaleFactor : 40}px`,
                            backgroundColor: getRandomColor(segment.id),
                        }"
                    ></div>
                </template>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';
import draggable from 'vuedraggable';
import Segment from './segment/Segment.vue';

/**
 * Interfaces para tipagem do componente
 */

// Interface para o produto
interface Product {
    id: string | number;
    name: string;
    image: string;
    height: number;
    [key: string]: any; // Para propriedades adicionais do produto
}

// Interface para a camada que representa o produto no segmento
interface Layer {
    product_id?: string | number;
    product_name?: string;
    product_image?: string;
    product?: Product;
    height: number;
    spacing: number;
    quantity: number;
    status: string;
    [key: string]: any; // Para propriedades adicionais da camada
}

// Interface para um segmento individual
interface Segment {
    id: string;
    width: number;
    ordering: number;
    quantity: number;
    spacing: number;
    position: number;
    preserveState: boolean;
    status: string;
    layer?: Layer;
    [key: string]: any; // Para propriedades adicionais do segmento
}

// Interface para uma prateleira
interface Shelf {
    id: string | number;
    shelf_height: number;
    shelf_position: number;
    segments: Segment[];
    [key: string]: any; // Para propriedades adicionais da prateleira
}

// Interface para a categoria
interface Category {
    id: string | number;
    name: string;
    [key: string]: any; // Para propriedades adicionais da categoria
}

// Interface para posição de furos
interface Hole {
    position: number;
    [key: string]: any; // Para propriedades adicionais dos furos
}

// Interface para eventos de draggable
interface DraggableEvent {
    moved?: {
        oldIndex: number;
        newIndex: number;
        element: Segment;
    };
    added?: {
        newIndex: number;
        element: Segment;
    };
    removed?: {
        oldIndex: number;
        element: Segment;
    };
}

// Props do componente
const props = defineProps({
    shelf: {
        type: Object as () => Shelf,
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
        type: Object as () => Category | null,
        default: null,
    },
    holes: {
        type: Array as () => Hole[],
        default: () => [],
    },
});

// Define os eventos emitidos pelo componente
const emit = defineEmits<{
    (e: 'click', shelf: Shelf): void;
    (e: 'drop:product', data: { shelf: Shelf; segment: Segment }): void;
    (e: 'segment-select', segmentId: string): void;
    (e: 'update:segments', data: { shelfId: string | number; segments: Segment[] }): void;
    (e: 'update:quantity', data: { segmentId: string; quantity: number }): void;
}>();

// Referências reativas
const draggingProduct = ref<boolean>(false);
const shelfRef = ref<HTMLElement | null>(null);
const ghostTemplate = ref<HTMLElement | null>(null);
const ghost = ref<HTMLElement | null>(null);
const shelfSelected = ref<Shelf | null>(null);
// Constante para o evento de seleção de prateleira
const SHELF_SELECTED_EVENT = 'shelf-selected';

/**
 * Função chamada quando a prateleira é clicada
 * @param event - Evento de clique
 */
const shelfClick = (event: MouseEvent): void => {
    // Prevent event propagation to parent elements
    event.stopPropagation();

    // Check if in multi-select mode (Ctrl/Cmd pressed)
    const isMultiSelect = event.ctrlKey || event.metaKey;

    shelfSelected.value = props.shelf; // Atualiza a prateleira selecionada
    // Emite o evento de clique na prateleira
    const shelfClickEvent = new CustomEvent(SHELF_SELECTED_EVENT, {
        detail: {
            selectedId: props.shelf.id,
            isMultiSelect,
        },
    });
    window.dispatchEvent(shelfClickEvent); // Dispara o evento de seleção
};

/**
 * Função para lidar com cliques no documento
 * Desmarca a prateleira selecionada quando clica fora dela
 * @param event - Evento de clique
 */
const handleDocumentClick = (event: MouseEvent): void => {
    // Se o clique não foi dentro desta prateleira, desmarca-a
    if (shelfRef.value && !shelfRef.value.contains(event.target as Node)) {
        if (shelfSelected.value && shelfSelected.value.id === props.shelf.id) {
            shelfSelected.value = null;
        }
        draggingProduct.value = false; // Restaura o estado de arraste
    }
};

/**
 * Gerencia a seleção de prateleiras
 * @param selectedId - ID do segmento selecionado
 * @param isMultiSelect - Indica se está em modo de seleção múltipla
 */
const handleShelfSelection = (selectedId: string, isMultiSelect: boolean): void => {
    // Implemente a lógica de seleção conforme necessário
    // Esta função está apenas definida para manter a estrutura do código original

    if (isMultiSelect) {
        // Adiciona a prateleira à seleção
        console.log('Adicionando à seleção múltipla');
    } else {
        // Seleciona apenas esta prateleira
        if (shelfSelected.value && shelfSelected.value.id !== selectedId) {
            shelfSelected.value = null; // Desseleciona se já estiver selecionada
        }
    }
};

/**
 * Manipulador para o evento personalizado de seleção
 * @param e - Evento recebido
 */
const shelfSelectionHandler = (e: Event): void => {
    const customEvent = e as CustomEvent;
    handleShelfSelection(customEvent.detail.selectedId, customEvent.detail.isMultiSelect);
};

/**
 * Handle keyboard events for quantity adjustment
 * @param {KeyboardEvent} event - Keyboard event
 */
const handleKeyDown = (event: KeyboardEvent) => {
    // Only handle keyboard events when this shelf is selected
    if (shelfSelected.value && shelfSelected.value.id === props.shelf.id) {
        console.log('This shelf is selected, processing arrow key');

        if (event.key === 'ArrowUp') {
            event.preventDefault();
            const previousHole = props.holes.find((hole: Hole) => hole.position < props.shelf.shelf_position);
            console.log('Previous hole:', previousHole, props.shelf.shelf_position);
            if (previousHole) {
                // Move the shelf to the previous hole
                props.shelf.shelf_position = previousHole.position;
                // emit('update:segments', {
                //     shelfId: props.shelf.id,
                //     segments: sortableSegments.value,
                // });
            }
        } else if (event.key === 'ArrowDown') {
            event.preventDefault();
            const nextHole = props.holes.find((hole: Hole) => hole.position > props.shelf.shelf_position);
            console.log('Next hole:', nextHole, props.shelf.shelf_position);
            if (nextHole) {
                // Move the shelf to the next hole
                props.shelf.shelf_position = nextHole.position;
                // emit('update:segments', {
                //     shelfId: props.shelf.id,
                //     segments: sortableSegments.value,
                // });
            }
        }
    } else {
    }
};

/**
 * Função para gerar cores aleatórias consistentes baseadas no ID
 * Isso garante que o mesmo ID sempre produza a mesma cor
 * @param id - ID do segmento ou elemento
 * @returns String de cor no formato HSL
 */
function getRandomColor(id: string | number): string {
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

/**
 * Garante que todos os segmentos tenham IDs únicos
 * @param segments - Array de segmentos a serem verificados
 * @returns Array de segmentos com IDs garantidos
 */
const ensureSegmentIds = (segments: Segment[]): Segment[] => {
    return segments.map((segment, index) => ({
        ...segment,
        id: segment.id || `segment-${Date.now()}-${index}`,
    }));
};

/**
 * Referência local aos segmentos para o draggable
 * Aplica ordenamento e garante IDs para todos os segmentos
 */
const sortableSegments = computed<Segment[]>({
    get() {
        // Garantir que todos os segmentos tenham IDs
        return ensureSegmentIds(props.shelf.segments || []);
    },
    set(newSegments: Segment[]) {
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

/**
 * Computed property para estilo do container de segmentos
 * Define a altura baseada na altura da prateleira
 */
const segmentsContainerStyle = computed(() => {
    return {
        height: `${props.shelf.shelf_height * props.scaleFactor}px`,
    };
});

/**
 * Computed property para calcular o estilo da prateleira
 * Inclui posicionamento, dimensões e z-index
 */
const shelfStyle = computed(() => {
    // Convertemos a posição da prateleira para pixels usando o fator de escala
    const topPosition = props.shelf.shelf_position * props.scaleFactor;

    // Retornamos o estilo final com tipagem correta (as CSSProperties)
    return {
        position: 'absolute' as const, // Use 'as const' para tipar corretamente
        left: '-4px',
        width: `${props.sectionWidth * props.scaleFactor + 4}px`,
        height: `${props.shelf.shelf_height * props.scaleFactor}px`,
        top: `${topPosition}px`,
        zIndex: '1',
    };
});

/**
 * Computed property para o estilo da área de arrasto
 * Muda aparência quando um produto está sendo arrastado
 */
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

/**
 * Tratar eventos de arrasto do segmento
 * @param eventData - Dados do evento de arrasto
 */
const handleSegmentDrag = (eventData: any): void => {
    // Pode ser usado para rastreamento ou lógica adicional
    // Implementação futura se necessário
};

/**
 * Função para lidar com alterações no arrasto de segmentos
 * Atualiza a ordenação dos segmentos quando são arrastados
 * @param event - Evento de alteração do draggable
 */
const onSegmentDragChange = (event: DraggableEvent): void => {
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

/**
 * Função chamada quando um elemento é solto na prateleira
 * Processa tanto produtos quanto segmentos
 * @param event - Evento de drop do DOM
 */
const onDrop = (event: DragEvent): void => {
    draggingProduct.value = false; // Restaura o estado de arraste

    // Verificar se temos um produto ou um segmento sendo solto
    const productData = event.dataTransfer?.getData('text/product');
    const segmentData = event.dataTransfer?.getData('text/segment');

    if (productData) {
        handleProductDrop(productData);
    } else if (segmentData) {
        handleSegmentDrop(segmentData);
    }
};

/**
 * Processa a soltura de um produto na prateleira
 * Cria um novo segmento com o produto
 * @param productData - Dados do produto em formato JSON
 */
const handleProductDrop = (productData: string): void => {
    try {
        const product = JSON.parse(productData) as Product;
        // Verifica se o produto é válido
        if (product && product.id) {
            const newSegment: Segment = {
                id: `segment-${Date.now()}-${sortableSegments.value.length}`,
                width: parseInt(props.sectionWidth.toString()),
                ordering: (sortableSegments.value.length || 0) + 1,
                quantity: 1,
                spacing: 0,
                position: 0,
                preserveState: false,
                status: 'published',
                // Cria layer com informações do produto
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
                shelf: props.shelf,
                segment: newSegment,
            });
        }
    } catch (error) {
        console.error('Erro ao processar produto:', error);
    }
};

/**
 * Processa a soltura de um segmento na prateleira
 * Atualiza um segmento existente ou cria um novo
 * @param segmentData - Dados do segmento em formato JSON
 */
const handleSegmentDrop = (segmentData: string): void => {
    try {
        const segment = JSON.parse(segmentData) as Segment;
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
            // Cria um novo segmento
            const newSegment: Segment = {
                id: segment.id,
                width: parseInt(segment.width.toString()),
                ordering: (sortableSegments.value.length || 0) + 1, // Garante que fique no final
                quantity: segment.quantity,
                spacing: segment.spacing,
                position: segment.position,
                preserveState: false,
                status: 'published',
                // Cria layer com informações do produto
                layer: segment.layer,
            };

            emit('drop:product', {
                shelf: props.shelf,
                segment: newSegment,
            });
        }
    } catch (error) {
        console.error('Erro ao processar segmento:', error);
    }
};

/**
 * Função chamada quando o mouse entra na área de soltar
 * Ativa o modo de arraste para visualização
 * @param event - Evento de dragenter do DOM
 */
const onDragEnter = (event: DragEvent): void => {
    if (event.dataTransfer?.types.includes('text/product') || event.dataTransfer?.types.includes('text/segment')) {
        draggingProduct.value = true; // Define o estado de arraste
        event.preventDefault(); // Previne o comportamento padrão
        (event.target as HTMLElement).style.borderColor = 'rgba(59, 130, 246, 0.5)'; // Altera a cor da borda
    }
};

/**
 * Função chamada quando o mouse sai da área de soltar
 * Desativa o modo de arraste
 * @param event - Evento de dragleave do DOM
 */
const onDragLeave = (event: DragEvent): void => {
    if (shelfRef.value && !shelfRef.value.contains(event.relatedTarget as Node)) {
        draggingProduct.value = false; // Restaura o estado de arraste
        (event.target as HTMLElement).style.borderColor = 'transparent'; // Restaura a cor da borda
    }
};

/**
 * Cria um elemento visual que representa a prateleira durante o arrasto
 * @returns - Elemento DOM do ghost criado
 */
const createShelfGhost = (): HTMLElement => {
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

/**
 * Função chamada quando o arrasto começa
 * Configura a aparência e os dados para o arrasto
 * @param event - Evento de dragstart do DOM
 */
const onDragstart = (event: DragEvent): void => {
    (event.target as HTMLElement).style.opacity = '0.2'; // Diminui a opacidade da prateleira arrastada
    (event.target as HTMLElement).style.zIndex = '10'; // Aumenta o z-index da prateleira arrastada
    event.dataTransfer?.setData('text/shelf', JSON.stringify(props.shelf));

    // Cria um ghost mais avançado que se parece com a prateleira
    const newGhost = createShelfGhost();

    // Define offset de arraste (para centralizar no cursor)
    const offsetX = (props.sectionWidth * props.scaleFactor) / 2;
    const offsetY = 20; // Um pouco acima do cursor

    // Define o ghost como imagem de arraste
    event.dataTransfer?.setDragImage(newGhost, offsetX, offsetY);
};

/**
 * Função chamada quando o arrasto termina
 * Limpa os elementos visuais e dados do arrasto
 * @param event - Evento de dragend do DOM
 */
const onDragend = (event: DragEvent): void => {
    draggingProduct.value = false; // Restaura o estado de arraste
    event.dataTransfer?.clearData();
    (event.target as HTMLElement).style.opacity = '1'; // Restaura a opacidade da prateleira
    (event.target as HTMLElement).style.zIndex = '1'; // Restaura o z-index da prateleira

    // Limpa o ghost após um pequeno delay (para evitar flicker)
    setTimeout(() => {
        if (ghost.value) {
            document.body.removeChild(ghost.value);
            ghost.value = null;
        }
    }, 50);

    // Pegar a posição da prateleira
    const position = (event.target as HTMLElement).getBoundingClientRect();
    const shelfPosition = {
        left: position.left,
        top: position.top,
        width: position.width,
        height: position.height,
    };

    // Aqui você pode adicionar lógica para atualizar a posição da prateleira se necessário
    // shelf.shelf_position = shelfPosition.top;
};

// Lifecycle Hooks
onMounted(() => {
    // Registra os event listeners
    window.addEventListener(SHELF_SELECTED_EVENT, shelfSelectionHandler);
    window.addEventListener('keydown', handleKeyDown);
    document.addEventListener('click', handleDocumentClick);
});

onUnmounted(() => {
    // Limpa os event listeners
    window.removeEventListener(SHELF_SELECTED_EVENT, shelfSelectionHandler);
    window.removeEventListener('keydown', handleKeyDown);
    document.removeEventListener('click', handleDocumentClick);
    // Limpa recursos como ghost, se existirem
    if (ghost.value && document.body.contains(ghost.value)) {
        document.body.removeChild(ghost.value);
    }
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
