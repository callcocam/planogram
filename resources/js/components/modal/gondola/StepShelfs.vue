<template>
    <div>
        <div class="mb-4 flex items-center">
            <div class="rounded-full bg-gray-100 p-2">
                <LayoutGridIcon class="h-5 w-5" />
            </div>
            <h3 class="ml-2 text-lg font-medium">Configuração de Prateleiras</h3>
        </div>

        <!-- Configuração das Prateleiras -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="space-y-2">
                <Label for="shelf_qty">Quantidade de Prateleiras</Label>
                <Input id="shelf_qty" type="number" v-model="formLocal.shelf_qty" min="1" @change="updateForm" />
            </div>

            <div class="space-y-2">
                <Label for="shelf_height">Altura da Prateleira (cm)</Label>
                <Input id="shelf_height" type="number" v-model="formLocal.shelf_height" min="1" @change="updateForm" />
            </div>
        </div>

        <!-- Configuração dos Furos -->
        <div class="mt-4 space-y-2">
            <h4 class="text-sm font-medium">Detalhes dos Furos</h4>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="space-y-2">
                    <Label for="hole_spacing">Espaçamento entre Furos (cm)</Label>
                    <Input id="hole_spacing" type="number" v-model="formLocal.hole_spacing" min="1" @change="updateForm" />
                </div>

                <div class="space-y-2">
                    <Label for="hole_diameter">Diâmetro dos Furos (cm)</Label>
                    <Input id="hole_diameter" type="number" v-model="formLocal.hole_diameter" min="1" @change="updateForm" />
                </div>
            </div>
        </div>

        <!-- Configuração dos Módulos -->
        <div class="mt-4 space-y-2">
            <h4 class="text-sm font-medium">Configuração dos Módulos</h4>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="space-y-2">
                    <Label for="num_modulos">Número de Módulos</Label>
                    <Input id="num_modulos" type="number" v-model="formLocal.num_modulos" min="1" @change="updateForm" />
                </div>

                <div class="space-y-2">
                    <Label>Tipo de Produto</Label>
                    <div class="grid grid-cols-2 gap-2">
                        <Button
                            :variant="formLocal.tipo_produto === 'normal' ? 'default' : 'outline'"
                            @click="setTipoProduto('normal')"
                            class="justify-center"
                        >
                            Normal
                        </Button>
                        <Button
                            :variant="formLocal.tipo_produto === 'penduravel' ? 'default' : 'outline'"
                            @click="setTipoProduto('penduravel')"
                            class="justify-center"
                        >
                            Pendurável
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5 rounded-lg border border-amber-200 bg-amber-50 p-4">
            <p class="text-sm text-amber-800">
                As prateleiras serão distribuídas com a primeira na base e a última no topo, com as intermediárias distribuídas uniformemente.
            </p>
        </div>

        <div class="mt-4 flex justify-end">
            <p class="text-sm text-gray-600">Espaçamento entre prateleiras: {{ calcularEspacamento() }}cm</p>
        </div>

        <!-- Visualização dos Módulos -->
        <div class="mt-5 flex justify-center rounded-lg border bg-gray-50 p-4">
            <div class="flex space-x-4">
                <div v-for="modulo in parseInt(formLocal.num_modulos)" :key="modulo" class="w-16 border border-gray-300 bg-white">
                    <div
                        v-for="i in parseInt(formLocal.shelf_qty)"
                        :key="i"
                        class="mx-1 h-6 border-t border-gray-300"
                        :class="{ 'bg-gray-200': i === parseInt(formLocal.shelf_qty) }"
                    ></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { LayoutGridIcon } from 'lucide-vue-next';
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

// Função para definir o tipo de produto e atualizar o formulário
const setTipoProduto = (tipo) => {
    formLocal.tipo_produto = tipo;
    updateForm();
};

// Função para calcular o espaçamento entre prateleiras
const calcularEspacamento = () => {
    const alturaTotal = formLocal.height; // Usar a altura da gôndola
    const alturaUtil = alturaTotal - formLocal.base_height;
    const espacamento = formLocal.shelf_qty > 1 ? (alturaUtil / (formLocal.shelf_qty - 1)).toFixed(1) : 0;
    return espacamento;
};

// Função para emitir os dados atualizados ao componente pai
const updateForm = () => {
    emit('update:form', { ...formLocal });
};
</script>
