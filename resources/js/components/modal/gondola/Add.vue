<template>
    <Dialog :open="isOpen" @update:open="updateOpen">
        <DialogContent class="flex max-h-[90vh] w-full max-w-4xl flex-col p-0">
            <!-- Cabeçalho Fixo -->
            <div class="border-b p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <DialogTitle class="text-xl font-semibold">{{ passoTitulos[passoAtual] }}</DialogTitle>
                        <DialogDescription>{{ passoDescricoes[passoAtual] }}</DialogDescription>
                    </div>
                </div>

                <!-- Indicador de passos -->
                <div class="mb-2 mt-3 flex items-center">
                    <template v-for="(passo, index) in passoTitulos" :key="index">
                        <div
                            class="flex h-8 w-8 flex-none items-center justify-center rounded-full text-sm font-medium"
                            :class="{
                                'bg-black text-white': passoAtual > index,
                                'bg-black text-white': passoAtual === index,
                                'bg-gray-200 text-gray-700': passoAtual < index,
                            }"
                        >
                            <CheckIcon v-if="passoAtual > index" class="h-4 w-4" />
                            <span v-else>{{ index + 1 }}</span>
                        </div>
                        <div
                            v-if="index < passoTitulos.length - 1"
                            class="mx-2 h-1 flex-1"
                            :class="{ 'bg-black': passoAtual > index, 'bg-gray-300': passoAtual <= index }"
                        ></div>
                    </template>
                </div>
            </div>

            <!-- Área de Conteúdo com Rolagem -->
            <div class="flex-1 overflow-y-auto p-4">
                <!-- Componentes de cada passo -->
                <StepGondola v-if="passoAtual === 0" :form-data="formData" @update:form="updateForm" />

                <StepDimensions v-if="passoAtual === 1" :form-data="formData" @update:form="updateForm" />

                <StepShelfs v-if="passoAtual === 2" :form-data="formData" @update:form="updateForm" />

                <StepReview v-if="passoAtual === 3" :form-data="formData" />
            </div>

            <!-- Rodapé Fixo -->
            <div class="flex justify-between border-t bg-white p-4">
                <Button v-if="passoAtual > 0" variant="outline" @click="passoAtual--"> <ChevronLeftIcon class="mr-2 h-4 w-4" /> Anterior </Button>
                <div v-else>
                    <Button variant="outline" @click="fecharModal">Cancelar</Button>
                </div>

                <Button v-if="passoAtual < passoTitulos.length - 1" @click="passoAtual++"> Próximo <ChevronRightIcon class="ml-2 h-4 w-4" /> </Button>
                <Button v-else @click="enviarFormulario" :disabled="enviando">
                    <SaveIcon v-if="!enviando" class="mr-2 h-4 w-4" />
                    <Loader2Icon v-else class="mr-2 h-4 w-4 animate-spin" />
                    Salvar
                </Button>
            </div>
        </DialogContent>
    </Dialog>
</template>

<script setup>
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogTitle } from '@/components/ui/dialog';
import { useForm } from '@inertiajs/vue3';
import { CheckIcon, ChevronLeftIcon, ChevronRightIcon, Loader2Icon, SaveIcon } from 'lucide-vue-next';
import { reactive, ref, watch } from 'vue';

// Importação dos componentes de passos
import StepDimensions from './StepDimensions.vue';
import StepGondola from './StepGondola.vue';
import StepShelfs from './StepShelfs.vue';
import StepReview from './StepReview.vue';

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    planogramId: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(['close', 'gondola-added', 'update:open']);

const isOpen = ref(props.open);
const enviando = ref(false);
const passoAtual = ref(0);

// Títulos e descrições para cada passo
const passoTitulos = ['Informações Básicas', 'Dimensões', 'Prateleiras', 'Revisão'];

const passoDescricoes = [
    'Preencha as informações básicas da gôndola',
    'Configure as dimensões e especificações técnicas',
    'Configure as prateleiras e seções',
    'Revise todas as informações antes de salvar',
];

// Formulário unificado com todos os campos necessários
const formData = reactive({
    // Informações básicas (Passo 1)
    planogram_id: props.planogramId,
    name: '', // Será preenchido com código gerado automaticamente no componente StepGondola
    location: 'Centro de Distribuição',
    status: 'published',
    scale_factor: 3,

    // Dimensões (Passo 2)
    width: 130,
    height: 180,
    thickness: 4,
    base_height: 17,
    depth: 40,

    // Prateleiras e seções (Passo 3)
    shelf_height: 4,
    shelf_qty: 5,
    hole_spacing: 2,
    hole_diameter: 2,
    num_modulos: 4,
    tipo_produto: 'normal',
    position: 0,
    section_width: 130, // Largura específica da seção (pode ser diferente da gôndola)
});

// Formulário do Inertia.js para envio
const form = useForm({});

// Watch para sincronizar a propriedade open com o estado interno
watch(
    () => props.open,
    (newVal) => {
        isOpen.value = newVal;
    },
);

// Watch para sincronizar o planogramId
watch(
    () => props.planogramId,
    (newVal) => {
        formData.planogram_id = newVal;
    },
);

// Função para atualizar dados do formulário
const updateForm = (newData) => {
    Object.assign(formData, newData);
};

// Função para atualizar o estado do modal e emitir evento
const updateOpen = (value) => {
    isOpen.value = value;
    emit('update:open', value);

    if (!value) {
        emit('close');
        resetarFormulario();
    }
};

// Função para fechar o modal
const fecharModal = () => {
    updateOpen(false);
};

// Função para resetar o formulário
const resetarFormulario = () => {
    passoAtual.value = 0;

    // Resetar formulário
    Object.assign(formData, {
        planogram_id: props.planogramId,
        name: '',
        location: 'Centro de Distribuição',
        status: 'published',
        scale_factor: 3,
        width: 130,
        height: 180,
        thickness: 4,
        base_height: 17,
        depth: 40,
        shelf_height: 4,
        shelf_qty: 5,
        hole_spacing: 2,
        hole_diameter: 2,
        num_modulos: 4,
        tipo_produto: 'normal',
        position: 0,
        section_width: 130,
    });

    form.reset();
    form.clearErrors();
};

// Função para enviar o formulário
const enviarFormulario = () => {
    enviando.value = true;

    // Preparar os dados para envio em formato compatível com o backend
    const dadosEnvio = {
        // Dados da gôndola
        planogram_id: formData.planogram_id,
        name: formData.name,
        location: formData.location,
        height: formData.height,
        width: formData.width,
        thickness: formData.thickness,
        base_height: formData.base_height,
        hole_spacing: formData.hole_spacing,
        hole_diameter: formData.hole_diameter,
        shelf_height: formData.shelf_height,
        scale_factor: formData.scale_factor,
        status: formData.status,

        // Dados da seção
        section: {
            width: formData.section_width,
            depth: formData.depth,
            shelf_qty: formData.shelf_qty,
            tipo_produto: formData.tipo_produto,
            num_modulos: formData.num_modulos,
        },
    };

    // Atualizar o formulário do Inertia
    Object.assign(form, dadosEnvio);

    // Enviar os dados
    form.put(
        route('planogram.planogram.update', {
            planogram: props.planogramId,
        }),
        {
            preserveScroll: true,
            onSuccess: () => {
                emit('gondola-added');
                fecharModal();
                // Exibir mensagem de sucesso se necessário
            },
            onFinish: () => {
                enviando.value = false;
            },
        },
    );
};
</script>
