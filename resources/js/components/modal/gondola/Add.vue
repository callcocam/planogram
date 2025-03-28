<template>
    <Dialog :open="isOpen" @update:open="updateOpen">
        <DialogContent class="max-w-2xl">
            <DialogHeader>
                <DialogTitle>Adicionar Nova Gôndola</DialogTitle>
                <DialogDescription> Preencha os detalhes da gôndola para adicionar ao planograma. </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submitForm">
                <div class="grid grid-cols-1 gap-4 py-4 md:grid-cols-2">
                    <!-- Informações Básicas -->
                    <div class="space-y-4 md:col-span-2">
                        <h3 class="text-sm font-medium">Informações Básicas</h3>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="name">Nome *</Label>
                                <Input id="name" v-model="form.gondola.name" required />
                            </div>

                            <div class="space-y-2">
                                <Label for="location">Localização</Label>
                                <Input id="location" v-model="form.gondola.location" placeholder="Ex: Setor de bebidas" />
                            </div>
                        </div>
                    </div>

                    <!-- Dimensões Principais -->
                    <div class="space-y-4 md:col-span-2">
                        <h3 class="text-sm font-medium">Dimensões Principais</h3>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="height">Altura (cm)</Label>
                                <Input id="height" type="number" v-model="form.gondola.height" min="1" />
                            </div>

                            <div class="space-y-2">
                                <Label for="width">Largura (cm)</Label>
                                <Input id="width" type="number" v-model="form.gondola.width" min="1" />
                            </div>

                            <div class="space-y-2">
                                <Label for="thickness">Espessura (cm)</Label>
                                <Input id="thickness" type="number" v-model="form.gondola.thickness" min="1" />
                            </div>
                        </div>
                    </div>

                    <!-- Detalhes Técnicos -->
                    <div class="space-y-4 md:col-span-2">
                        <h3 class="text-sm font-medium">Detalhes Técnicos</h3>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="base_height">Altura da Base (cm)</Label>
                                <Input id="base_height" type="number" v-model="form.gondola.base_height" min="1" />
                            </div>

                            <div class="space-y-2">
                                <Label for="shelf_height">Altura da Prateleira (cm)</Label>
                                <Input id="shelf_height" type="number" v-model="form.gondola.shelf_height" min="1" />
                            </div>

                            <div class="space-y-2">
                                <Label for="scale_factor">Fator de Escala</Label>
                                <Input id="scale_factor" type="number" v-model="form.gondola.scale_factor" min="1" />
                            </div>
                        </div>
                    </div>

                    <!-- Detalhes dos Furos -->
                    <div class="space-y-4 md:col-span-2">
                        <h3 class="text-sm font-medium">Detalhes dos Furos</h3>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="hole_spacing">Espaçamento entre Furos (cm)</Label>
                                <Input id="hole_spacing" type="number" v-model="form.gondola.hole_spacing" min="1" />
                            </div>

                            <div class="space-y-2">
                                <Label for="hole_diameter">Diâmetro dos Furos (cm)</Label>
                                <Input id="hole_diameter" type="number" v-model="form.gondola.hole_diameter" min="1" />
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="space-y-2 md:col-span-2">
                        <Label for="status">Status</Label>
                        <Select v-model="form.gondola.status">
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

                <DialogFooter>
                    <Button type="button" variant="outline" @click="closeModal">Cancelar</Button>
                    <Button type="submit" :disabled="isSubmitting">
                        <Loader2Icon v-if="isSubmitting" class="mr-2 h-4 w-4 animate-spin" />
                        Adicionar Gôndola
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

<script setup>
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useForm } from '@inertiajs/vue3';
import { Loader2Icon } from 'lucide-vue-next';
import { ref, watch } from 'vue';

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
const isSubmitting = ref(false);

// Watch para sincronizar a propriedade open com o estado interno
watch(
    () => props.open,
    (newVal) => {
        isOpen.value = newVal;
    },
);

// Formulário com valores padrão
const formDefaults = {
    gondola: {
        planogram_id: props.planogramId,
        name: '',
        location: 'Centro de Distribuição',
        height: 180, // Valor padrão: 200cm
        width: 130, // Valor padrão: 100cm
        thickness: 4, // Padrão da migration
        base_height: 17, // Padrão da migration
        shelf_height: 4, // Padrão da migration
        hole_spacing: 2, // Padrão da migration
        hole_diameter: 2, // Padrão da migration
        scale_factor: 3, // Padrão da migration
        status: 'published', // Status padrão
    },
};

const form = useForm({ ...formDefaults });

// Função para atualizar o estado do modal e emitir evento
const updateOpen = (value) => {
    isOpen.value = value;
    emit('update:open', value);

    if (!value) {
        emit('close');
        resetForm();
    }
};

// Função para fechar o modal
const closeModal = () => {
    updateOpen(false);
};

// Função para resetar o formulário
const resetForm = () => {
    form.reset();
    form.clearErrors();
    Object.keys(formDefaults).forEach((key) => {
        form[key] = formDefaults[key];
    });
};

// Função para submeter o formulário
const submitForm = () => {
    isSubmitting.value = true;

    form.put(
        route('planogram.planogram.update', {
            planogram: props.planogramId,
        }),
        {
            preserveScroll: true,
            onSuccess: () => {
                emit('gondola-added');
                closeModal();
                // Exibir mensagem de sucesso
                // toast({
                //     title: 'Gôndola adicionada',
                //     description: 'A gôndola foi adicionada com sucesso ao planograma.',
                //     variant: 'success',
                // });
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        },
    );
};
</script>
