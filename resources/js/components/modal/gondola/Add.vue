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
                                'bg-black text-white': passoAtual >= index,
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

                <StepModule v-if="passoAtual === 1" :form-data="formData" @update:form="updateForm" />

                <StepBase v-if="passoAtual === 2" :form-data="formData" @update:form="updateForm" />

                <StepCremalheira v-if="passoAtual === 3" :form-data="formData" @update:form="updateForm" />

                <StepShelves v-if="passoAtual === 4" :form-data="formData" @update:form="updateForm" />

                <StepReview v-if="passoAtual === 5" :form-data="formData" />
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

<script setup lang="ts">
// @ts-ignore
import { Button } from '@/components/ui/button';
// @ts-ignore
import { Dialog, DialogContent, DialogDescription, DialogTitle } from '@/components/ui/dialog';
import { useForm } from '@inertiajs/vue3';
import { CheckIcon, ChevronLeftIcon, ChevronRightIcon, Loader2Icon, SaveIcon } from 'lucide-vue-next';
import { reactive, ref, watch } from 'vue';

// Importação dos componentes de passos
import StepBase from './StepBase.vue';
import StepCremalheira from './StepCremalheira.vue';
import StepGondola from './StepGondola.vue';
import StepModule from './StepModule.vue';
import StepReview from './StepReview.vue';
import StepShelves from './StepShelves.vue';

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    planogram: {
        type: Object,
        required: true,
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
const passoTitulos = ['Informações Básicas', 'Módulos', 'Base', 'Cremalheira', 'Prateleiras', 'Revisão'];

const passoDescricoes = [
    'Preencha as informações básicas da gôndola',
    'Configure os módulos da gôndola',
    'Configure as dimensões da base',
    'Configure a cremalheira e os furos',
    'Configure as prateleiras e gancheiras',
    'Revise todas as informações antes de salvar',
];

// Formulário unificado com todos os campos necessários
const formData = reactive({
    // Informações básicas (Passo 1)
    planogram_id: props.planogramId,
    gondola_name: '', // Será preenchido com código gerado automaticamente
    location: 'Centro',
    side: 'A',
    flow: 'left_to_right',
    scale_factor: 3,
    status: 'published',

    // Módulos (Passo 2)
    num_modulos: 4,
    width: 130,
    height: 180,
    section_code: '',

    // Base (Passo 3)
    base_height: 17,
    base_width: 130,
    base_depth: 40,

    // Cremalheira (Passo 4)
    cremalheira_width: 4,
    hole_height: 3,
    hole_width: 2,
    hole_spacing: 2,

    // Prateleiras (Passo 5)
    shelf_width: 4,
    shelf_height: 4,
    shelf_depth: 40,
    num_shelves: 4,
    product_type: 'normal',
});

// Formulário do Inertia.js para envio
let form = useForm({});

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
        side: '',
        flow: 'left_to_right',
        scale_factor: 3,
        status: 'published',

        num_modulos: 4,
        num_shelves: 4,
        width: 130,
        height: 180, 

        base_height: 17,
        base_width: 130,
        base_depth: 40,

        cremalheira_width: 4,
        hole_height: 3,
        hole_width: 2,
        hole_spacing: 2,
 
        shelf_width: 4,
        shelf_height: 4,
        shelf_depth: 40, 
        product_type: 'normal',
    });

    form.reset();
    form.clearErrors();
};

// Função para enviar o formulário
const enviarFormulario = () => {
    enviando.value = true;

    // Preparar os dados para envio em formato compatível com o backend
    const dadosEnvio = {
        // Dados do planograma
        planogram_id: formData.planogram_id,

        // Dados da gôndola (tabela gondolas)
        gondola_name: formData.gondola_name,
        location: formData.location,
        side: formData.side,
        flow: formData.flow,
        scale_factor: formData.scale_factor,
        status: formData.status,

        // Dados da seção (tabela sections)
        section: {
            name: formData.gondola_name + ' - Seção', 
            width: formData.width,
            height: formData.height, 
            base_height: formData.base_height,
            base_depth: formData.base_depth,
            base_width: formData.base_width,
            cremalheira_width: formData.cremalheira_width,
            hole_height: formData.hole_height,
            hole_width: formData.hole_width,
            hole_spacing: formData.hole_spacing,
            shelf_width: formData.shelf_width,
            shelf_height: formData.shelf_height,
            shelf_depth: formData.shelf_depth,

            // Dados adicionais para criação das prateleiras
            num_shelves: formData.num_shelves,
            num_modulos: formData.num_modulos,
            product_type: formData.product_type,
            settings: {
                product_type: formData.product_type,
            },
        },
    };

    console.log('Dados a serem enviados:', dadosEnvio);

    // Recrie o formulário com os dados
    form = useForm(dadosEnvio);

    // Enviar os dados
    // @ts-ignore
    // console.log('holes:', holes(formData, formData.scale_factor));
    form.put(route('planogram.planogram.update', {
            planogram: props.planogramId,
        }),
        {
            preserveScroll: true,
            onSuccess: (page) => {
                emit('gondola-added');
                fecharModal(); 
            },
            onError: (errors) => {
                console.error('Erros:', errors);
            },
            onFinish: () => {
                enviando.value = false;
            },
        },
    );
};
 
</script>
