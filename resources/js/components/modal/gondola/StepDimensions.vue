<template>
    <div class="space-y-4">
        <div class="mb-4 flex items-center">
            <div class="rounded-full bg-gray-100 p-2">
                <RulerIcon class="h-5 w-5" />
            </div>
            <h3 class="ml-2 text-lg font-medium">Configurar prateleiras e Gancheiras:</h3>
        </div>

        <!-- Dimensões Principais da Gôndola -->
        <div class="space-y-2">
            <h4 class="text-sm font-medium">Dimensões e Especificações:</h4>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="space-y-2">
                    <Label for="height">Altura (cm)</Label>
                    <Input id="height" type="number" v-model="formLocal.height" min="1" @change="updateForm" />
                </div>

                <div class="space-y-2">
                    <Label for="width">Largura (cm)</Label>
                    <Input id="width" type="number" v-model="formLocal.width" min="1" @change="updateForm" />
                </div>

                <div class="space-y-2">
                    <Label for="thickness">Espessura (cm)</Label>
                    <Input id="thickness" type="number" v-model="formLocal.thickness" min="1" @change="updateForm" />
                </div>
            </div>
        </div> 
        <div class="rounded-lg border border-blue-100 bg-blue-50 p-4">
            <p class="text-sm text-blue-800">
                <span class="font-medium">Dica:</span> As dimensões da gôndola e da seção podem ser diferentes. Certifique-se de que as medidas são
                compatíveis para um correto encaixe no planograma.
            </p>
        </div>
    </div>
</template>

<script setup>
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RulerIcon } from 'lucide-vue-next';
import { reactive, watch } from 'vue';

const props = defineProps({
    formData: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['update:form']);

// Cópia local do formulário para manipulação
const formLocal = reactive({ ...props.formData });

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
