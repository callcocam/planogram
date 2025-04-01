<template>
    <div class="segment flex items-end " :style="layerStyle">
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
import { computed } from 'vue';
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
});

const layerQuantity = computed(() => {
    return props.layer.quantity;
});

const layerStyle = computed(() => { 
    const layerHeight = props.layer.product.height * props.scaleFactor;
    const layerWidth = props.layer.product.width * props.shelf.quantity * props.scaleFactor;
    return {
        height: `${layerHeight}px`,
        width: `${layerWidth}px`, 
        position: 'relative' as const,
    };
});
</script>
