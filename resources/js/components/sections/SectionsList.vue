<template>
    <div class="sections-list-container">
        <!-- Título da seção de gôndolas -->
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                </svg>
                Seções ({{ sections.length }})
            </h3>
            <Button size="sm" variant="outline" @click="$emit('add-section')">
                <PlusIcon class="h-4 w-4 mr-1" />
                Adicionar Seção
            </Button>
        </div>

        <!-- Seções vazias -->
        <div v-if="!sections.length" class="flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded p-8">
            <div class="text-center">
                <LayoutIcon class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma seção encontrada</h3>
                <p class="mt-1 text-sm text-gray-500">Adicione seções para organizar os produtos na gôndola</p>
                <div class="mt-4">
                    <Button @click="$emit('add-section')">
                        <PlusIcon class="mr-2 h-4 w-4" />
                        Adicionar Seção
                    </Button>
                </div>
            </div>
        </div>

        <!-- Lista de seções -->
        <div v-else class="sections-grid">
            <div
                v-for="section in sections"
                :key="section.id"
                class="section-item relative"
            >
                <Section
                    :section="section"
                    :scale-factor="scaleFactor"
                    :base-height-cm="baseHeight"
                    :show-grid="showGrid"
                    @select-shelf="$emit('select-shelf', $event)"
                    @add-shelf="$emit('add-shelf', section)"
                />
                
                <!-- Sobreposição com ações -->
                <div class="section-overlay absolute inset-0 opacity-0 hover:opacity-100 transition-opacity bg-black/10 flex items-center justify-center">
                    <div class="actions-panel flex space-x-2">
                        <Button size="icon" variant="secondary" @click="editSection(section)">
                            <PencilIcon class="h-4 w-4" />
                        </Button>
                        <Button size="icon" variant="destructive" @click="confirmDelete(section)">
                            <TrashIcon class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Button } from '@/components/ui/button';
import { LayoutIcon, PencilIcon, PlusIcon, TrashIcon } from 'lucide-vue-next';
import { ref } from 'vue';
import Section from './Section.vue';

const props = defineProps({
    sections: {
        type: Array,
        default: () => [],
    },
    scaleFactor: {
        type: Number,
        default: 3,
    },
    baseHeight: {
        type: Number,
        default: 15,
    },
    showGrid: {
        type: Boolean,
        default: true,
    }
});

const emit = defineEmits(['add-section', 'edit-section', 'delete-section']);

function editSection(section) {
    emit('edit-section', section);
}

function confirmDelete(section) {
    if (confirm(`Tem certeza que deseja excluir a seção ${section.name || 'selecionada'}?`)) {
        emit('delete-section', section);
    }
}
</script>

<style scoped>
.sections-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
}

.section-item {
    transition: transform 0.2s;
}

.section-item:hover {
    transform: translateY(-2px);
}

.actions-panel {
    backdrop-filter: blur(4px);
    padding: 0.5rem;
    border-radius: 0.25rem;
}
</style>