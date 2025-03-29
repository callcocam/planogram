<template>
    <div>
        <div class="mb-4 flex items-center">
            <div class="rounded-full bg-gray-100 p-2">
                <CheckIcon class="h-5 w-5" />
            </div>
            <h3 class="ml-2 text-lg font-medium">Revisão Final</h3>
        </div>

        <div class="space-y-6">
            <!-- Informações Básicas -->
            <div class="space-y-2">
                <h4 class="border-b pb-1 font-medium">Informações Básicas</h4>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <dl class="space-y-1">
                            <div class="flex">
                                <dt class="w-32 font-medium">Nome:</dt>
                                <dd>{{ formData.name }}</dd>
                            </div>
                            <div class="flex">
                                <dt class="w-32 font-medium">Localização:</dt>
                                <dd>{{ formData.location }}</dd>
                            </div>
                        </dl>
                    </div>
                    <div>
                        <dl class="space-y-1">
                            <div class="flex">
                                <dt class="w-32 font-medium">Status:</dt>
                                <dd>{{ formData.status === 'published' ? 'Publicado' : 'Rascunho' }}</dd>
                            </div>
                            <div class="flex">
                                <dt class="w-32 font-medium">Fator de Escala:</dt>
                                <dd>{{ formData.scale_factor }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Dimensões -->
            <div class="space-y-2">
                <h4 class="border-b pb-1 font-medium">Dimensões</h4>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <div class="mb-2 text-sm font-medium text-gray-700">Gôndola</div>
                        <dl class="space-y-1">
                            <div class="flex">
                                <dt class="w-32 font-medium">Altura:</dt>
                                <dd>{{ formData.height }}cm</dd>
                            </div>
                            <div class="flex">
                                <dt class="w-32 font-medium">Largura:</dt>
                                <dd>{{ formData.width }}cm</dd>
                            </div>
                            <div class="flex">
                                <dt class="w-32 font-medium">Espessura:</dt>
                                <dd>{{ formData.thickness }}cm</dd>
                            </div>
                            <div class="flex">
                                <dt class="w-32 font-medium">Altura da Base:</dt>
                                <dd>{{ formData.base_height }}cm</dd>
                            </div>
                        </dl>
                    </div>
                    <div>
                        <div class="mb-2 text-sm font-medium text-gray-700">Seção</div>
                        <dl class="space-y-1">
                            <div class="flex">
                                <dt class="w-32 font-medium">Largura:</dt>
                                <dd>{{ formData.section_width }}cm</dd>
                            </div>
                            <div class="flex">
                                <dt class="w-32 font-medium">Profundidade:</dt>
                                <dd>{{ formData.depth }}cm</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Prateleiras -->
            <div class="space-y-2">
                <h4 class="border-b pb-1 font-medium">Prateleiras e Módulos</h4>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <dl class="space-y-1">
                            <div class="flex">
                                <dt class="w-32 font-medium">Quantidade:</dt>
                                <dd>{{ formData.shelf_qty }}</dd>
                            </div>
                            <div class="flex">
                                <dt class="w-32 font-medium">Altura:</dt>
                                <dd>{{ formData.shelf_height }}cm</dd>
                            </div>
                            <div class="flex">
                                <dt class="w-32 font-medium">Espaçamento:</dt>
                                <dd>{{ calcularEspacamento() }}cm</dd>
                            </div>
                        </dl>
                    </div>
                    <div>
                        <dl class="space-y-1">
                            <div class="flex">
                                <dt class="w-32 font-medium">Nº de Módulos:</dt>
                                <dd>{{ formData.num_modulos }}</dd>
                            </div>
                            <div class="flex">
                                <dt class="w-32 font-medium">Tipo de Produto:</dt>
                                <dd>{{ formData.tipo_produto === 'normal' ? 'Normal' : 'Pendurável' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Detalhes dos Furos -->
            <div class="space-y-2">
                <h4 class="border-b pb-1 font-medium">Detalhes dos Furos</h4>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <dl class="space-y-1">
                            <div class="flex">
                                <dt class="w-48 font-medium">Espaçamento entre Furos:</dt>
                                <dd>{{ formData.hole_spacing }}cm</dd>
                            </div>
                        </dl>
                    </div>
                    <div>
                        <dl class="space-y-1">
                            <div class="flex">
                                <dt class="w-48 font-medium">Diâmetro dos Furos:</dt>
                                <dd>{{ formData.hole_diameter }}cm</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Visualização -->
            <div class="space-y-2">
                <h4 class="border-b pb-1 font-medium">Visualização</h4>
                <div class="mt-4 flex justify-center rounded-lg border bg-gray-50 p-4">
                    <div class="flex space-x-4">
                        <div v-for="modulo in parseInt(formData.num_modulos)" :key="modulo" class="w-16 border border-gray-300 bg-white">
                            <div
                                v-for="i in parseInt(formData.shelf_qty)"
                                :key="i"
                                class="mx-1 h-6 border-t border-gray-300"
                                :class="{ 'bg-gray-200': i === parseInt(formData.shelf_qty) }"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { CheckIcon } from 'lucide-vue-next';

const props = defineProps({
    formData: {
        type: Object,
        required: true,
    },
});

// Função para calcular o espaçamento entre prateleiras
const calcularEspacamento = () => {
    const alturaTotal = props.formData.height;
    const alturaUtil = alturaTotal - props.formData.base_height;
    const espacamento = props.formData.shelf_qty > 1 ? (alturaUtil / (props.formData.shelf_qty - 1)).toFixed(1) : 0;
    return espacamento;
};
</script>
