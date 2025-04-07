<template>
    <div
        class="segment drag-segment-handle group relative flex cursor-pointer flex-col items-end border-2"
        :style="segmentStyle"
        @click="segmentClick"
        @dragstart="onDragstart"
        @dragend="onDragend"
        ref="segmentRef"
        draggable="true"
        tabindex="0"
    >
        <!-- Stack counter visible when selected -->
        <div v-if="segmentSelected" class="absolute -top-6 left-0 w-full rounded-t-md bg-blue-600 px-2 py-1 text-xs text-white">
            Produtos: {{ originalQuantity * segment.quantity }} de {{ previewQuantity }}
        </div>

        <Layer
            v-for="(quantity, index) in segmentQuantity"
            :key="index"
            :shelf="shelf"
            :segment="segment"
            :layer="segment.layer"
            :scale-factor="scaleFactor"
            :selected="segmentSelected"
            @update:quantity="updateLayerQuantity"
        />
    </div>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
// @ts-ignore
import { useToast } from '@/components/ui/toast/use-toast';
import Layer from './Layer.vue';

const { toast } = useToast();

// ----------------------------------------------------
// Props and Emits
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
        type: Object as () => Record<string, any> | null,
        default: null,
    },
    scaleFactor: {
        type: Number,
        required: true,
    },
});

const emit = defineEmits(['segment-select', 'segment-drag', 'update:quantity']);

// ----------------------------------------------------
// Component State
// ----------------------------------------------------
/** DOM reference to the segment element */
const segmentRef = ref(null) as any;

/** Segment selection state */
const segmentSelected = ref(false);

/** Unique ID for this segment - used for communication between segments */
const segmentId = ref(`segment-${props.segment.id || Math.random().toString(36).substring(2, 9)}`);

/** Segment quantity (number of layers) */
const segmentQuantity = ref(props.segment.quantity);

/** Reference to the layer spacing */
const layerSpacing = ref(props.segment.layer.spacing);
/** Reference to the original quantity of layers */
const originalQuantity = ref(props.segment.layer.quantity);

/** Custom event name for inter-segment communication */
const SEGMENT_SELECTED_EVENT = 'segment-selected';

/** Debounce timer reference */
const debounceTimer = ref<any>(null);

// ----------------------------------------------------
// Watchers
// ----------------------------------------------------
/**
 * Watch for changes in selected category and update selection state
 */
watch(
    () => props.selectedCategory,
    (newCategory) => {
        if (!newCategory) {
            // Reset selection state when category is null
            segmentSelected.value = false;
            return;
        }

        // Check if this segment's product belongs to the selected category
        const isCategoryMatch = props.segment.layer.product.category_id === newCategory.id;

        // Update segment selection state
        segmentSelected.value = isCategoryMatch;

        // Notify parent component about the selection
        emit('segment-select', {
            ...props.segment,
            product: props.segment.layer.product,
            isMultiSelect: true,
            segmentSelected: isCategoryMatch,
            category: true,
        });
    },
);

// ----------------------------------------------------
// Computed Properties
// ----------------------------------------------------
/**
 * Calculate segment style based on properties and selection state
 */
const segmentStyle = computed(() => {
    // Calculate segment dimensions
    const layerHeight = props.segment.layer.product.height * segmentQuantity.value * props.scaleFactor;
    const layerWidth = props.segment.layer.product.width * props.shelf.quantity * props.scaleFactor;

    // Conditional style when segment is selected
    const selectedStyle = segmentSelected.value
        ? {
              border: '2px solid blue',
              boxShadow: '0 0 5px rgba(0, 0, 255, 0.5)',
              outline: 'none',
          }
        : {};

    // Return complete style object
    return {
        height: `${layerHeight}px`,
        width: `${layerWidth}px`,
        marginBottom: `${props.shelf.shelf_height * props.scaleFactor}px`,
        ...selectedStyle,
    };
});

const previewQuantity = computed(() => {
    // Obtém as dimensões relevantes
    const productWidth = props.segment.layer.product.width;
    const spacing = layerSpacing.value || 0;
    const shelfWidth = props.shelf.section.width;

    // Calculamos a largura total disponível na seção
    let availableWidth = shelfWidth;

    // Se tivermos outros segmentos na prateleira, subtraia o espaço que eles ocupam
    if (props.shelf.segments && props.shelf.segments.length > 0) {
        props.shelf.segments.forEach((seg) => {
            // Pule o segmento atual
            if (seg.id === props.segment.id) {
                return;
            }

            // Para cada outro segmento, calcule o espaço que ele ocupa
            const segProductWidth = seg.layer.product.width;
            const segQuantity = seg.layer.quantity;
            const segSpacing = seg.layer.spacing || 0;

            // Para n produtos, precisamos de (n-1) espaçamentos
            const totalSegSpacing = segQuantity > 1 ? segSpacing * (segQuantity - 1) : 0;

            // Subtraia a largura deste segmento do espaço disponível
            availableWidth -= segProductWidth * segQuantity + totalSegSpacing;
        });
    }

    // Calculamos quantos produtos cabem no espaço disponível
    // Fórmula: maxProducts = (availableWidth + spacing) / (productWidth + spacing)
    let maxQuantity;

    if (spacing > 0) {
        maxQuantity = Math.floor((availableWidth + spacing) / (productWidth + spacing));
    } else {
        maxQuantity = Math.floor(availableWidth / productWidth);
    }

    // Garanta que o valor não seja negativo
    return Math.max(0, maxQuantity);
});

/**
 * Calculate layer style based on properties
 */
const updateLayerQuantity = (quantity: number) => {
    // Update layer quantity and spacing
    originalQuantity.value = quantity;
};
// ----------------------------------------------------
// Utility Functions
// ----------------------------------------------------
/**
 * Debounce function to delay execution
 * @param {Function} fn - Function to execute after delay
 * @param {Number} delay - Delay time in ms
 */
const debounce = (fn: Function, delay = 300) => {
    if (debounceTimer.value) clearTimeout(debounceTimer.value);
    debounceTimer.value = setTimeout(() => {
        fn();
        debounceTimer.value = null;
    }, delay);
};

// ----------------------------------------------------
// Layer Quantity Management
// ----------------------------------------------------
/**
 * Increase the number of layers in a segment
 */
const onIncreaseQuantity = () => {
    if (!segmentSelected.value) return;

    const currentQuantity = segmentQuantity.value;

    debounce(() => {
        // @ts-ignore
        window.axios
            // @ts-ignore
            .put(route('planogram.api.segments.update', props.segment.id), {
                increaseQuantity: true,
                quantity: currentQuantity + 1,
            })
            .then((response) => {
                segmentQuantity.value++;
                const { description, title, variant } = response.data;

                toast({
                    title,
                    description,
                    variant,
                });
            })
            .catch((error) => {
                toast({
                    title: 'Erro ao atualizar a quantidade do segmento',
                    description: error.response.data.message,
                    variant: 'destructive',
                });
            });
    });
};

/**
 * Decrease the number of layers in a segment
 */
const onDecreaseQuantity = () => {
    if (!segmentSelected.value || segmentQuantity.value <= 1) return;

    const currentQuantity = segmentQuantity.value;

    debounce(() => {
        // @ts-ignore
        window.axios
            // @ts-ignore
            .put(route('planogram.api.segments.update', props.segment.id), {
                decreaseQuantity: true,
                quantity: currentQuantity - 1,
            })
            .then((response) => {
                segmentQuantity.value--;
                const { description, title, variant } = response.data;

                toast({
                    title,
                    description,
                    variant,
                });
            })
            .catch((error) => {
                toast({
                    title: 'Erro ao atualizar a quantidade do segmento',
                    description: error.response.data.message,
                    variant: 'destructive',
                });
            });
    });
};

// ----------------------------------------------------
// Event Handlers
// ----------------------------------------------------
/**
 * Handle segment click
 * @param {MouseEvent} event - Click event
 */
const segmentClick = (event: MouseEvent) => {
    // Prevent event propagation to parent elements
    event.stopPropagation();

    // Check if in multi-select mode (Ctrl/Cmd pressed)
    const isMultiSelect = event.ctrlKey || event.metaKey;

    // Update selection state
    if (isMultiSelect) {
        // In multi-select mode, toggle current state
        segmentSelected.value = !segmentSelected.value;
    } else {
        // In normal mode, select this segment
        segmentSelected.value = true;
    }

    // Notify other segments about the selection
    notifyOtherSegments(isMultiSelect);

    // Notify parent component about the selection
    notifyParent(isMultiSelect);

    // Focus element to enable keyboard events
    if (segmentRef.value && segmentSelected.value) {
        segmentRef.value.focus();
    }
};

/**
 * Handle drag start
 * @param {DragEvent} event - Drag event
 */
const onDragstart = (event: DragEvent) => {
    if (!event.dataTransfer) return;

    // Set up dataTransfer to transfer segment data
    event.dataTransfer.setData('text/segment', JSON.stringify(props.segment));
    event.dataTransfer.effectAllowed = 'move';

    // Apply visual styles during drag
    if (segmentRef.value) {
        segmentRef.value.style.opacity = '0.5';
        segmentRef.value.style.zIndex = '10';
    }

    // Emit event to parent component
    emit('segment-drag', {
        segment: props.segment,
        action: 'start',
    });

    // Create custom drag image
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
        console.error('Error creating drag image:', error);
    }
};

/**
 * Handle drag end
 * @param {DragEvent} event - Drag event
 */
const onDragend = (event: DragEvent) => {
    if (!event.dataTransfer) return;

    // Restore visual styles
    if (segmentRef.value) {
        segmentRef.value.style.opacity = '1';
        segmentRef.value.style.zIndex = '1';
    }

    // Clear transfer data
    event.dataTransfer.clearData();

    // Emit event to parent component
    emit('segment-drag', {
        segment: props.segment,
        action: 'end',
    });
};

/**
 * Notify other segments about this selection via custom event
 * @param {boolean} isMultiSelect - Indicates if in multi-select mode
 */
const notifyOtherSegments = (isMultiSelect: boolean) => {
    const customEvent = new CustomEvent(SEGMENT_SELECTED_EVENT, {
        detail: {
            segmentId: segmentId.value,
            isMultiSelect,
        },
    });

    window.dispatchEvent(customEvent);
};

/**
 * Notify parent component about the selection
 * @param {boolean} isMultiSelect - Indicates if in multi-select mode
 */
const notifyParent = (isMultiSelect: boolean) => {
    emit('segment-select', {
        ...props.segment,
        product: props.segment.layer.product,
        isMultiSelect,
        segmentSelected: segmentSelected.value,
        category: false,
    });
};

/**
 * Handle selection events from other segments
 * @param {string} selectedId - ID of the selected segment
 * @param {boolean} isMultiSelect - Indicates if in multi-select mode
 */
const handleSegmentSelection = (selectedId: string, isMultiSelect: boolean) => {
    // Deselect this segment if not in multi-select mode and another segment was selected
    if (!isMultiSelect && selectedId !== segmentId.value) {
        segmentSelected.value = false;
    }
};

/**
 * Callback function for the custom event
 * @param {Event} e - Received event
 */
const segmentSelectionHandler = (e: Event) => {
    const customEvent = e as CustomEvent;
    handleSegmentSelection(customEvent.detail.segmentId, customEvent.detail.isMultiSelect);
};

/**
 * Handle keyboard events for quantity adjustment
 * @param {KeyboardEvent} event - Keyboard event
 */
const handleKeyDown = (event: KeyboardEvent) => {
    if (segmentSelected.value) {
        if (event.key === 'ArrowUp') {
            event.preventDefault();
            onIncreaseQuantity();
        } else if (event.key === 'ArrowDown') {
            event.preventDefault();
            onDecreaseQuantity();
        }
    }
};

// ----------------------------------------------------
// Lifecycle Hooks
// ----------------------------------------------------
onMounted(() => {
    // Register event listeners
    window.addEventListener(SEGMENT_SELECTED_EVENT, segmentSelectionHandler);
    document.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    // Clean up event listeners and timers
    window.removeEventListener(SEGMENT_SELECTED_EVENT, segmentSelectionHandler);
    document.removeEventListener('keydown', handleKeyDown);

    if (debounceTimer.value) {
        clearTimeout(debounceTimer.value);
    }
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
