<template>
    <div class="space-y-4">
        <div class="mb-4 flex items-center">
            <div class="rounded-full bg-gray-100 p-2">
                <BoxIcon class="h-5 w-5" />
            </div>
            <h3 class="ml-2 text-lg font-medium">Configurar Base</h3>
        </div>

        <!-- Dimensões da Base -->
        <div class="space-y-2">
            <h4 class="text-sm font-medium">Dimensões da Base</h4>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="space-y-2">
                    <Label for="base_height">Altura da Base (cm)</Label>
                    <Input id="base_height" type="number" v-model="formLocal.base_height" min="1" @change="updateForm" />
                    <p class="text-xs text-gray-500">Altura da base da gôndola</p>
                </div>

                <div class="space-y-2">
                    <Label for="base_width">Largura da Base (cm)</Label>
                    <Input id="base_width" type="number" v-model="formLocal.base_width" min="1" @change="updateForm" />
                    <p class="text-xs text-gray-500">Largura da base da gôndola</p>
                </div>

                <div class="space-y-2">
                    <Label for="base_depth">Profundidade da Base (cm)</Label>
                    <Input id="base_depth" type="number" v-model="formLocal.base_depth" min="1" @change="updateForm" />
                    <p class="text-xs text-gray-500">Profundidade da base da gôndola</p>
                </div>
            </div>
        </div>

        <!-- Visualização da Base -->
        <div class="mt-5 rounded-lg border bg-gray-50 p-4">
            <div class="flex justify-center">
                <div class="relative">
                    <!-- Base da gôndola (representação visual) -->
                    <div
                        class="border border-gray-400 bg-gray-300"
                        :style="{
                            width: `${formLocal.base_width / 3}px`,
                            height: `${formLocal.base_height / 3}px`,
                            maxWidth: '300px',
                        }"
                    ></div>
                    <!-- Indicador de profundidade -->
                    <div class="absolute right-0 top-1/2 flex -translate-y-1/2 translate-x-full transform items-center">
                        <div class="h-px w-6 bg-gray-400"></div>
                        <span class="ml-1 text-xs">{{ formLocal.base_depth }} cm</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-blue-100 bg-blue-50 p-4">
            <p class="text-sm text-blue-800">
                <span class="font-medium">Dica:</span> A base é a parte inferior da gôndola que sustenta toda a estrutura. Geralmente possui altura
                menor que as demais partes.
            </p>
        </div>
    </div>
</template>

<script setup>
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { BoxIcon } from 'lucide-vue-next';
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

// Inicializar valores padrão para a base se não existirem
onMounted(() => {
    if (!formLocal.base_height) {
        formLocal.base_height = 17; // Valor padrão conforme migration
    }

    if (!formLocal.base_width) {
        formLocal.base_width = formLocal.width || 130; // Usar a largura da gôndola ou valor padrão
    }

    if (!formLocal.base_depth) {
        formLocal.base_depth = 40; // Valor padrão conforme migration
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
