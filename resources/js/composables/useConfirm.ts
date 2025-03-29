import { ref, reactive } from 'vue'

interface ConfirmConfig {
    title: string
    message: string
    confirmButtonText: string
    cancelButtonText: string
    isDangerous: boolean
}

interface ConfirmOptions extends Partial<ConfirmConfig> { }

export function useConfirm() {
    const isOpen = ref<boolean>(false)
    const config = reactive<ConfirmConfig>({
        title: 'Confirmar',
        message: '',
        confirmButtonText: 'Confirmar',
        cancelButtonText: 'Cancelar',
        isDangerous: false
    })

    let resolvePromise: ((value: boolean) => void) | null = null

    /**
     * Abre o modal de confirmação e retorna uma promise
     * @param options - Opções de configuração do modal
     * @returns Promise que resolve com true (confirmado) ou false (cancelado)
     */
    const confirm = (options: ConfirmOptions = {}): Promise<boolean> => {
        // Mescla as opções padrão com as opções fornecidas
        Object.assign(config, options)
        isOpen.value = true

        return new Promise<boolean>((resolve) => {
            resolvePromise = resolve
        })
    }

    /**
     * Utilitário para confirmar ações perigosas como exclusão
     * @param options - Opções de configuração
     * @returns Promise que resolve com true (confirmado) ou false (cancelado)
     */
    const confirmDeletion = (options: ConfirmOptions = {}): Promise<boolean> => {
        return confirm({
            title: 'Confirmar exclusão',
            message: 'Tem certeza que deseja excluir este item? Esta ação não pode ser desfeita.',
            confirmButtonText: 'Excluir',
            cancelButtonText: 'Cancelar',
            isDangerous: true,
            ...options
        })
    }

    /**
     * Utilitário para confirmar ações de salvamento
     * @param options - Opções de configuração
     * @returns Promise que resolve com true (confirmado) ou false (cancelado)
     */
    const confirmSave = (options: ConfirmOptions = {}): Promise<boolean> => {
        return confirm({
            title: 'Salvar alterações',
            message: 'Deseja salvar as alterações realizadas?',
            confirmButtonText: 'Salvar',
            cancelButtonText: 'Cancelar',
            isDangerous: false,
            ...options
        })
    }

    // Função para confirmar a ação
    const onConfirm = (): void => {
        isOpen.value = false
        if (resolvePromise) {
            resolvePromise(true)
            resolvePromise = null
        }
    }

    // Função para cancelar a ação
    const onCancel = (): void => {
        isOpen.value = false
        if (resolvePromise) {
            resolvePromise(false)
            resolvePromise = null
        }
    }

    return {
        isOpen,
        config,
        confirm,
        confirmDeletion,
        confirmSave,
        onConfirm,
        onCancel
    }
}

// Tipo exportado para uso no provider
export type ConfirmComposable = ReturnType<typeof useConfirm>