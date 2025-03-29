<template>
    <div>
        <Tabs :default-value="firstGondolaId" class="w-full" v-if="gondolas.length">
            <TabsList class="flex items-center justify-around">
                <TabsTrigger v-for="gondola in gondolas" :key="gondola.id" :value="gondola.id" class="w-full truncate text-sm">
                    {{ gondola.name }}
                </TabsTrigger>
            </TabsList>

            <TabsContent v-for="gondola in gondolas" :key="gondola.id" :value="gondola.id" class="mt-4">
                <div class="flex flex-col gap-6 md:flex-row">
                    <div class="mt-1 flex w-full justify-end space-x-2">
                        <Button variant="outline" size="sm">
                            <PencilIcon class="mr-2 h-4 w-4" />
                            Editar
                        </Button>
                        <Button size="sm">
                            <PlusIcon class="mr-2 h-4 w-4" />
                            Adicionar Seção
                        </Button>
                    </div>
                </div>
            </TabsContent>
        </Tabs>
        <div v-else class="flex flex-col h-full w-full items-center justify-center p-8 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50">
            <div class="text-center">
                <div class="relative mx-auto h-24 w-24 mb-4">
                    <ShoppingBagIcon class="mx-auto h-16 w-16 text-gray-400" />
                    <span class="absolute -right-1 -top-1 flex h-6 w-6 items-center justify-center rounded-full bg-primary text-white text-xs">
                        <PlusIcon class="h-4 w-4" />
                    </span>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Nenhuma gôndola encontrada</h3>
                <p class="mt-2 text-sm text-gray-500 max-w-md">
                    As gôndolas são essenciais para organizar seus produtos no planograma. 
                    Adicione sua primeira gôndola para começar a criar o layout perfeito para sua loja.
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

<script setup>
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { PencilIcon, PlusIcon, ShoppingBagIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps({
    planogram: {
        type: Object,
        required: true,
    },
});
const showSectionModal = ref(false);
const selectedGondolaId = ref(null);

// Mapeamento simplificado das propriedades da gôndola
const gondolas = computed(() => {
    if (!props.planogram?.gondolas?.length) return [];

    return props.planogram.gondolas.map((gondola) => ({
        id: gondola.id,
        name: gondola.name,
        type: gondola.type,
        width: gondola.width,
        height: gondola.height,
        depth: gondola.depth,
        image: gondola.image,
    }));
});

// ID da primeira gôndola (se existir)
const firstGondolaId = computed(() => {
    return gondolas.value.length > 0 ? gondolas.value[0].id : '';
});

// Função para abrir o modal de adicionar seção à gôndola
const openAddSectionModal = (gondolaId) => {
    selectedGondolaId.value = gondolaId;
    showSectionModal.value = true;
};

// Função para fechar o modal de adicionar seção à gôndola
const handleCloseSectionModal = () => {
    showSectionModal.value = false;
    // Não limpe o selectedGondolaId aqui, só quando fechar o modal
};
// Função para lidar com o evento de seção adicionada
const handleSectionAdded = () => {
    // Aqui você pode atualizar os dados da gôndola ou recarregar a página
    // Inertia.reload({ only: ['planogram'] });
    window.location.reload(); // Solução temporária
};
</script>
