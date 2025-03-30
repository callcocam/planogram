<template>
    <div class="sticky top-0 z-50 border-b bg-white shadow-sm dark:bg-gray-800">
        <div class="p-4">
            <div class="flex items-center justify-between">
                <div class="flex flex-col items-center space-x-8 md:flex-row">
                    <h3 class="flex items-center text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"
                            />
                        </svg>
                        Dimensões da Gôndola
                    </h3>

                     

                    <!-- Controle de Escala -->
                    <div class="flex items-center space-x-2">
                        <label class="text-sm text-gray-600 dark:text-gray-400">Escala:</label>
                        <div class="flex items-center space-x-2">
                            <Button
                                type="buttom"
                                variant="outline"
                                size="icon"
                                @click="updateScale(Math.max(2, scaleFactor - 1))"
                                class="!p-1"
                                :disabled="scaleFactor <= 2"
                            >
                                <Minus class="h-4 w-4" />
                            </Button>
                            <span class="w-8 text-center text-sm font-medium text-gray-700 dark:text-gray-300">{{ scaleFactor }}x</span>
                            <Button
                                type="buttom"
                                variant="outline"
                                size="icon"
                                @click="updateScale(Math.min(10, scaleFactor + 1))"
                                class="!p-1"
                                :disabled="scaleFactor >= 10"
                            >
                                <Plus class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>

                    <!-- Toggle Grid -->
                    <Button
                        type="button"
                        variant="outline"
                        size="icon"
                        @click="showGrid = !showGrid"
                        class="ml-4 !p-1"
                        :class="{ 'bg-gray-100 dark:bg-gray-700': showGrid }"
                    >
                        <Grid class="h-4 w-4" />
                    </Button>
                </div>

                <!-- Botões agrupados -->
                <div class="flex items-center space-x-3">
                    <!-- Botão para abrir o drawer de produtos -->
                    <Button type="button" variant="secondary" v-if="sections.length > 0" @click="invertOrder" class="flex items-center">
                        <ArrowLeftRight class="mr-1 h-4 w-4" />
                        <span class="hidden md:block">Inverter Ordem</span>
                    </Button>

                    <Button type="button" variant="secondary" class="flex items-center">
                        <Plus class="mr-1 h-4 w-4" />
                        <span class="hidden md:block">Adicionar Modulo</span>
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup lang="ts">
// @ts-ignore
import { Button } from '@/components/ui/button';
// @ts-ignore
import { Input } from '@/components/ui/input';
import { ArrowLeftRight, Grid, Minus, Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';
const props = defineProps({
    record: {
        type: Object,
        required: true,
    },
    scaleFactor: {
        type: Number,
        default: 1,
    },
});
const emit = defineEmits(['update:scaleFactor', 'update:showGrid', 'update:invertOrder']);
const showGrid = ref(false);
const sections = computed(() => {
    return props.record.sections || [];
});
const updateScale = (newScale: number) => {
    emit('update:scaleFactor', newScale, props.record.id);
};

const updateShowGrid = (newShowGrid) => {
    emit('update:showGrid', newShowGrid);
};
const invertOrder = () => {
    emit('update:invertOrder', props.record.id);
};
</script>
