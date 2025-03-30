<template>
    <div class="mb-6 border-b pb-4" v-if="planogram">
        <!-- Modal para adicionar gôndola -->
        <GondolaModal
            :open="showGondolaModal"
            :planogram-id="planogram.id"
            :planogram="planogram"
            @close="handleCloseGondolaModal"
            @gondola-added="handleGondolaAdded"
            @update:open="showGondolaModal = $event"
        />

        <div class="flex items-center justify-between">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <h2 class="text-2xl font-bold tracking-tight">{{ planogram.name }}</h2>
                    <Badge :variant="getStatusVariant(planogram.status)">
                        {{ planogram.status }}
                    </Badge>
                </div>
                <p class="text-sm text-muted-foreground">ID: {{ planogram.id }} | Criado em: {{ formatDate(planogram.created_at) }}</p>
            </div>

            <div class="flex items-center gap-2">
                <Button variant="outline" size="sm" @click="openAddGondolaModal">
                    <PlusCircleIcon class="mr-2 h-4 w-4" />
                    Adicionar Gôndola
                </Button>
                <Button variant="outline" size="sm">
                    <PencilIcon class="mr-2 h-4 w-4" />
                    Editar
                </Button>
                <Button size="sm">
                    <SaveIcon class="mr-2 h-4 w-4" />
                    Salvar
                </Button>
            </div>
        </div>

        <!-- <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-medium">Tenant</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="text-lg font-semibold">{{ planogram.tenant.name }}</div>
                    <div class="text-sm text-muted-foreground">{{ planogram.tenant.email }}</div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-medium">Detalhes</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="space-y-1">
                        <div class="flex justify-between">
                            <span class="text-sm text-muted-foreground">Slug:</span>
                            <span class="text-sm font-medium">{{ planogram.slug }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-muted-foreground">Atualizado:</span>
                            <span class="text-sm font-medium">{{ formatDate(planogram.updated_at) }}</span>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div> -->
    </div>
</template>

<script setup>
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { PencilIcon, PlusCircleIcon, SaveIcon } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import GondolaModal from './components/modal/gondola/Add.vue';

// Props para receber os dados do planograma do Inertia
const props = defineProps({
    planogram: {
        type: Object,
        required: true,
    },
    showGondolaModalProp: {
        type: Boolean,
        default: false,
    },
});

// Emitir eventos para o componente pai
const emit = defineEmits(['close', 'gondola-added']);

watch(
    () => props.showGondolaModalProp,
    (newValue) => {
        showGondolaModal.value = newValue;
    },
);

// Estado para controlar a visibilidade dos modais
const showGondolaModal = ref(false);

// Função para abrir o modal de adicionar gôndola
const openAddGondolaModal = () => {
    showGondolaModal.value = true;
};

// Função para fechar o modal de adicionar gôndola
const handleCloseGondolaModal = () => {
    showGondolaModal.value = false;
    // Emitir evento para o componente pai
    emit('close');
};

// Função para lidar com o evento de gôndola adicionada
const handleGondolaAdded = () => {
    // Aqui você pode atualizar os dados do planograma ou recarregar a página
    // Inertia.reload({ only: ['planogram'] });
    // window.location.reload(); // Solução temporária
};

// Função para formatar datas
const formatDate = (dateString) => {
    if (!dateString) return 'N/A';

    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Determina a variante de cor do badge com base no status
const getStatusVariant = (status) => {
    switch (status?.toLowerCase()) {
        case 'published':
            return 'success';
        case 'draft':
            return 'secondary';
        default:
            return 'default';
    }
};
</script>
