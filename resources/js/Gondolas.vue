<template>
    <Tabs :default-value="firstGondolaId" class="w-full">
        <TabsList
            class="grid w-full"
            :class="{
                'grid-cols-2': gondolas.length === 2,
                'grid-cols-3': gondolas.length === 3,
                'grid-cols-4': gondolas.length === 4,
                'grid-cols-5': gondolas.length >= 5,
            }"
        >
            <TabsTrigger v-for="gondola in gondolas" :key="gondola.id" :value="gondola.id" class="truncate text-sm">
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
</template>

<script setup>
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { PencilIcon, PlusIcon } from 'lucide-vue-next';
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
