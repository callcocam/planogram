<template>
    <div
        class="segment drag-handle group relative flex cursor-pointer flex-col items-end border-2"
        :style="segmentStyle"
        @click="segmentClick"
        @dragstart="onDragstart"
        @dragend="onDragend"
        ref="segmentRef"
        draggable="true"
        tabindex="0"
    >
        <!-- Contador de layers visível quando selecionado -->
        <div v-if="segmentSelected" class="absolute -top-6 left-0 w-full rounded-t-md bg-blue-600 px-2 py-1 text-xs text-white">
            Pilhas: {{ segment.quantity }}
        </div>

        <Layer
            v-for="(quantity, index) in segmentQuantity"
            :key="index"
            :shelf="shelf"
            :segment="segment"
            :layer="segment.layer"
            :scale-factor="scaleFactor"
            :selected="segmentSelected"
        />
    </div>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import Layer from './Layer.vue';

// ----------------------------------------------------
// Props e Emits
// ----------------------------------------------------
const props = defineProps({
    shelf: {
        type: Object,
        required: true,
    },
    segment: {
        type: Object,
        required: true,
    },
    selectedCategory: {
        type: Object,
        default: null,
    },
    scaleFactor: {
        type: Number,
        required: true,
    },
});

const emit = defineEmits(['segment-select', 'segment-drag', 'update:quantity']);

// ----------------------------------------------------
// Estado do componente
// ----------------------------------------------------
/** Referência ao elemento DOM do segmento */
const segmentRef = ref(null) as any;

/** Estado de seleção do segmento */
const segmentSelected = ref(false);

/** ID único para este segmento - usado para comunicação entre segmentos */
const segmentId = ref(`segment-${props.segment.id || Math.random().toString(36).substring(2, 9)}`);

/** Nome do evento customizado para comunicação entre segmentos */
const SEGMENT_SELECTED_EVENT = 'segment-selected';

// Referência para o timer de debounce
const debounceTimer = ref<any>(null);
// ----------------------------------------------------
// Propriedades computadas
// ----------------------------------------------------
/**
 * Retorna a quantidade de segmentos e gerencia o estado de seleção
 * com base na categoria selecionada
 */
const segmentQuantity = ref(props.segment.quantity);

watch(
    () => props.selectedCategory,
    (newCategory) => {
        if (!newCategory) {
            // Se a categoria selecionada for nula, reseta o estado de seleção
            segmentSelected.value = false;
            return;
        }
        // Se a categoria selecionada não for nula, atualiza o estado de seleção
        const isCategoryMatch = props.segment.layer.product.category_id === newCategory.id;

        // Atualiza o estado de seleção do segmento
        segmentSelected.value = isCategoryMatch;

        // Emite evento de seleção para o componente pai
        emit('segment-select', {
            ...props.segment,
            product: props.segment.layer.product,
            isMultiSelect: true,
            segmentSelected: isCategoryMatch,
            category: true,
        });
    },
);

/**
 * Função de debounce que atrasa a execução de uma função
 * @param {Function} fn - Função a ser executada após o delay
 * @param {Number} delay - Tempo de espera em ms
 */
const debounce = (fn, delay = 300) => {
    if (debounceTimer.value) clearTimeout(debounceTimer.value);
    debounceTimer.value = setTimeout(() => {
        fn();
        debounceTimer.value = null;
    }, delay);
};

/**
 * Calcula o estilo do segmento com base nas propriedades e estado de seleção
 */
const segmentStyle = computed(() => {
    // Calcula as dimensões do segmento
    const layerHeight = props.segment.layer.product.height * segmentQuantity.value * props.scaleFactor;
    const layerWidth = props.segment.layer.product.width * props.shelf.quantity * props.scaleFactor;

    // Estilo condicional quando o segmento está selecionado
    const selectedStyle = segmentSelected.value
        ? {
              border: '2px solid blue',
              boxShadow: '0 0 5px rgba(0, 0, 255, 0.5)',
              outline: 'none',
          }
        : {};

    // Retorna o estilo completo
    return {
        height: `${layerHeight}px`,
        width: `${layerWidth}px`,
        marginBottom: `${props.shelf.shelf_height * props.scaleFactor}px`,
        ...selectedStyle,
    };
});

// ----------------------------------------------------
// Funções para manipular a quantidade de layers
// ----------------------------------------------------
/**
 * Aumenta a quantidade de layers em um segmento
 */
const onIncreaseQuantity = () => {
    if (!segmentSelected.value) return;

    // Incrementa o valor imediatamente na UI
    segmentQuantity.value++;

    // Debounce na emissão do evento para o componente pai
    debounce(() => {
        emit('update:quantity', {
            segmentId: props.segment.id,
            data: {
                increaseQuantity: segmentQuantity.value,
            },
        });
    });
};

/**
 * Diminui a quantidade de layers em um segmento
 */
const onDecreaseQuantity = () => {
    if (!segmentSelected.value) return;
    if (segmentQuantity.value > 1) {
        // Decrementa o valor imediatamente na UI
        segmentQuantity.value--;

        // Debounce na emissão do evento para o componente pai
        debounce(() => {
            emit('update:quantity', {
                segmentId: props.segment.id,
                data: {
                    decreaseQuantity: segmentQuantity.value,
                },
            });
        });
    }
};

// ----------------------------------------------------
// Manipuladores de eventos
// ----------------------------------------------------
/**
 * Gerencia o clique no segmento
 * @param {MouseEvent} event - O evento de clique
 */
const segmentClick = (event) => {
    // Evita a propagação do evento para os elementos pai
    event.stopPropagation();

    // Verifica se está em modo multi-seleção (Ctrl/Cmd pressionado)
    const isMultiSelect = event.ctrlKey || event.metaKey;

    // Atualiza o estado de seleção
    if (isMultiSelect) {
        // No modo multi-seleção, inverte o estado atual
        segmentSelected.value = !segmentSelected.value;
    } else {
        // No modo normal, seleciona este segmento
        segmentSelected.value = true;
    }

    // Notifica outros segmentos sobre a seleção
    notifyOtherSegments(isMultiSelect);

    // Notifica o componente pai sobre a seleção
    notifyParent(isMultiSelect);

    // Foca no elemento para permitir eventos de teclado
    if (segmentRef.value && segmentSelected.value) {
        segmentRef.value.focus();
    }
};

/**
 * Manipula o início do arraste
 * @param {DragEvent} event - O evento de arraste
 */
const onDragstart = (event) => {
    // Configure o dataTransfer para transferir os dados do segmento
    event.dataTransfer.setData('text/segment', JSON.stringify(props.segment));
    event.dataTransfer.effectAllowed = 'move';

    // Aplicar estilos visuais durante o arrasto
    if (segmentRef.value) {
        segmentRef.value.style.opacity = '0.5';
        segmentRef.value.style.zIndex = '10';
    }

    // Emitir evento para o componente pai
    emit('segment-drag', {
        segment: props.segment,
        action: 'start',
    });

    // Criar uma imagem de arrasto personalizada (opcional)
    try {
        const rect = segmentRef.value.getBoundingClientRect();
        const ghostEl = document.createElement('div');
        ghostEl.style.width = `${rect.width}px`;
        ghostEl.style.height = `${rect.height}px`;
        ghostEl.style.backgroundColor = 'rgba(59, 130, 246, 0.3)';
        ghostEl.style.borderRadius = '4px';
        ghostEl.style.position = 'absolute';
        ghostEl.style.top = '-1000px';
        ghostEl.style.left = '-1000px';

        document.body.appendChild(ghostEl);
        event.dataTransfer.setDragImage(ghostEl, 20, 20);

        setTimeout(() => {
            document.body.removeChild(ghostEl);
        }, 0);
    } catch (error) {
        console.error('Erro ao criar imagem de arrasto:', error);
    }
};

/**
 * Manipula o fim do arraste
 * @param {DragEvent} event - O evento de arraste
 */
const onDragend = (event) => {
    // Restaurar estilos visuais
    if (segmentRef.value) {
        segmentRef.value.style.opacity = '1';
        segmentRef.value.style.zIndex = '1';
    }

    // Limpar os dados de transferência
    event.dataTransfer.clearData();

    // Emitir evento para o componente pai
    emit('segment-drag', {
        segment: props.segment,
        action: 'end',
    });
};

/**
 * Notifica outros segmentos sobre a seleção através de um evento personalizado
 * @param {boolean} isMultiSelect - Indica se está no modo de multi-seleção
 */
const notifyOtherSegments = (isMultiSelect) => {
    const customEvent = new CustomEvent(SEGMENT_SELECTED_EVENT, {
        detail: {
            segmentId: segmentId.value,
            isMultiSelect: isMultiSelect,
        },
    });

    window.dispatchEvent(customEvent);
};

/**
 * Notifica o componente pai sobre a seleção
 * @param {boolean} isMultiSelect - Indica se está no modo de multi-seleção
 */
const notifyParent = (isMultiSelect) => {
    emit('segment-select', {
        ...props.segment,
        product: props.segment.layer.product,
        isMultiSelect: isMultiSelect,
        segmentSelected: segmentSelected.value,
        category: false,
    });
};

/**
 * Manipula eventos de seleção de outros segmentos
 * @param {string} selectedId - ID do segmento selecionado
 * @param {boolean} isMultiSelect - Indica se está no modo de multi-seleção
 */
const handleSegmentSelection = (selectedId, isMultiSelect) => {
    // Se não for multi-seleção e outro segmento foi selecionado, deseleciona este
    if (!isMultiSelect && selectedId !== segmentId.value) {
        segmentSelected.value = false;
    }
};

/**
 * Função de callback para o evento personalizado
 * @param {Event} e - Evento recebido
 */
const segmentSelectionHandler = (e) => {
    const customEvent = e as CustomEvent;
    handleSegmentSelection(customEvent.detail.segmentId, customEvent.detail.isMultiSelect);
};

// ----------------------------------------------------
// Lifecycle hooks
// ----------------------------------------------------
// Registra o ouvinte de eventos quando o componente é montado
onMounted(() => {
    window.addEventListener(SEGMENT_SELECTED_EVENT, segmentSelectionHandler);

    // Adiciona listener de teclado para o documento inteiro
    document.addEventListener('keydown', (event) => {
        if (segmentSelected.value) {
            if (event.key === 'ArrowUp') {
                event.preventDefault();
                onIncreaseQuantity();
            } else if (event.key === 'ArrowDown') {
                event.preventDefault();
                onDecreaseQuantity();
            }
        }
    });
});

// Remove o ouvinte de eventos quando o componente é desmontado
onUnmounted(() => {
    window.removeEventListener(SEGMENT_SELECTED_EVENT, segmentSelectionHandler);
    if (debounceTimer.value) clearTimeout(debounceTimer.value);
});
</script>

<style scoped>
.segment {
    transition: all 0.2s ease;
}

.segment:hover {
    z-index: 5;
}

.segment:focus {
    outline: none;
}

.drag-handle {
    touch-action: none;
}
</style>
