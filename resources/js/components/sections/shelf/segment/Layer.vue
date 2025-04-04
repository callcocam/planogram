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

const layerQuantity = ref(props.layer.quantity);

const { toast } = useToast();

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
 * @param {string} action - 'increase' ou 'decrease'
 */
const updateLayerQuantity = (action: 'increase' | 'decrease') => {
    if (!segmentSelected.value) return;

    // Para decremento, verificar se a quantidade é maior que 1
    if (action === 'decrease' && layerQuantity.value <= 1) return;

    // Calculando a nova quantidade
    const newQuantity = action === 'increase' ? layerQuantity.value + 1 : layerQuantity.value - 1;

    // Atualiza o UI imediatamente
    const originalQuantity = layerQuantity.value;

    // Pré-atualiza o valor na interface para feedback imediato
    if (action === 'increase') {
        layerQuantity.value++;
    } else {
        layerQuantity.value--;
    }

    // Debounce na emissão do evento para o componente pai
    debounce(() => {
        // @ts-ignore
        window.axios
            // @ts-ignore
            .put(route('planogram.api.layers.update', props.layer.id), {
                [action === 'increase' ? 'increaseQuantity' : 'decreaseQuantity']: true,
                quantity: newQuantity,
            })
            .then((response) => {
                const { description, title, variant } = response.data;
                toast({
                    title,
                    description,
                    variant,
                });
            })
            .catch((error) => {
                // Restaura o valor original em caso de erro
                layerQuantity.value = originalQuantity;

                toast({
                    title: 'Erro ao atualizar a quantidade do segmento',
                    description: error.response?.data?.message || 'Erro desconhecido',
                    variant: 'destructive',
                });
            });
    });
};

/**
 * Aumenta a quantidade de layers em um segmento
 */
const onIncreaseQuantity = () => updateLayerQuantity('increase');

/**
 * Diminui a quantidade de layers em um segmento
 */
const onDecreaseQuantity = () => updateLayerQuantity('decrease');
// ----------------------------------------------------
// Lifecycle hooks
// ----------------------------------------------------
// Registra o ouvinte de eventos quando o componente é montado
onMounted(() => {
    // Adiciona listener de teclado para o documento inteiro
    document.addEventListener('keydown', (event) => {
        if (segmentSelected.value) {
            if (event.key === 'ArrowRight') {
                event.preventDefault();
                onIncreaseQuantity();
            } else if (event.key === 'ArrowLeft') {
                event.preventDefault();
                onDecreaseQuantity();
            }
            //Verifica se a tecla pressionada é a tecla de espaço
            else if (event.key === ' ') {
                event.preventDefault();
                // Chama a função de aumentar a quantidade
                onIncreaseQuantity();
            }
            //Verifica se a tecla pressionada é a tecla de espaço
            else if (event.key === 'Backspace') {
                event.preventDefault();
                // Chama a função de diminuir a quantidade
                onDecreaseQuantity();
            }
        }
    });
});

// Remove o ouvinte de eventos quando o componente é desmontado
onUnmounted(() => {
    if (debounceTimer.value) clearTimeout(debounceTimer.value);
});
</script>
