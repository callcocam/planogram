<template>
    <div class="gondola-view">
        <!-- Card principal da gôndola -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
            <!-- Cabeçalho com informações da gôndola -->
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ gondola.name }}</h3>
                        <span class="ml-2 px-2 py-1 text-xs rounded-full" 
                              :class="gondola.status === 'published' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                            {{ gondola.status === 'published' ? 'Publicado' : 'Rascunho' }}
                        </span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <Button variant="outline" size="sm">
                            <PencilIcon class="h-4 w-4 mr-1" />
                            Editar
                        </Button>
                    </div>
                </div>
                
                <!-- Dimensões e detalhes -->
                <div class="mt-2 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="flex items-center">
                        <RulerIcon class="h-4 w-4 text-gray-500 mr-2" />
                        <span class="text-sm text-gray-700 dark:text-gray-300">
                            {{ gondola.width }}cm × {{ gondola.height }}cm × {{ gondola.depth }}cm
                        </span>
                    </div>
                    <div class="flex items-center">
                        <MapPinIcon class="h-4 w-4 text-gray-500 mr-2" />
                        <span class="text-sm text-gray-700 dark:text-gray-300">
                            {{ gondola.location || 'Sem localização' }}
                        </span>
                    </div>
                    <div class="flex items-center">
                        <ScaleIcon class="h-4 w-4 text-gray-500 mr-2" />
                        <span class="text-sm text-gray-700 dark:text-gray-300">
                            Fator de escala: {{ gondola.scale_factor || 1 }}
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Controles de visualização -->
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2">
                            <Button
                                type="button"
                                variant="outline"
                                size="icon"
                                class="!p-1"
                                :disabled="scaleFactor <= 1"
                                @click="updateScale(Math.max(1, scaleFactor - 1))"
                            >
                                <MinusIcon class="h-4 w-4" />
                            </Button>
                            <span class="w-8 text-center text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ scaleFactor }}x
                            </span>
                            <Button
                                type="button"
                                variant="outline"
                                size="icon"
                                class="!p-1"
                                :disabled="scaleFactor >= 10"
                                @click="updateScale(Math.min(10, scaleFactor + 1))"
                            >
                                <PlusIcon class="h-4 w-4" />
                            </Button>
                        </div>
                        
                        <Button variant="outline" size="sm" @click="showGrid = !showGrid" :class="{ 'bg-gray-100': showGrid }">
                            <GridIcon class="h-4 w-4 mr-1" />
                            {{ showGrid ? 'Ocultar Grade' : 'Mostrar Grade' }}
                        </Button>
                    </div>
                    
                    <div>
                        <Button @click="$emit('add-section')" variant="default" size="sm">
                            <PlusIcon class="h-4 w-4 mr-1" />
                            Adicionar Seção
                        </Button>
                    </div>
                </div>
            </div>
            
            <!-- Conteúdo principal - Lista de Seções -->
            <div class="p-4">
                <SectionsList 
                    :sections="gondola.sections || []" 
                    :scale-factor="scaleFactor" 
                    :base-height="gondola.base_height || 15"
                    :show-grid="showGrid"
                    @add-section="$emit('add-section')"
                    @edit-section="$emit('edit-section', $event)"
                    @delete-section="$emit('delete-section', $event)"
                    @select-shelf="$emit('select-shelf', $event)"
                    @add-shelf="$emit('add-shelf', $event)"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { Button } from '@/components/ui/button';
import { GridIcon, MapPinIcon, MinusIcon, PencilIcon, PlusIcon, RulerIcon, ScaleIcon } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import SectionsList from '../sections/SectionsList.vue';

const props = defineProps({
    gondola: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits([
    'add-section', 
    'edit-section', 
    'delete-section', 
    'update-scale',
    'select-shelf',
    'add-shelf'
]);

// Estado
const scaleFactor = ref(props.gondola.scale_factor || 3);
const showGrid = ref(true);

// Monitorar mudanças no fator de escala do prop gondola
watch(() => props.gondola.scale_factor, (newValue) => {
    if (newValue !== undefined && newValue !== scaleFactor.value) {
        scaleFactor.value = newValue;
    }
});

// Função para atualizar a escala
function updateScale(value) {
    scaleFactor.value = value;
    emit('update-scale', value);
}
</script>

<style scoped>
/* Qualquer estilo específico adicional pode ser adicionado aqui */
</style>