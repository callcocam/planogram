<template>
    <div class="segment group flex justify-between" :style="layerStyle">
        <Product
            v-for="(quantity, index) in layerQuantity"
            :key="index"
            :shelf="shelf"
            :segment="segment"
            :product="layer.product"
            :scale-factor="scaleFactor"
            :layer="layer"
            :product-spacing="layerSpacing"
        />
    </div>
</template>
<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
// @ts-ignore
import { useToast } from '@/components/ui/toast/use-toast';
import Product from './Product.vue';
const props = defineProps({
    shelf: {
        type: Object,
        required: true,
    },
    segment: {
        type: Object,
        required: true,
    },
    layer: {
        type: Object,
        required: true,
    },
    scaleFactor: {
        type: Number,
        required: true,
    },
    selected: {
        type: Boolean,
        default: false,
    },
});

/** Estado de seleção do segmento */
const segmentSelected = ref(false);

// Referência para o timer de debounce
const debounceTimer = ref<any>(null);

const layerQuantity = ref(props.layer.quantity || 1);

const layerSpacing = ref(props.layer.spacing);

const originalQuantity = ref(props.layer.quantity);

const { toast } = useToast();

const emit = defineEmits(['update:quantity']);

watch(
    () => props.selected,
    (newValue) => {
        console.log('Novo valor de selected:', newValue);
        segmentSelected.value = newValue;
    },
);

const layerStyle = computed(() => {
    const layerHeight = props.layer.product.height * props.scaleFactor;
    const layerWidth = props.layer.product.width * props.shelf.quantity * props.scaleFactor;
    return {
        height: `${layerHeight}px`,
        width: `${layerWidth}px`,
        position: 'relative' as const,
    };
});

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
// ----------------------------------------------------
// Funções para manipular a quantidade de layers
// ----------------------------------------------------

/**
 * Manipula a alteração de quantidade de um segmento
 * @param object params - Dados a serem enviados para o servidor
 * @param {number} newQuantity - Nova quantidade
 * @returns {number} - Nova quantidade
 */
const updateLayerQuantity = async (params, currentQuantity) => {
    if (!segmentSelected.value) return;

    // const currentQuantity = action.includes('increase') ? newQuantity + 1 : newQuantity - 1;
    // Atualiza o UI imediatamente
    originalQuantity.value = currentQuantity; 

    // Debounce na emissão do evento para o componente pai
    try {
        // @ts-ignore
        const response = await window.axios.put(route('planogram.api.layers.update', props.layer.id), params);
        const { data } = response;
        // Atualiza a quantidade no UI
        if (data) {
            const { description, title, variant } = data;
            //     // Atualiza a quantidade no UI
          
            toast({
                title,
                description,
                variant,
            });
        }
    } catch (error) {
        toast({
            title: 'Erro ao atualizar a quantidade do segmento',
            description: error.response?.data?.message || 'Erro desconhecido',
            variant: 'destructive',
        });
        originalQuantity.value = currentQuantity - 1; // Restaura o valor original em caso de erro 
        // Restaura o valor original em caso de erro
    }
};

/**
 * Aumenta a quantidade de layers em um segmento
 */
const onIncreaseQuantity = async () => {
    const data = {
        increaseQuantity: true,
        quantity: layerQuantity.value + 1,
    };
    await updateLayerQuantity(data, layerQuantity.value + 1);
    // Atualiza a quantidade no UI
    layerQuantity.value = originalQuantity.value;
    emit('update:quantity', layerQuantity.value);
};

/**
 * Diminui a quantidade de layers em um segmento
 */
const onDecreaseQuantity = async () => {
    const data = {
        decreaseQuantity: true,
        quantity: layerQuantity.value - 1,
    };

    // Para decremento, verificar se a quantidade é maior que 1
    if (data.quantity <= 0) return;
    await updateLayerQuantity(data, layerQuantity.value - 1);
    // Atualiza a quantidade no UI
    layerQuantity.value = originalQuantity.value;
    emit('update:quantity', layerQuantity.value);
};

/**
 * Add um spacing entre os produtos
 * @param {number} index - Índice do produto
 * @returns {string} - Estilo de espaçamento
 */
const onSpacingIncrease = async () => {
    const data = {
        increaseSpacing: true,
        spacing: layerSpacing.value + 1,
    };
    await updateLayerQuantity(data, layerSpacing.value + 1);
    // Atualiza o espaçamento no UI
    layerSpacing.value = originalQuantity.value;
};

/**
 * Remove o spacing entre os produtos
 * @param {number} index - Índice do produto
 * @returns {string} - Estilo de espaçamento
 */
const onSpacingDecrease = async () => {
    const data = {
        decreaseSpacing: true,
        spacing: layerSpacing.value - 1,
    }; 
    // Para decremento, verificar se a quantidade é maior que 1
    if (data.spacing < 0) return;
    await updateLayerQuantity(data, layerSpacing.value - 1);
    // Atualiza o espaçamento no UI
    layerSpacing.value = originalQuantity.value;
};

// ----------------------------------------------------
// Lifecycle hooks
// ----------------------------------------------------
// Registra o ouvinte de eventos quando o componente é montado
onMounted(() => {
    // Adiciona listener de teclado para o documento inteiro
    document.addEventListener('keydown', async (event) => {
        if (segmentSelected.value) {
            if (event.key === 'ArrowRight') {
                event.preventDefault();
                await onIncreaseQuantity();
            } else if (event.key === 'ArrowLeft') {
                event.preventDefault();
                await onDecreaseQuantity();
            }
            //Verifica se a tecla pressionada é a tecla de espaço
            else if (event.key === ' ') {
                event.preventDefault();
                // Chama a função de aumentar a quantidade
                await onSpacingIncrease();
            }
            //Verifica se a tecla pressionada é a tecla de espaço
            else if (event.key === 'Backspace') {
                event.preventDefault();
                // Chama a função de diminuir a quantidade
                await onSpacingDecrease();
            }
        }
    });
});

// Remove o ouvinte de eventos quando o componente é desmontado
onUnmounted(() => {
    if (debounceTimer.value) clearTimeout(debounceTimer.value);
});
</script>
