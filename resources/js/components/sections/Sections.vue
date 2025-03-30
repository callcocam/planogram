<template>
    <div class="flex w-full flex-col rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 md:flex-row">
        <draggable v-model="sortableSections" item-key="id" handle=".drag-handle" @end="onDragEnd" class="flex w-full flex-col md:flex-row">
            <template #item="{ element: section, index }">
                <div class="flex items-center">
                    <Gramalheira :section="section" :scale-factor="props.scaleFactor" @delete-section="deleteSection">
                        <template #actions>
                            <Button size="sm" class="drag-handle h-6 w-6 cursor-move p-0" variant="secondary">
                                <MoveIcon class="h-3 w-3" />
                            </Button>
                        </template>
                    </Gramalheira>
                    <Section :section="section" :scale-factor="props.scaleFactor" />
                    <Gramalheira :section="section" :scale-factor="props.scaleFactor" v-if="isLastSection(section)" :is-last-section="true" />
                </div>
            </template>
        </draggable>
    </div>
</template>

<script setup lang="ts">
import { MoveIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Gramalheira from './Gramalheira.vue';
import Section from './Section.vue';
// @ts-ignore
import { Button } from '@/components/ui/button';
// @ts-ignore
import draggable from 'vuedraggable';

const props = defineProps({
    gondola: {
        type: Object,
        required: true,
    },
    scaleFactor: {
        type: Number,
        required: true,
    },
});

const emit = defineEmits(['sections-reordered']);

const planogram = computed(() => {
    return props.gondola.planogram;
});

const sections = computed(() => { 
    return props.gondola.sections;
});

const lastSection = computed(() => {
    return sections.value[sections.value.length - 1];
});
const firstSection = computed(() => {
    return sections.value[0];
});
const isLastSection = (section) => {
    return section.id === lastSection.value.id;
};
const isFirstSection = (section) => {
    return section.id === firstSection.value.id;
};
const isFirstOrLastSection = (section) => {
    return isFirstSection(section) || isLastSection(section);
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
</script>
