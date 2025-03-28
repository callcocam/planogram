<template>
    <Dialog :open="open" @update:open="updateOpen" class="max-w-4xl">
        <DialogContent class="flex max-h-[90vh] w-full max-w-4xl flex-col p-0">
            <!-- Fixed Header -->
            <div class="border-b p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <DialogTitle class="text-xl font-semibold">Adicionar Seção à Gôndola</DialogTitle>
                        <DialogDescription> Configure os detalhes da nova seção para o planograma </DialogDescription>
                    </div>
                </div>

                <!-- Seleção de modo (Assistente ou Em branco) -->
                <div class="mt-3 grid grid-cols-2 gap-1 rounded-lg bg-gray-100 p-1">
                    <button
                        class="rounded-md px-4 py-2 text-center text-sm font-medium transition-colors"
                        :class="{ 'bg-white shadow': mode === 'wizard', 'hover:bg-white/50': mode !== 'wizard' }"
                        @click="mode = 'wizard'"
                    >
                        Assistente
                    </button>
                    <button
                        class="rounded-md px-4 py-2 text-center text-sm font-medium transition-colors"
                        :class="{ 'bg-white shadow': mode === 'blank', 'hover:bg-white/50': mode !== 'blank' }"
                        @click="mode = 'blank'"
                    >
                        Em branco
                    </button>
                </div>

                <!-- Passos do assistente -->
                <div class="mb-2 mt-3 flex items-center">
                    <template v-for="(step, index) in steps" :key="index">
                        <div
                            class="flex h-8 w-8 flex-none items-center justify-center rounded-full text-sm font-medium"
                            :class="{
                                'bg-black text-white': currentStep > index,
                                'bg-black text-white': currentStep === index,
                                'bg-gray-200 text-gray-700': currentStep < index,
                            }"
                        >
                            <CheckIcon v-if="currentStep > index" class="h-4 w-4" />
                            <span v-else>{{ index + 1 }}</span>
                        </div>
                        <div
                            v-if="index < steps.length - 1"
                            class="mx-2 h-1 flex-1"
                            :class="{ 'bg-black': currentStep > index, 'bg-gray-300': currentStep <= index }"
                        ></div>
                    </template>
                </div>
            </div>

            <!-- Scrollable Content Area -->
            <div class="flex-1 overflow-y-auto p-4">
                <!-- Conteúdo dos passos -->
                <div class="space-y-4">
                    <!-- Passo 1: Informações da Seção -->
                    <div v-if="currentStep === 0">
                        <div class="mb-4 flex items-center">
                            <div class="rounded-full bg-gray-100 p-2">
                                <LayoutGridIcon class="h-5 w-5" />
                            </div>
                            <h3 class="ml-2 text-lg font-medium">Informações da Seção</h3>
                        </div>

                        <div class="space-y-4">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div class="space-y-2">
                                    <Label for="largura">Largura (cm)</Label>
                                    <Input id="largura" type="number" v-model="form.largura" min="1" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="profundidade">Profundidade (cm)</Label>
                                    <Input id="profundidade" type="number" v-model="form.profundidade" min="1" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="espessura">Espessura (cm)</Label>
                                    <Input id="espessura" type="number" v-model="form.espessura" min="1" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Passo 2: Configuração de Prateleiras -->
                    <div v-if="currentStep === 1">
                        <div class="mb-4 flex items-center">
                            <div class="rounded-full bg-gray-100 p-2">
                                <LayoutGridIcon class="h-5 w-5" />
                            </div>
                            <h3 class="ml-2 text-lg font-medium">Configuração de Prateleiras</h3>
                        </div>

                        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                            <div class="space-y-2">
                                <Label for="num_prateleiras">Nº de Prateleiras</Label>
                                <Input id="num_prateleiras" type="number" v-model="form.num_prateleiras" min="1" />
                            </div>

                            <div class="space-y-2">
                                <Label for="espacamento_furos">Esp. entre furos</Label>
                                <Input id="espacamento_furos" type="number" v-model="form.espacamento_furos" min="1" />
                            </div>

                            <div class="space-y-2">
                                <Label for="altura_base">Altura da Base</Label>
                                <Input id="altura_base" type="number" v-model="form.altura_base" min="1" />
                            </div>

                            <div class="space-y-2">
                                <Label for="num_modulos">Nº de Módulos</Label>
                                <Input id="num_modulos" type="number" v-model="form.num_modulos" min="1" />
                            </div>

                            <!-- Nova linha para espessura da coluna -->
                            <div class="space-y-2 md:col-span-4">
                                <Label for="espessura_coluna">Espessura da coluna</Label>
                                <Input id="espessura_coluna" type="number" v-model="form.espessura_coluna" min="1" />
                            </div>
                        </div>

                        <div class="mt-5">
                            <Label>Tipo de Produto</Label>
                            <div class="mt-2 grid grid-cols-2 gap-2">
                                <Button
                                    :variant="form.tipo_produto === 'normal' ? 'default' : 'outline'"
                                    @click="form.tipo_produto = 'normal'"
                                    class="justify-center"
                                >
                                    Normal
                                </Button>
                                <Button
                                    :variant="form.tipo_produto === 'penduravel' ? 'default' : 'outline'"
                                    @click="form.tipo_produto = 'penduravel'"
                                    class="justify-center"
                                >
                                    Pendurável
                                </Button>
                            </div>
                        </div>

                        <div class="mt-5 rounded-lg border border-amber-200 bg-amber-50 p-4">
                            <p class="text-sm text-amber-800">
                                As prateleiras serão distribuídas com a primeira na base e a última no topo, com as intermediárias distribuídas
                                uniformemente.
                            </p>
                        </div>

                        <div class="mt-4 flex justify-end">
                            <p class="text-sm text-gray-600">Espaçamento entre prateleiras: {{ calcularEspacamento() }}cm</p>
                        </div>

                        <!-- Visualização -->
                        <div class="mt-5 flex justify-center rounded-lg border bg-gray-50 p-4">
                            <div class="flex space-x-4">
                                <div v-for="modulo in parseInt(form.num_modulos)" :key="modulo" class="w-16 border border-gray-300 bg-white">
                                    <div
                                        v-for="i in parseInt(form.num_prateleiras)"
                                        :key="i"
                                        class="mx-1 h-6 border-t border-gray-300"
                                        :class="{ 'bg-gray-200': i === parseInt(form.num_prateleiras) }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Passo 3: Revisão -->
                    <div v-if="currentStep === 2">
                        <div class="mb-4 flex items-center">
                            <div class="rounded-full bg-gray-100 p-2">
                                <CheckIcon class="h-5 w-5" />
                            </div>
                            <h3 class="ml-2 text-lg font-medium">Revisão</h3>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <h4 class="mb-2 font-medium">Informações Básicas</h4>
                                <dl class="space-y-1">
                                    <div class="flex">
                                        <dt class="w-32 font-medium">Nome:</dt>
                                        <dd>Módulo 1</dd>
                                    </div>
                                    <div class="flex">
                                        <dt class="w-32 font-medium">Largura:</dt>
                                        <dd>{{ form.largura }}cm</dd>
                                    </div>
                                    <div class="flex">
                                        <dt class="w-32 font-medium">Altura:</dt>
                                        <dd>170cm</dd>
                                    </div>
                                    <div class="flex">
                                        <dt class="w-32 font-medium">Profundidade:</dt>
                                        <dd>{{ form.profundidade }}cm</dd>
                                    </div>
                                    <div class="flex">
                                        <dt class="w-32 font-medium">Altura da Base:</dt>
                                        <dd>{{ form.altura_base }}cm</dd>
                                    </div>
                                </dl>
                            </div>

                            <div>
                                <h4 class="mb-2 font-medium">Configuração de Prateleiras</h4>
                                <dl class="space-y-1">
                                    <div class="flex">
                                        <dt class="w-32 font-medium">Quantidade:</dt>
                                        <dd>{{ form.num_prateleiras }}</dd>
                                    </div>
                                    <div class="flex">
                                        <dt class="w-32 font-medium">Distribuição:</dt>
                                        <dd>Base ao Topo</dd>
                                    </div>
                                    <div class="flex">
                                        <dt class="w-32 font-medium">Tipo:</dt>
                                        <dd>{{ form.tipo_produto === 'normal' ? 'Normal' : 'Pendurável' }}</dd>
                                    </div>
                                    <div class="flex">
                                        <dt class="w-32 font-medium">Espaçamento médio:</dt>
                                        <dd>{{ calcularEspacamento() }}cm</dd>
                                    </div>
                                    <div class="flex">
                                        <dt class="w-32 font-medium">Nº de módulos:</dt>
                                        <dd>{{ form.num_modulos }}</dd>
                                    </div>
                                    <div class="flex">
                                        <dt class="w-32 font-medium">Espaçamento entre furos:</dt>
                                        <dd>{{ form.espacamento_furos }}cm</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Visualização dos módulos -->
                        <div class="mt-5 flex justify-center rounded-lg border bg-gray-50 p-4">
                            <div class="flex space-x-4">
                                <div v-for="modulo in parseInt(form.num_modulos)" :key="modulo" class="w-16 border border-gray-300 bg-white">
                                    <div
                                        v-for="i in parseInt(form.num_prateleiras)"
                                        :key="i"
                                        class="mx-1 h-6 border-t border-gray-300"
                                        :class="{ 'bg-gray-200': i === parseInt(form.num_prateleiras) }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fixed Footer -->
            <div class="flex justify-between border-t bg-white p-4">
                <Button v-if="currentStep > 0" variant="outline" @click="currentStep--"> <ChevronLeftIcon class="mr-2 h-4 w-4" /> Anterior </Button>
                <div v-else>
                    <Button variant="outline" @click="closeModal">Cancelar</Button>
                </div>

                <Button v-if="currentStep < steps.length - 1" @click="currentStep++"> Próximo <ChevronRightIcon class="ml-2 h-4 w-4" /> </Button>
                <Button v-else @click="salvarSecao" :disabled="isSubmitting">
                    <SaveIcon v-if="!isSubmitting" class="mr-2 h-4 w-4" />
                    <Loader2Icon v-else class="mr-2 h-4 w-4 animate-spin" />
                    Salvar Seção
                </Button>
            </div>
        </DialogContent>
    </Dialog>
</template>

<script setup>
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useForm } from '@inertiajs/vue3';
import { CheckIcon, ChevronLeftIcon, ChevronRightIcon, LayoutGridIcon, Loader2Icon, SaveIcon } from 'lucide-vue-next';
import { reactive, ref, watch } from 'vue';

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
    gondolaId: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(['close', 'section-added', 'update:open']);

const mode = ref('wizard'); // 'wizard' ou 'blank'
const currentStep = ref(0);
const isSubmitting = ref(false);

const steps = [{ title: 'Informações da Seção' }, { title: 'Configuração de Prateleiras' }, { title: 'Revisão' }];

// Formulário com valores padrão
// const form = reactive({
//     gondola_id: props.gondolaId,
//     largura: 130,
//     profundidade: 40,
//     espessura: 4,
//     num_prateleiras: 5,
//     espacamento_furos: 2,
//     altura_base: 17,
//     num_modulos: 4,
//     espessura_coluna: 4,
//     tipo_produto: 'normal',
// });

const form = useForm({
    gondola_id: props.gondolaId,
    altura: 170,
    largura: 130,
    profundidade: 40,
    espessura: 4,
    num_prateleiras: 5,
    espacamento_furos: 2,
    altura_base: 17,
    num_modulos: 4,
    espessura_coluna: 4,
    tipo_produto: 'normal',
});

// Atualiza a gondola_id quando a prop mudar
watch(
    () => props.gondolaId,
    (newVal) => {
        form.gondola_id = newVal;
    },
);

// Função para calcular o espaçamento entre prateleiras
const calcularEspacamento = () => {
    // Assumindo que a altura total é 170cm (como mostrado na imagem)
    const alturaTotal = 170;
    const alturaUtil = alturaTotal - form.altura_base;
    const espacamento = form.num_prateleiras > 1 ? (alturaUtil / (form.num_prateleiras - 1)).toFixed(1) : 0;
    return espacamento;
};

// Função para atualizar o estado de abertura do modal
const updateOpen = (value) => {
    emit('update:open', value);
    if (!value) {
        emit('close');
        resetForm();
    }
};

// Função para fechar o modal
const closeModal = () => {
    emit('update:open', false);
    emit('close');
    resetForm();
};

// Função para resetar o formulário
const resetForm = () => {
    Object.assign(form, {
        gondola_id: props.gondolaId,
        largura: 130,
        profundidade: 40,
        espessura: 4,
        num_prateleiras: 5,
        espacamento_furos: 2,
        altura_base: 17,
        num_modulos: 4,
        espessura_coluna: 4,
        tipo_produto: 'normal',
    });
    currentStep.value = 0;
    mode.value = 'wizard';
    isSubmitting.value = false;
};

// Função para salvar a seção
const salvarSecao = () => {
    isSubmitting.value = true;

    // Simulação para exemplo
    setTimeout(() => {
        emit('section-added');
        closeModal();
    }, 1000);

    // Exemplo de implementação real com Axios:
    /*
    axios.post('/api/gondola-sections', form)
      .then(() => {
        emit('section-added');
        closeModal();
      })
      .catch((error) => {
        console.error('Erro ao adicionar seção:', error);
      })
      .finally(() => {
        isSubmitting.value = false;
      });
    */
};
</script>
