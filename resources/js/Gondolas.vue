<template>
    <div>
        <Tabs :default-value="firstGondolaId" v-model="selectedGondolaId" class="w-full" v-if="gondolas.length" @update:modelValue="setActiveTab">
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
import { computed, onMounted, ref } from 'vue';
import Info from './components/gondola/Info.vue';
import Sections from './components/sections/Sections.vue';
import MovableContainer from './MovableContainer.vue';

const props = defineProps({
    planogram: {
        type: Object,
        required: true,
    },
});
const showSectionModal = ref(false);

// Mapeamento simplificado das propriedades da gôndola
const gondolas = computed(() => {
    if (!props.planogram?.gondolas?.length) return [];

    return props.planogram.gondolas;
});
const selectedGondolaId = ref(gondolas.value.length > 0 ? gondolas.value[0].id : '');

// ID da primeira gôndola (se existir)
const firstGondolaId = ref(gondolas.value.length > 0 ? gondolas.value[0].id : '');
// Atualiza as seções da gôndola
const updateSections = (sections: any, gondolaId: any) => {
    const sectionIds = sections.map((section: any) => section.id);
    // @ts-ignore
    router.put(
        route('planogram.sections.reorder', gondolaId),
        {
            sections: sectionIds,
        },
        {
            preserveState: false,
            preserveScroll: true,
            onSuccess: () => {},
        },
    );
};

// Atualiza o ID da gôndola ativa
const setActiveTab = (gondolaId: any) => {
    selectedGondolaId.value = gondolaId;
};
// Atualiza o fator de escala da gôndola
const updateScaleFactor = (scaleFactor: number, gondolaId: any) => {
    // @ts-ignore
    router.put(
        route('planogram.gondolas.updateScaleFactor', gondolaId),
        {
            scale_factor: scaleFactor,
        },
        {
            preserveState: false,
            preserveScroll: true,
            onSuccess: () => {},
        },
    );
};

// Atualiza a ordem das gôndolas
const updateInvertOrder = (gondolaId: any) => {
    // @ts-ignore
    router.put(
        route('planogram.sections.updateInvertOrder', gondolaId),
        {
            invert_order: true,
        },
        {
            preserveState: false,
            preserveScroll: true,
            onSuccess: () => {
                console.log('Inverted', gondolaId);
                selectedGondolaId.value = gondolaId;
            },
            onFinish: () => {
                console.log('Prefetched', gondolaId);
                selectedGondolaId.value = gondolaId;
            },
        },
    );
};

onMounted(() => {
    const { gondola } = route().queryParams;

    if (gondola) {
        selectedGondolaId.value = gondola;
    } else {
        if(!gondolas.value.length) return;
        // Se não houver gôndola selecionada, selecione a primeira
        selectedGondolaId.value = gondolas.value[0].id;
    }
});
</script>
