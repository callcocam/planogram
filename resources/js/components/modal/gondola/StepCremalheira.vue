<template>
    <div class="space-y-4">
        <div class="mb-4 flex items-center">
            <div class="rounded-full bg-gray-100 p-2">
                <GripVerticalIcon class="h-5 w-5" />
            </div>
            <h3 class="ml-2 text-lg font-medium">Configurar Cremalheira</h3>
        </div>

        <!-- Dimensões da Cremalheira -->
        <div class="space-y-2">
            <h4 class="text-sm font-medium">Dimensões da Cremalheira</h4>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="space-y-2">
                    <Label for="cremalheira_width">Largura da Cremalheira (cm)</Label>
                    <Input id="cremalheira_width" type="number" v-model="formLocal.cremalheira_width" min="1" @change="updateForm" />
                    <p class="text-xs text-gray-500">Largura da coluna vertical (cremalheira)</p>
                </div>
            </div>
        </div>

        <!-- Configuração dos Furos -->
        <div class="space-y-2">
            <h4 class="text-sm font-medium">Configuração dos Furos</h4>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="space-y-2">
                    <Label for="hole_height">Altura do Furo (cm)</Label>
                    <Input id="hole_height" type="number" v-model="formLocal.hole_height" min="1" @change="updateForm" />
                </div>

                <div class="space-y-2">
                    <Label for="hole_width">Largura do Furo (cm)</Label>
                    <Input id="hole_width" type="number" v-model="formLocal.hole_width" min="1" @change="updateForm" />
                </div>

                <div class="space-y-2">
                    <Label for="hole_spacing">Espaçamento entre Furos (cm)</Label>
                    <Input id="hole_spacing" type="number" v-model="formLocal.hole_spacing" min="1" @change="updateForm" />
                    <p class="text-xs text-gray-500">Distância vertical entre furos</p>
                </div>
            </div>
        </div>

        <!-- Visualização da Cremalheira -->
        <div class="mt-5 rounded-lg border bg-gray-50 p-4">
            <div class="flex justify-center">
                <div class="relative">
                    <!-- Cremalheira (representação visual) -->
                    <div
                        class="relative bg-gray-400"
                        :style="{
                            width: `${formLocal.cremalheira_width * 2}px`,
                            height: '200px',
                        }"
                    >
                        <!-- Furos representados como círculos -->
                        <div
                            v-for="i in Math.floor(200 / (formLocal.hole_height * 2 + formLocal.hole_spacing * 2))"
                            :key="i"
                            class="absolute left-1/2 -translate-x-1/2 transform rounded-full border border-gray-300 bg-white"
                            :style="{
                                width: `${formLocal.hole_width * 2}px`,
                                height: `${formLocal.hole_height * 2}px`,
                                top: `${i * (formLocal.hole_height * 2 + formLocal.hole_spacing * 2)}px`,
                            }"
                        ></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-blue-100 bg-blue-50 p-4">
            <p class="text-sm text-blue-800">
                <span class="font-medium">Dica:</span> A cremalheira é a estrutura vertical com furos onde as prateleiras são encaixadas. O
                espaçamento entre os furos determina as posições possíveis para as prateleiras.
            </p>
        </div>
    </div>
</template>

<script setup>
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { GripVerticalIcon } from 'lucide-vue-next';
import { onMounted, reactive, watch } from 'vue';

const props = defineProps({
    formData: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['update:form']);

// Cópia local do formulário para manipulação
const formLocal = reactive({ ...props.formData });

// Inicializar valores padrão para a cremalheira se não existirem
onMounted(() => {
    if (!formLocal.cremalheira_width) {
        formLocal.cremalheira_width = 4; // Valor padrão conforme migration
    }

    if (!formLocal.hole_height) {
        formLocal.hole_height = 2; // Valor padrão conforme migration
    }

    if (!formLocal.hole_width) {
        formLocal.hole_width = 2; // Valor padrão conforme migration
    }

    if (!formLocal.hole_spacing) {
        formLocal.hole_spacing = 2; // Valor padrão conforme migration
    }

    updateForm();
});

// Observar mudanças nas props e atualizar o formulário local
watch(
    () => props.formData,
    (newVal) => {
        Object.assign(formLocal, newVal);
    },
    { deep: true },
);

// Função para emitir os dados atualizados ao componente pai
const updateForm = () => {
    emit('update:form', { ...formLocal });
};
</script>
