<template>
    <div :style="gramalheiraStyle" class="group relative bg-slate-900">
        <!-- Botões que aparecem apenas no hover, posicionados acima da gramalheira em coluna -->
        <div
            v-if="!props.isLastSection"
            class="absolute -top-24 left-1/2 flex -translate-x-1/2 transform flex-col space-y-2 opacity-0 transition-opacity duration-200 group-hover:opacity-100"
        >
            <Button size="sm" class="h-6 w-6 p-0" variant="secondary" @click="$emit('edit-section', section)">
                <PencilIcon class="h-3 w-3" />
            </Button>
            <Button size="sm" class="h-6 w-6 p-0" variant="destructive" @click="openDeleteConfirm">
                <TrashIcon class="h-3 w-3" />
            </Button>
            <slot name="actions" />
        </div>
        <!-- Furos na gramalheira -->
        <div
            v-for="(hole, index) in holes"
            :key="index"
            class="absolute bg-slate-100"
            :style="{
                width: `${hole.width}px`,
                height: `${hole.height}px`,
                top: `${hole.position}px`,
                left: `${(gramalheiraWidth - hole.width) / 2}px`,
            }"
        ></div>

        <!-- Base section (without holes) at the bottom -->
        <div
            class="absolute bottom-0 left-0 w-full bg-slate-900"
            :style="{
                height: `${baseHeight * props.scaleFactor}px`,
            }"
        ></div>
    </div>

    <!-- Modal de confirmação -->
    <ConfirmModal
        :isOpen="showDeleteConfirm"
        @update:isOpen="showDeleteConfirm = $event"
        title="Excluir seção"
        message="Tem certeza que deseja excluir esta seção? Esta ação não pode ser desfeita."
        confirmButtonText="Excluir"
        cancelButtonText="Cancelar"
        :isDangerous="true"
        @confirm="confirmDelete"
        @cancel="cancelDelete"
    />
</template>
<script setup lang="ts">
import { PencilIcon, TrashIcon } from 'lucide-vue-next'; 
// @ts-ignore
import { Button } from '@/components/ui/button';
import { computed, ref } from 'vue';

const props = defineProps({
    section: {
        type: Object,
        required: true,
    },
    scaleFactor: {
        type: Number,
        required: true,
    },
    isLastSection: {
        type: Boolean,
        default: false,
    },
    isFirstSection: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['edit-section', 'delete-section']);

const gondola = ref<any>(props.section.gondola);
const gramalheiraWidth = computed(() => gondola.value.thickness * props.scaleFactor);
const baseHeight = computed(() => props.section.gondola.base_height || 17);

// Estado para controlar a visibilidade do modal de confirmação
const showDeleteConfirm = ref(false);

// Funções para gerenciar a confirmação de exclusão
function openDeleteConfirm(): void {
    showDeleteConfirm.value = true;
}

function confirmDelete(): void {
    // Emitir evento de exclusão apenas quando confirmado
    emit('delete-section', props.section);
}

function cancelDelete(): void {
    // Apenas fechar o modal
    showDeleteConfirm.value = false;
}

const holes = computed(() => {
    interface Hole {
        width: number;
        height: number;
        position: number;
    }

    const sectionHeight = props.section.height;
    const holeHeight = props.section.gondola.shelf_height;
    const holeWidth = props.section.gondola.hole_diameter;
    const holeSpacing = props.section.gondola.hole_spacing;

    // Calculate available height for holes (excluding the base at the bottom)
    const availableHeight = sectionHeight - baseHeight.value;

    // Calculate how many holes we can fit
    const totalSpaceNeeded = holeHeight + holeSpacing;
    const holeCount = Math.floor(availableHeight / totalSpaceNeeded);

    // Calculate the remaining space to distribute evenly
    const remainingSpace = availableHeight - holeCount * holeHeight - (holeCount - 1) * holeSpacing;
    const marginTop = remainingSpace / 2; // Start from the top with margin

    const holes: Hole[] = [];
    for (let i = 0; i < holeCount; i++) {
        holes.push({
            width: holeWidth * props.scaleFactor,
            height: holeHeight * props.scaleFactor,
            position: (marginTop + i * (holeHeight + holeSpacing)) * props.scaleFactor,
        });
    }

    return holes;
});

const gramalheiraStyle = computed(() => {
    return {
        width: `${gondola.value.thickness * props.scaleFactor}px`,
        height: `${props.section.height * props.scaleFactor}px`,
    };
});
</script>
