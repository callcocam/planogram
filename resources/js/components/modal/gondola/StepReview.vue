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
                            <div class="flex">
                                <dt class="w-32 font-medium">Lado:</dt>
                                <dd>{{ formData.side || 'Não especificado' }}</dd>
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
                                <dt class="w-32 font-medium">Fluxo:</dt>
                                <dd>{{ formData.flow === 'left_to_right' ? 'Esquerda para direita' : 'Direita para esquerda' }}</dd>
                            </div>
                            <div class="flex">
                                <dt class="w-32 font-medium">Fator de Escala:</dt>
                                <dd>{{ formData.scale_factor }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Módulos -->
            <div class="space-y-2">
                <h4 class="border-b pb-1 font-medium">Módulos</h4>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <dl class="space-y-1">
                            <div class="flex">
                                <dt class="w-32 font-medium">Nº de Módulos:</dt>
                                <dd>{{ formData.num_modulos }}</dd>
                            </div>
                            <div class="flex">
                                <dt class="w-32 font-medium">Largura:</dt>
                                <dd>{{ formData.section_width }}cm</dd>
                            </div>
                        </dl>
                    </div>
                    <div>
                        <dl class="space-y-1">
                            <div class="flex">
                                <dt class="w-32 font-medium">Código da Seção:</dt>
                                <dd>{{ formData.section_code || 'Automático' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Base -->
            <div class="space-y-2">
                <h4 class="border-b pb-1 font-medium">Base</h4>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <dl class="space-y-1">
                            <div class="flex">
                                <dt class="w-32 font-medium">Altura:</dt>
                                <dd>{{ formData.base_height }}cm</dd>
                            </div>
                        </dl>
                    </div>
                    <div>
                        <dl class="space-y-1">
                            <div class="flex">
                                <dt class="w-32 font-medium">Largura:</dt>
                                <dd>{{ formData.base_width }}cm</dd>
                            </div>
                        </dl>
                    </div>
                    <div>
                        <dl class="space-y-1">
                            <div class="flex">
                                <dt class="w-32 font-medium">Profundidade:</dt>
                                <dd>{{ formData.base_depth }}cm</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Cremalheira -->
            <div class="space-y-2">
                <h4 class="border-b pb-1 font-medium">Cremalheira</h4>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <dl class="space-y-1">
                            <div class="flex">
                                <dt class="w-48 font-medium">Largura da Cremalheira:</dt>
                                <dd>{{ formData.cremalheira_width }}cm</dd>
                            </div>
                            <div class="flex">
                                <dt class="w-48 font-medium">Altura do Furo:</dt>
                                <dd>{{ formData.hole_height }}cm</dd>
                            </div>
                        </dl>
                    </div>
                    <div>
                        <dl class="space-y-1">
                            <div class="flex">
                                <dt class="w-48 font-medium">Largura do Furo:</dt>
                                <dd>{{ formData.hole_width }}cm</dd>
                            </div>
                            <div class="flex">
                                <dt class="w-48 font-medium">Espaçamento entre Furos:</dt>
                                <dd>{{ formData.hole_spacing }}cm</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Prateleiras -->
            <div class="space-y-2">
                <h4 class="border-b pb-1 font-medium">Prateleiras</h4>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <dl class="space-y-1">
                            <div class="flex">
                                <dt class="w-32 font-medium">Altura Total:</dt>
                                <dd>{{ formData.height }}cm</dd>
                            </div>
                            <div class="flex">
                                <dt class="w-32 font-medium">Quantidade:</dt>
                                <dd>{{ formData.shelf_qty }}</dd>
                            </div>
                            <div class="flex">
                                <dt class="w-32 font-medium">Altura:</dt>
                                <dd>{{ formData.shelf_height }}cm</dd>
                            </div>
                        </dl>
                    </div>
                    <div>
                        <dl class="space-y-1">
                            <div class="flex">
                                <dt class="w-32 font-medium">Largura:</dt>
                                <dd>{{ formData.shelf_width }}cm</dd>
                            </div>
                            <div class="flex">
                                <dt class="w-32 font-medium">Profundidade:</dt>
                                <dd>{{ formData.shelf_depth }}cm</dd>
                            </div>
                            <div class="flex">
                                <dt class="w-32 font-medium">Tipo de Produto:</dt>
                                <dd>{{ formData.tipo_produto === 'normal' ? 'Normal' : 'Pendurável' }}</dd>
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
    const alturaBase = props.formData.base_height || 17; // Usar valor padrão se não estiver definido
    const alturaUtil = alturaTotal - alturaBase;
    const espacamento = props.formData.shelf_qty > 1 ? (alturaUtil / (props.formData.shelf_qty - 1)).toFixed(1) : 0;
    return espacamento;
};
</script>
