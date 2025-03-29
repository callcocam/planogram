import type { InjectionKey } from 'vue'
import type { ConfirmComposable } from './../composables/useConfirm'

// Chave de injeção para tipagem forte
export const confirmKey: InjectionKey<ConfirmComposable> = Symbol('confirm')