<template>
    <div class="flex h-full w-full gap-6 overflow-hidden">
        <!-- Barra lateral esquerda com componente Products separado -->
        <Products :categories="categories" @select-product="handleProductSelect" @drag-start="handleDragStart" @view-stats="showProductStats" />

        <!-- Área central rolável (vertical e horizontal) -->
        <div class="flex h-full w-full flex-col gap-6 overflow-x-auto overflow-y-auto">
            <Tabs v-model="selectedGondolaId" class="w-full" v-if="gondolas.length">
                <TabsList class="sticky top-0 z-10 flex items-center justify-around bg-white dark:bg-gray-800">
                    <TabsTrigger v-for="gondola in gondolas" :key="gondola.id" :value="gondola.id" class="w-full truncate text-sm dark:text-gray-200 dark:data-[state=active]:bg-gray-700 dark:data-[state=active]:text-white">
                        {{ gondola.name }}
                    </TabsTrigger>
                </TabsList>
                <TabsContent v-for="gondola in gondolas" :key="gondola.id" :value="gondola.id" class="mt-4">
                    <Info
                        :record="gondola"
                        :categories="categories"
                        :scale-factor="gondola.scale_factor"
                        @update:scaleFactor="updateScaleFactor"
                        @update:invertOrder="updateInvertOrder"
                        @update:category="updateCategory"
                    />
                    <div class="w-full flex-col gap-6 overflow-visible border dark:border-gray-700 md:flex-row">
                        <!-- Area de trabalho -->
                        <MovableContainer :storage-id="gondola.id" :scale-factor="gondola.scale_factor">
                            <Sections
                                :gondola="gondola"
                                :scale-factor="gondola.scale_factor"
                                :selected-category="selectedCategory"
                                @sections-reordered="updateSections"
                                @product-drop="handleProductDrop"
                                @segment-select="handleSegmentSelected"
                            />
                        </MovableContainer>
                    </div>
                </TabsContent>
            </Tabs>
            <div
                v-else
                class="flex h-full w-full flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-8 dark:border-gray-700 dark:bg-gray-800"
            >
                <div class="text-center">
                    <div class="relative mx-auto mb-4 h-24 w-24">
                        <ShoppingBagIcon class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-500" />
                        <span class="absolute -right-1 -top-1 flex h-6 w-6 items-center justify-center rounded-full bg-primary text-xs text-white">
                            <PlusIcon class="h-4 w-4" />
                        </span>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Nenhuma gôndola encontrada</h3>
                    <p class="mt-2 max-w-md text-sm text-gray-500 dark:text-gray-400">
                        As gôndolas são essenciais para organizar seus produtos no planograma. Adicione sua primeira gôndola para começar a criar o
                        layout perfeito para sua loja.
                    </p>
                    <div class="mt-6">
                        <Button @click="$emit('add-gondola')" size="default" class="shadow-sm dark:bg-gray-700 dark:text-gray-100">
                            <PlusIcon class="mr-2 h-4 w-4" />
                            Adicionar Gôndola
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barra lateral direita fixa -->
        <div class="sticky top-0 flex h-screen w-64 flex-shrink-0 flex-col overflow-hidden rounded-lg border bg-gray-50 dark:bg-gray-800 dark:border-gray-700">
            <div class="border-b border-gray-200 bg-white p-3 dark:bg-gray-800 dark:border-gray-700">
                <h3 class="text-center text-lg font-medium text-gray-800 dark:text-gray-100">Propriedades</h3>
            </div>
            <div class="flex-1 p-3">
                <div v-if="selectedProducts.length" class="rounded-md bg-white p-3 shadow-sm dark:bg-gray-700">
                    <p class="text-gray-800 dark:text-gray-200">{{ selectedProducts.length }} produto(s) selecionado(s)</p>
                    <div v-for="product in selectedProducts" :key="product.id" class="flex items-center gap-2">
                        <img :src="product.image_url" alt="" class="h-16 w-16 rounded-md object-cover border dark:border-gray-600" />
                        <div class="flex flex-col">
                            <h4 class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ product.name }}</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400">SKU: {{ product.sku }}</p>
                        </div>
                    </div> 
                </div>
                <div v-else class="flex h-full items-center justify-center p-4 text-center text-gray-400 dark:text-gray-500">
                    Selecione um produto para ver suas propriedades
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
// @ts-ignore
import { Button } from '@/components/ui/button';
// @ts-ignore
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { router } from '@inertiajs/vue3';
import { PlusIcon, ShoppingBagIcon } from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';
import Info from './components/gondola/Info.vue';
import Products from './components/products/Products.vue';
import Sections from './components/sections/Sections.vue';
import MovableContainer from './MovableContainer.vue';

const emit = defineEmits(['add-gondola']);

const props = defineProps({
    planogram: {
        type: Object,
        required: true,
    },
});

// Mapeamento das propriedades da gôndola
const gondolas = ref<any[]>(props.planogram.gondolas || []);

// ID da gôndola selecionada
const selectedGondolaId = ref('');
// Produto selecionado
const selectedProducts = ref([]) as any;

// Busca categorias disponíveis
const categories = ref<any[]>([]);
// Categoria selecionada
const selectedCategory = ref<Record<string, any> | undefined | null>(undefined);

const segments = ref<any[]>([]);
// Inicializa o componente
onMounted(() => {
    initializeSelectedGondola();
});

// Função para inicializar a gôndola selecionada
function initializeSelectedGondola() {
    // @ts-ignore
    const { gondola } = route().params || route().queryParams || {};

    // Verifica se existe uma gôndola na URL e se ela existe no array de gôndolas
    if (gondola && gondolas.value.some((g) => g.id === gondola)) {
        selectedGondolaId.value = gondola;
    } else if (gondolas.value.length > 0) {
        // Caso contrário, seleciona a primeira gôndola
        selectedGondolaId.value = gondolas.value[0].id;
    }
}

// Manipula a seleção de um segmento
function handleSegmentSelected(segment: any) { 
    if(!segment.category) {
        selectedCategory.value = null;
    }
    if (segment.isMultiSelect) {
        // Se a seleção for múltipla, adiciona o segmento à lista
        const index = segments.value.findIndex((s) => s.id === segment.id);
        if (segment.segmentSelected) {
            if (index === -1) {
                segments.value.push(segment);
                selectedProducts.value.push(segment.product);
            }
        } else {
            if (index !== -1) {
                segments.value.splice(index, 1);
                selectedProducts.value.splice(index, 1);
            }
        }
    } else {
        segments.value = [segment];
        selectedProducts.value = [segment.product];
    }
    // Implementação futura - exibir detalhes do segmento
}

// Manipula a seleção de um produto
function handleProductSelect(product) {
    console.log('Produto selecionado:', product);
}

// Manipula o início do arrasto de um produto
function handleDragStart(event, product) {
    // console.log('Iniciando arrasto de produto:', event );
    // Esta função recebe o evento do componente Products
    // console.log('Iniciando arrasto de produto:', product);
}

// Manipula o drop de um produto em uma seção
function handleProductDrop(product, sectionId) {
    console.log('Produto adicionado à seção:', product, 'Seção:', sectionId);

    // Implementação da chamada ao backend
    router.post(
        // @ts-ignore
        route('planogram.sections.addProduct', { section: sectionId }),
        {
            product_id: product.id,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
}

// Exibe estatísticas do produto
function showProductStats(product) {
    console.log('Mostrar estatísticas para:', product);
    // Implementação futura - modal ou painel de estatísticas
}

// Observa mudanças nas gôndolas para garantir que sempre haja uma selecionada
watch(
    () => gondolas.value,
    (newGondolas) => {
        if (newGondolas.length > 0 && !selectedGondolaId.value) {
            selectedGondolaId.value = newGondolas[0].id;
        } else if (newGondolas.length > 0 && !newGondolas.some((g) => g.id === selectedGondolaId.value)) {
            // Se a gôndola selecionada não existir mais, seleciona a primeira
            selectedGondolaId.value = newGondolas[0].id;
        }
    },
    { deep: true },
);

// Atualiza as seções da gôndola
const updateSections = (sections, gondolaId) => {
    const sectionIds = sections.map((section) => section.id);

    router.put(
        // @ts-ignore
        route('planogram.sections.reorder', gondolaId),
        {
            sections: sectionIds,
        },
        {
            preserveState: false,
            preserveScroll: true,
        },
    );
};

// Atualiza o fator de escala da gôndola
const updateScaleFactor = (scaleFactor, gondolaId) => {
    router.put(
        // @ts-ignore
        route('planogram.gondolas.updateScaleFactor', gondolaId),
        {
            scale_factor: scaleFactor,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

// Atualiza a ordem das gôndolas
const updateInvertOrder = (gondolaId) => {
    router.put(
        // @ts-ignore
        route('planogram.sections.updateInvertOrder', gondolaId),
        {
            invert_order: true,
        },
        {
            preserveState: false,
            preserveScroll: true,
        },
    );
};

// Observa mudanças nas categorias para garantir que sempre haja uma selecionada
const updateCategory = (category) => {
    // Atualiza a categoria selecionada

    selectedCategory.value = category;
    if (!category) {
        segments.value = [];
        selectedProducts.value = [];
    }
};

/**
 * Busca categorias disponíveis
 */
async function fetchCategories() {
    try {
        // @ts-ignore
        const response = await window.axios.get(route('api.categories.index'));
        categories.value = response.data;
    } catch (error) {
        console.error('Erro ao carregar categorias:', error);
    }
}

onMounted(async () => {
    // Carrega categorias e produtos ao montar o componente
    await fetchCategories();
});
</script>

<style scoped>
/* Estilo para garantir que a área central possa rolar enquanto as laterais ficam fixas */
.overflow-y-auto {
    max-height: 100vh;
    scrollbar-width: thin;
    scrollbar-color: #e2e8f0 #f8fafc;
}

.overflow-x-auto {
    scrollbar-width: thin;
    scrollbar-color: #e2e8f0 #f8fafc;
}

.overflow-y-auto::-webkit-scrollbar,
.overflow-x-auto::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.overflow-y-auto::-webkit-scrollbar-track,
.overflow-x-auto::-webkit-scrollbar-track {
    background: #f8fafc;
}

.overflow-y-auto::-webkit-scrollbar-thumb,
.overflow-x-auto::-webkit-scrollbar-thumb {
    background-color: #e2e8f0;
    border-radius: 4px;
}

/* Estilos para o modo escuro da barra de rolagem */
@media (prefers-color-scheme: dark) {
    .overflow-y-auto {
        scrollbar-color: #4b5563 #1f2937;
    }
    
    .overflow-x-auto {
        scrollbar-color: #4b5563 #1f2937;
    }
    
    .overflow-y-auto::-webkit-scrollbar-track,
    .overflow-x-auto::-webkit-scrollbar-track {
        background: #1f2937;
    }
    
    .overflow-y-auto::-webkit-scrollbar-thumb,
    .overflow-x-auto::-webkit-scrollbar-thumb {
        background-color: #4b5563;
    }
}
</style>
