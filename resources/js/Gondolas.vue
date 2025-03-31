<template>
    <div>
        <Tabs v-model="selectedGondolaId" class="w-full" v-if="gondolas.length">
            <TabsList class="flex items-center justify-around">
                <TabsTrigger v-for="gondola in gondolas" :key="gondola.id" :value="gondola.id" class="w-full truncate text-sm">
                    {{ gondola.name }}
                </TabsTrigger>
            </TabsList>
            <TabsContent v-for="gondola in gondolas" :key="gondola.id" :value="gondola.id" class="mt-4">
                <Info
                    :record="gondola"
                    :scale-factor="gondola.scale_factor"
                    @update:scaleFactor="updateScaleFactor"
                    @update:invertOrder="updateInvertOrder"
                />
                <div class="flex min-h-screen w-full flex-col gap-6 border md:flex-row">
                    <!-- Area de trabalho -->
                    <MovableContainer :storage-id="gondola.id" :scale-factor="gondola.scale_factor">
                        <Sections :gondola="gondola" :scale-factor="gondola.scale_factor" @sections-reordered="updateSections" />
                    </MovableContainer>
                </div>
            </TabsContent>
        </Tabs>
        <div v-else class="flex h-full w-full flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-8">
            <div class="text-center">
                <div class="relative mx-auto mb-4 h-24 w-24">
                    <ShoppingBagIcon class="mx-auto h-16 w-16 text-gray-400" />
                    <span class="absolute -right-1 -top-1 flex h-6 w-6 items-center justify-center rounded-full bg-primary text-xs text-white">
                        <PlusIcon class="h-4 w-4" />
                    </span>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Nenhuma gôndola encontrada</h3>
                <p class="mt-2 max-w-md text-sm text-gray-500">
                    As gôndolas são essenciais para organizar seus produtos no planograma. Adicione sua primeira gôndola para começar a criar o layout
                    perfeito para sua loja.
                </p>
                <div class="mt-6">
                    <Button @click="$emit('add-gondola')" size="default" class="shadow-sm">
                        <PlusIcon class="mr-2 h-4 w-4" />
                        Adicionar Gôndola
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
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { router } from '@inertiajs/vue3';
import { PlusIcon, ShoppingBagIcon } from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';
import Info from './components/gondola/Info.vue';
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
const gondolas = computed(() => {
    return props.planogram?.gondolas?.length ? props.planogram.gondolas : [];
});

// ID da gôndola selecionada
const selectedGondolaId = ref('');

// Inicializa o componente
onMounted(() => {
    initializeSelectedGondola();
});

// Função para inicializar a gôndola selecionada
function initializeSelectedGondola() {
    const { gondola } = route().params || route().queryParams || {};

    // Verifica se existe uma gôndola na URL e se ela existe no array de gôndolas
    if (gondola && gondolas.value.some((g) => g.id === gondola)) {
        selectedGondolaId.value = gondola;
    } else if (gondolas.value.length > 0) {
        // Caso contrário, seleciona a primeira gôndola
        selectedGondolaId.value = gondolas.value[0].id;
    }

    console.log('Gôndola selecionada:', selectedGondolaId.value);
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
        route('planogram.sections.reorder', gondolaId),
        {
            sections: sectionIds,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

// Atualiza o fator de escala da gôndola
const updateScaleFactor = (scaleFactor, gondolaId) => {
    router.put(
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
        route('planogram.sections.updateInvertOrder', gondolaId),
        {
            invert_order: true,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};
</script>
