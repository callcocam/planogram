<template>
    <div class="segment relative flex items-end" :style="segmentStyle" @click="segmentClick" ref="segmentRef">
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
import { computed, onMounted, onUnmounted, ref } from 'vue';
import Layer from './Layer.vue';
const props = defineProps({
    shelf: {
        type: Object,
        required: true,
    },
    segment: {
        type: Object,
        required: true,
    },
    scaleFactor: {
        type: Number,
        required: true,
    },
});

const emit = defineEmits(['segment-select']);

const segmentQuantity = computed(() => {
    return props.segment.quantity;
});

const segmentSelected = ref(false);
const segmentRef = ref(null) as any;

// Evento personalizado para notificar outros segmentos
const SEGMENT_SELECTED_EVENT = 'segment-selected';

// ID único para este segmento
const segmentId = ref(`segment-${props.segment.id || Math.random().toString(36).substring(2, 9)}`);

// Função para deselecionar este segmento quando outro é clicado
const handleSegmentSelection = (selectedId, isMultiSelect) => {
    // Se não for multi-seleção (Ctrl não pressionado) e o ID for diferente, deseleciona
    if (!isMultiSelect && selectedId !== segmentId.value) {
        segmentSelected.value = false;
    }
    // Se for multi-seleção, mantém o estado atual (não faz nada)
};

// Função de callback para o evento
const segmentSelectionHandler = (e: Event) => {
    const customEvent = e as CustomEvent;
    handleSegmentSelection(customEvent.detail.segmentId, customEvent.detail.isMultiSelect);
};

// Registrar e remover o ouvinte de eventos
onMounted(() => {
    window.addEventListener(SEGMENT_SELECTED_EVENT, segmentSelectionHandler);
});

onUnmounted(() => {
    window.removeEventListener(SEGMENT_SELECTED_EVENT, segmentSelectionHandler);
});

const segmentStyle = computed(() => {
    const layerHeight = props.segment.layer.product.height * props.segment.quantity * props.scaleFactor;
    const layerWidth = props.segment.layer.product.width * props.shelf.quantity * props.scaleFactor;

    let selectedStyle = null as any;
    if (segmentSelected.value) {
        selectedStyle = {
            border: '2px solid blue',
            boxShadow: '0 0 5px rgba(0, 0, 255, 0.5)',
        };
    }

    return {
        height: `${layerHeight}px`,
        width: `${layerWidth}px`,
        marginBottom: `${props.shelf.shelf_height * props.scaleFactor}px`,
        ...selectedStyle,
    };
});

const segmentClick = (event) => {
    event.stopPropagation();

    // Verifica se a tecla Ctrl (ou Command no Mac) está pressionada
    const isMultiSelect = event.ctrlKey || event.metaKey;

    // Se for clique normal (sem Ctrl) em um segmento já selecionado, mantém selecionado
    // Se for clique normal em um não selecionado, seleciona este e deseleciona outros
    // Se for clique com Ctrl, inverte a seleção deste segmento sem afetar os outros
    if (isMultiSelect) {
        // Toggle da seleção quando Ctrl estiver pressionado
        segmentSelected.value = !segmentSelected.value;
    } else {
        // Comportamento normal - seleciona este segmento
        segmentSelected.value = true;
    }

    // Notifica outros segmentos
    const customEvent = new CustomEvent(SEGMENT_SELECTED_EVENT, {
        detail: {
            segmentId: segmentId.value,
            isMultiSelect: isMultiSelect,
        },
    });

    const segment = props.segment;
    // Emite o evento de clique no segmento
    emit('segment-select', {
        ...segment,
        product: props.segment.layer.product,
        isMultiSelect: isMultiSelect,
        segmentSelected: segmentSelected.value,
    });
    window.dispatchEvent(customEvent);
};
</script>
