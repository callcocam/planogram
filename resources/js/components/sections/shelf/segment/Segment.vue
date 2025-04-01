<template>
    <div 
        class="segment relative flex items-end" 
        :style="segmentStyle" 
        @click="segmentClick" 
        @dragstart="onDragstart"
        @dragend="onDragend"
        ref="segmentRef"
    >
        <Layer
            v-for="(quantity, index) in segmentQuantity"
            :key="index"
            :shelf="shelf"
            :segment="segment"
            :layer="segment.layer"
            :scale-factor="scaleFactor"
        />
    </div>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import Layer from './Layer.vue';
import { on } from 'events';

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

const emit = defineEmits(['segment-select']);

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

// ----------------------------------------------------
// Propriedades computadas
// ----------------------------------------------------
/**
 * Retorna a quantidade de segmentos e gerencia o estado de seleção 
 * com base na categoria selecionada
 */
const segmentQuantity = computed(() => {
    // Verifica se existe uma categoria selecionada
    if (props.selectedCategory) {
        const isCategoryMatch = props.segment.layer.product.category_id === props.selectedCategory.id;
        
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
    }else{
        // Se não houver categoria selecionada, mantém o estado de seleção atual
        segmentSelected.value = false;
    }
    
    return props.segment.quantity;
});

/**
 * Calcula o estilo do segmento com base nas propriedades e estado de seleção
 */
const segmentStyle = computed(() => {
    // Calcula as dimensões do segmento
    const layerHeight = props.segment.layer.product.height * props.segment.quantity * props.scaleFactor;
    const layerWidth = props.segment.layer.product.width * props.shelf.quantity * props.scaleFactor;

    // Estilo condicional quando o segmento está selecionado
    const selectedStyle = segmentSelected.value ? {
        border: '2px solid blue',
        boxShadow: '0 0 5px rgba(0, 0, 255, 0.5)',
    } : {};

    // Retorna o estilo completo
    return {
        height: `${layerHeight}px`,
        width: `${layerWidth}px`,
        marginBottom: `${props.shelf.shelf_height * props.scaleFactor}px`,
        ...selectedStyle,
    };
});

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
};

/**
 * Manipula o início do arraste
 * @param {DragEvent} event - O evento de arraste
 */
const onDragstart = (event) => {
    event.dataTransfer.setData('text/segment', JSON.stringify(props.segment));
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.dropEffect = 'move';
    if (segmentRef.value) {
        segmentRef.value.style.opacity = '0.5'; // Diminui a opacidade do segmento arrastado
        segmentRef.value.style.zIndex = '10'; // Aumenta o z-index do segmento arrastado
    }
};

/**
 * Manipula o fim do arraste
 * @param {DragEvent} event - O evento de arraste
 */
const onDragend = (event) => {
    if (segmentRef.value) {
        segmentRef.value.style.opacity = '1'; // Restaura a opacidade do segmento
        segmentRef.value.style.zIndex = '1'; // Restaura o z-index do segmento
    }
    event.dataTransfer.clearData();
    event.dataTransfer.dropEffect = 'none';
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
    handleSegmentSelection(
        customEvent.detail.segmentId, 
        customEvent.detail.isMultiSelect
    );
};

// ----------------------------------------------------
// Lifecycle hooks
// ----------------------------------------------------
// Registra o ouvinte de eventos quando o componente é montado
onMounted(() => {
    window.addEventListener(SEGMENT_SELECTED_EVENT, segmentSelectionHandler);
});

// Remove o ouvinte de eventos quando o componente é desmontado
onUnmounted(() => {
    window.removeEventListener(SEGMENT_SELECTED_EVENT, segmentSelectionHandler);
});
</script>