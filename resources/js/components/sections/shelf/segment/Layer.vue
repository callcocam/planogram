<template>
    <div class="segment group flex items-end" :style="layerStyle">
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
 * Aumenta a quantidade de layers em um segmento
 */
const onIncreaseQuantity = () => {
    if (!segmentSelected.value) return;

    // Incrementa o valor imediatamente na UI
    const currentQuantity = layerQuantity.value;
    // Debounce na emissão do evento para o componente pai
    // @ts-ignore
    window.axios
        // @ts-ignore
        .put(route('planogram.api.layers.update', props.layer.id), {
            increaseQuantity: true,
            quantity: currentQuantity + 1,
        })
        .then((response) => {
            // Atualiza a quantidade no componente pai
            layerQuantity.value++;
            const { description, title, variant } = response.data;
            // Atualiza a quantidade no componente pai
            toast({
                title,
                description,
                variant,
            });
        })
        .catch((error) => {
            // console.error('Erro ao atualizar a quantidade do segmento:', error);
            toast({
                title: 'Erro ao atualizar a quantidade do segmento',
                description: error.response.data.message,
                variant: 'destructive',
            });
        });
};

/**
 * Diminui a quantidade de layers em um segmento
 */
const onDecreaseQuantity = () => {
    if (!segmentSelected.value) return;
    if (layerQuantity.value > 1) {
        // Decrementa o valor imediatamente na UI
        const currentQuantity = layerQuantity.value - 1;
        // @ts-ignore
        window.axios
            // @ts-ignore
            .put(route('planogram.api.layers.update', props.layer.id), {
                decreaseQuantity: true,
                quantity: currentQuantity,
            })
            .then((response) => {
                // Atualiza a quantidade no componente pai
                layerQuantity.value--;
                const { description, title, variant } = response.data;
                // Atualiza a quantidade no componente pai
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
        // Debounce na emissão do evento para o componente pai
    }
};
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
        }
    });
});

// Remove o ouvinte de eventos quando o componente é desmontado
onUnmounted(() => {
    if (debounceTimer.value) clearTimeout(debounceTimer.value);
});
</script>
