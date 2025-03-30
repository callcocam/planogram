<template>
    <div class="space-y-4">
        <!-- Informações Básicas -->
        <div class="mb-4 flex items-center">
            <div class="rounded-full bg-gray-100 p-2">
                <InfoIcon class="h-5 w-5" />
            </div>
            <h3 class="ml-2 text-lg font-medium">Informações Básicas</h3>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="space-y-2">
                <Label for="name">Nome da Gôndola *</Label>
                <Input id="name" v-model="formLocal.gondola_name" required @change="updateForm" />
            </div>

            <div class="space-y-2">
                <Label for="location">Localização</Label>
                <Input id="location" v-model="formLocal.location" placeholder="Ex: Setor de bebidas" @change="updateForm" />
                <p class="text-xs text-gray-500">Corredor onde a gôndola está localizada</p>
            </div>

            <div class="space-y-2">
                <Label for="side">Lado do corredor</Label>
                <Input id="side" v-model="formLocal.side" placeholder="Ex: A, B ou 1, 2" @change="updateForm" />
                <p class="text-xs text-gray-500">Identificação do lado do corredor</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="space-y-2">
                <Label for="scale_factor">Fator de Escala</Label>
                <Input id="scale_factor" type="number" v-model="formLocal.scale_factor" min="1" @change="updateForm" />
                <p class="text-xs text-gray-500">Fator para escalar o modelo visual da gôndola</p>
            </div>

            <div class="space-y-2 md:col-span-2">
                <Label>Posição Fluxo</Label>
                <div class="grid grid-cols-2 gap-2">
                    <Button
                        :variant="formLocal.flow === 'left_to_right' ? 'default' : 'outline'"
                        @click="setFlow('left_to_right')"
                        class="justify-center"
                    >
                        Esquerda para direita
                    </Button>
                    <Button
                        :variant="formLocal.flow === 'right_to_left' ? 'default' : 'outline'"
                        @click="setFlow('right_to_left')"
                        class="justify-center"
                    >
                        Direita para esquerda
                    </Button>
                </div>
                <p class="text-xs text-gray-500">Define o sentido de fluxo da gôndola</p>
            </div>
        </div>

        <div class="space-y-2">
            <Label for="status">Status</Label>
            <Select v-model="formLocal.status" @update:modelValue="updateForm">
                <SelectTrigger>
                    <SelectValue placeholder="Selecione o status" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        <SelectLabel>Status</SelectLabel>
                        <SelectItem value="published">Publicado</SelectItem>
                        <SelectItem value="draft">Rascunho</SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>
        </div>
    </div>
</template>

<script setup>
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select';
import { InfoIcon } from 'lucide-vue-next';
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

// Função para gerar um código aleatório formatado para o nome da gôndola
const gerarCodigoGondola = () => {
    const prefixo = 'GND';
    const data = new Date();
    const ano = data.getFullYear().toString().slice(2); // Últimos 2 dígitos do ano
    const mes = (data.getMonth() + 1).toString().padStart(2, '0');
    const random = Math.floor(Math.random() * 10000)
        .toString()
        .padStart(4, '0');

    return `${prefixo}-${ano}${mes}-${random}`;
};

// Inicializar o nome da gôndola se estiver vazio
onMounted(() => {
    if (!formLocal.gondola_name) {
        formLocal.gondola_name = gerarCodigoGondola();
        updateForm();
    }

    // Inicializar flow se ainda não estiver definido
    if (!formLocal.flow) {
        formLocal.flow = 'left_to_right';
    }
});

// Observar mudanças nas props e atualizar o formulário local
watch(
    () => props.formData,
    (newVal) => {
        Object.assign(formLocal, newVal);

        // Se o nome ainda estiver vazio após atualização, gere um novo código
        if (!formLocal.gondola_name) {
            formLocal.gondola_name = gerarCodigoGondola();
            updateForm();
        }
    },
    { deep: true },
);

// Definir o fluxo da gôndola
const setFlow = (flow) => {
    formLocal.flow = flow;
    updateForm();
};

// Função para emitir os dados atualizados ao componente pai
const updateForm = () => {
    emit('update:form', { ...formLocal });
};
</script>
