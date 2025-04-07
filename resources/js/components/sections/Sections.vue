<template>
    <div class="flex flex-col bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 md:flex-row">
        <draggable v-model="sortableSections" item-key="id" handle=".drag-handle" @end="onDragEnd" class="mt-28 flex px-10 md:flex-row">
            <template #item="{ element: section, index }">
                <div class="flex items-center">
                    <Gramalheira :section="section" :scale-factor="props.scaleFactor" @delete-section="deleteSection">
                        <template #actions>
                            <Button
                                size="sm"
                                class="drag-handle h-6 w-6 cursor-move p-0 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                                variant="secondary"
                            >
                                <MoveIcon class="h-3 w-3" />
                            </Button>
                        </template>
                    </Gramalheira>
                    <Section
                        :section="section"
                        :scale-factor="props.scaleFactor"
                        :selected-category="selectedCategory"
                        @move-shelf-to-section="handleMoveShelfToSection"
                        @segment-select="$emit('segment-select', $event)"
                        @update-shelves="handleMoveSegmentToSection"
                        @update:quantity="updateSegmentQuantity"
                        @update:segments="handleMoveSegmentToSection"
                    />
                    <Gramalheira :section="section" :scale-factor="props.scaleFactor" v-if="isLastSection(section)" :is-last-section="true" />
                </div>
            </template>
        </draggable>
    </div>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { MoveIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import Gramalheira from './Cremalheira.vue';
import Section from './Section.vue';
// @ts-ignore
import { Button } from '@/components/ui/button';
// @ts-ignore
import { round } from 'lodash';
import draggable from 'vuedraggable';
interface Category {
    id: string | number;
    name: string;
}

const props = defineProps({
    gondola: {
        type: Object,
        required: true,
    },
    scaleFactor: {
        type: Number,
        required: true,
    },
    selectedCategory: {
        type: Object as () => Category | null,
        default: null,
    },
});

const emit = defineEmits(['sections-reordered', 'shelves-updated', 'move-shelf-to-section', 'segment-select']);

const sections = ref(props.gondola.sections || []);

const lastSection = computed(() => {
    return sections.value[sections.value.length - 1];
});
const isLastSection = (section) => {
    return section.id === lastSection.value.id;
};
// Create a ref for sections so we can modify it with draggable
const sortableSections = ref(sections.value);

// When drag ends, emit event with the new order
const onDragEnd = () => {
    emit('sections-reordered', sortableSections.value, props.gondola.id);
};

const deleteSection = (section: any) => {
    // @ts-ignore
    router.delete(route('planogram.sections.destroy', section.id), {
        preserveState: false,
        preserveScroll: true,
        onSuccess: () => {
            // Remove the section from the local state
            sortableSections.value = sortableSections.value.filter((s: any) => s.id !== section.id);
        },
        onError: () => {
            // Handle error if needed
        },
        onFinish: () => {
            // Reset the state if needed
        },
    });
    // emit('delete-section', section);
};

const handleMoveShelfToSection = (shelf: any, sectionId: number) => {
    router.put(
        // @ts-ignore
        route('planogram.shelves.update-section', shelf.id),
        {
            section_id: sectionId,
            new_position: round(shelf.shelf_position),
        },
        {
            preserveState: false,
            preserveScroll: true,
            onSuccess: () => {
                // Handle success if needed
            },
            onError: () => {
                // Handle error if needed
            },
            onFinish: () => {
                // Reset the state if needed
            },
        },
    );
};

const handleMoveSegmentToSection = (segment: any, sectionId: number) => {
    router.put(
        // @ts-ignore
        route('planogram.segments.reorder', segment.shelfId),
        segment,
        {
            preserveState: false,
            preserveScroll: true,
            onSuccess: () => {
                // Handle success if needed
            },
            onError: () => {
                // Handle error if needed
            },
            onFinish: () => {
                // Reset the state if needed
            },
        },
    );
};

const updateSegmentQuantity = (segment: any) => {
    router.put(
        // @ts-ignore
        route('planogram.segments.update', segment.segmentId),
        segment.data,
        {
            preserveState: false,
            preserveScroll: true,
            onSuccess: () => {
                // Handle success if
            },
            onError: () => {
                // Handle error if needed
            },
            onFinish: () => {
                // Reset the state if needed
            },
        },
    );
};
</script>

<style scoped>
/* Estilos para o modo escuro específicos do componente Sections, se necessário */
@media (prefers-color-scheme: dark) {
    .drag-handle {
        /* Ajustes adicionais para o ícone de arrastar no modo escuro, se necessário */
        filter: brightness(1.1);
    }
}
</style>
