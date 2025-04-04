// Info.vue - Componente Principal
<script setup lang="ts">
// @ts-ignore
import { Button } from '@/components/ui/button';
// @ts-ignore
import { ArrowLeftRight, Grid, Minus, Plus } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import Category from './Category.vue';
import Popover from './Popover.vue';

const props = defineProps({
    record: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        default: () => [],
    },
    scaleFactor: {
        type: Number,
        default: 1,
    },
});

const emit = defineEmits(['update:scaleFactor', 'update:showGrid', 'update:invertOrder', 'update:category']);

// Estado local para filtros e opções de visualização
const showGrid = ref(false);
const filters = ref({
    category: null,
});

// Computado para obter as seções do registro atual
const sections = computed(() => {
    return props.record.sections || [];
});

// Atualizar a escala e emitir o evento
const updateScale = (newScale: number) => {
    emit('update:scaleFactor', newScale, props.record.id);
};

// Atualizar a grade e emitir o evento
const updateShowGrid = (newShowGrid) => {
    showGrid.value = newShowGrid;
    emit('update:showGrid', newShowGrid);
};

// Inverter a ordem das seções
const invertOrder = () => {
    emit('update:invertOrder', props.record.id);
};

// Atualizar a categoria selecionada
const updateCategory = (category) => {
    filters.value.category = category;
    emit('update:category', category);
};

// Limpar todos os filtros
const clearFilters = () => {
    filters.value.category = null;
    showGrid.value = false;

    // Emitir eventos para atualizar o componente pai
    emit('update:category', null);
    emit('update:showGrid', false);
};

// Observar mudanças externas nos filtros
watch(
    () => props.scaleFactor,
    (newValue) => {
        // Atualizar a escala local se for alterada externamente
    },
    { immediate: true },
);
</script>

<template>
    <div class="sticky top-0 z-50 border-b bg-white shadow-sm dark:bg-gray-800 dark:border-gray-700">
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
                                type="button"
                                variant="outline"
                                size="icon"
                                @click="updateScale(Math.max(2, scaleFactor - 1))"
                                class="!p-1 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                                :disabled="scaleFactor <= 2"
                            >
                                <Minus class="h-4 w-4" />
                            </Button>
                            <span class="w-8 text-center text-sm font-medium text-gray-700 dark:text-gray-300">{{ scaleFactor }}x</span>
                            <Button
                                type="button"
                                variant="outline"
                                size="icon"
                                @click="updateScale(Math.min(10, scaleFactor + 1))"
                                class="!p-1 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
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
                        @click="updateShowGrid(!showGrid)"
                        class="ml-4 !p-1 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                        :class="{ 'bg-gray-100 dark:bg-gray-700': showGrid }"
                    >
                        <Grid class="h-4 w-4" />
                    </Button>
                    <!-- Filtros -->
                    <div class="flex items-center space-x-2" v-if="categories.length > 0">
                        <label class="text-sm text-gray-600 dark:text-gray-400">Filtros:</label>
                        <Popover @clear-filters="clearFilters" :has-active-filters="!!filters.category">
                            <Category
                                class="w-full"
                                :categories="categories"
                                v-model="filters.category"
                                @update:model-value="updateCategory"
                                :clearable="true"
                            />
                        </Popover>
                    </div>
                </div>

                <!-- Botões agrupados -->
                <div class="flex items-center space-x-3">
                    <!-- Botão para inverter ordem -->
                    <Button 
                        type="button" 
                        variant="secondary" 
                        v-if="sections.length > 0" 
                        @click="invertOrder" 
                        class="flex items-center dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                    >
                        <ArrowLeftRight class="mr-1 h-4 w-4" />
                        <span class="hidden md:block">Inverter Ordem</span>
                    </Button>

                    <Button 
                        type="button" 
                        variant="secondary" 
                        class="flex items-center dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                    >
                        <Plus class="mr-1 h-4 w-4" />
                        <span class="hidden md:block">Adicionar Modulo</span>
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
