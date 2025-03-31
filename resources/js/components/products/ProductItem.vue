<template>
    <li
        class="cursor-move px-4 py-4 hover:bg-gray-50 dark:hover:bg-gray-800"
        @click="$emit('click', product)"
        draggable="true"
        @dragstart="onDragStart"
        :data-product-id="product.id"
    >
        <div class="flex items-center space-x-4">
            <div
                class="flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-md border border-gray-200 bg-gray-100 p-1 dark:border-gray-700 dark:bg-gray-800"
            >
                <img v-if="product.image_url" :src="product.image_url" :alt="product.name" class="max-h-full max-w-full object-contain" />
                <Package v-else class="h-8 w-8 text-gray-400" />
            </div>

            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-medium text-gray-900 dark:text-white">
                    {{ product.name }}
                </p>
                <div class="mt-1 flex items-center text-xs text-gray-500">
                    <span>{{ product.width }}×{{ product.height }}×{{ product.depth }} cm</span>
                    <span v-if="product.sku" class="ml-2 rounded bg-gray-100 px-1.5 py-0.5 dark:bg-gray-800"> SKU: {{ product.sku }} </span>
                </div>
            </div>

            <div class="flex items-center">
                <Button size="sm" variant="ghost" @click.stop="$emit('view-stats')">
                    <BarChart2 class="mr-1 h-5 w-5" />
                    <span>Ver estatísticas</span>
                </Button>
            </div>
        </div>
    </li>
</template>

<script setup>
import { Button } from '@/components/ui/button';
import { BarChart2, Package } from 'lucide-vue-next';
import { defineEmits, defineProps } from 'vue';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['click', 'drag-start', 'view-stats']);

function onDragStart(event) {
    emit('drag-start', event, props.product);
}
</script>
