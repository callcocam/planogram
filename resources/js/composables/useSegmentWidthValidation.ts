import { ref, Ref } from 'vue';

// Interfaces para tipagem
interface Product {
  width: number;
  height: number;
}

interface Layer {
  id: number;
  product: Product;
  quantity: number;
  spacing?: string | number;
  segment?: {
    shelf?: {
      section?: {
        width: number;
      };
    };
  };
}

interface Segment {
  id: number;
  layer: Layer;
}

interface Shelf {
  segments: Segment[];
  quantity: number;
}

/**
 * Composable para validação de largura de segmentos
 * @returns Funções e estado relacionados à validação de largura
 */
export function useSegmentWidthValidation() {
  const validationError: Ref<string | null> = ref(null);
  
  /**
   * Verifica se um valor é um número válido
   * @param value - Valor a ser verificado
   * @returns true se for um número válido
   */
  const isValidNumber = (value: unknown): value is number => {
    return typeof value === 'number' && Number.isFinite(value) && !Number.isNaN(value);
  };
  
  /**
   * Acessa uma propriedade de forma segura através de um caminho
   * @param obj - Objeto a ser acessado
   * @param path - Caminho da propriedade (ex: "a.b.c")
   * @param defaultValue - Valor padrão caso a propriedade não exista
   * @returns Valor da propriedade ou defaultValue
   */
  const safelyGetProperty = <T>(obj: any, path: string, defaultValue: T): T => {
    if (!obj || !path) return defaultValue;
    
    const properties = path.split('.');
    let result = obj;
    
    for (const prop of properties) {
      if (result === null || result === undefined || typeof result !== 'object') {
        return defaultValue;
      }
      result = result[prop];
    }
    
    return result !== undefined ? result : defaultValue;
  };
  
  /**
   * Valida se a quantidade solicitada cabe na largura da seção
   * @param shelf - O objeto da prateleira contendo os segmentos
   * @param segment - O segmento atual sendo modificado
   * @param layer - O objeto da camada
   * @param value - O novo valor de quantidade
   * @returns Resultado da validação (true se válido)
   */
  const validateSegmentWidth = (
    shelf: Shelf | undefined, 
    segment: Segment | undefined, 
    layer: Layer | undefined, 
    value: number
  ): boolean => {
    try {
      validationError.value = null;
      
      // Verifica se os objetos necessários existem
      if (!shelf || !segment || !layer) {
        validationError.value = 'Dados incompletos para validação';
        return false;
      }
      
      // Verifica se shelf.segments existe e é um array
      const segments = safelyGetProperty<Segment[]>(shelf, 'segments', []);
      if (!segments || !Array.isArray(segments) || segments.length === 0) {
        validationError.value = 'Dados de segmentos não disponíveis';
        return false;
      } 
      // Verifica o valor da quantidade
      if (!isValidNumber(value) || value < 1) {
        validationError.value = 'Quantidade inválida';
        return false;
      }
      
      // Calcula a largura total de todos os segmentos
      let totalWidth = 0;
      let lastWidth = 0;
      
      for (const seg of segments) {
        // Pega as propriedades necessárias com verificação de segurança
        const segId = safelyGetProperty<number>(seg, 'id', 0);
        const productWidth = safelyGetProperty<number>(seg, 'layer.product.width', 0);
        const layerQuantity = safelyGetProperty<number>(seg, 'layer.quantity', 0);
        const spacingValue = safelyGetProperty<string | number | undefined>(seg, 'layer.spacing', '0');
        const spacing = typeof spacingValue === 'string' ? parseFloat(spacingValue) || 0 : (spacingValue || 0);
        
        // Pula se não tiver dados válidos
        if (!isValidNumber(productWidth) || productWidth <= 0) {
          continue;
        }
        
        // Determina a quantidade a ser usada
        const quantity = segId === segment.id ? value : layerQuantity;
        
        if (isValidNumber(quantity) && quantity > 0) {
          totalWidth += (productWidth * quantity) + spacing;
          lastWidth = productWidth;
        }
      }
      
      // Obtém a largura da seção
      const sectionWidth = safelyGetProperty<number>(layer, 'segment.shelf.section.width', 0);
      console.log('Largura da seção:', sectionWidth);
      
      // Se não conseguirmos obter a largura da seção, consideramos inválido
      if (!isValidNumber(sectionWidth) || sectionWidth <= 0) {
        validationError.value = 'Não foi possível determinar a largura da seção';
        return false;
      }
      
      const adjustedSectionWidth = sectionWidth - lastWidth;
      
      // Verifica se excede a largura disponível
      if (totalWidth > adjustedSectionWidth) {
        let remainingWidth = adjustedSectionWidth;
        
        // Subtrai a largura dos outros segmentos
        for (const seg of segments) {
          const segId = safelyGetProperty<number>(seg, 'id', 0);
          if (segId !== segment.id) {
            const productWidth = safelyGetProperty<number>(seg, 'layer.product.width', 0);
            const layerQuantity = safelyGetProperty<number>(seg, 'layer.quantity', 0);
            const spacingValue = safelyGetProperty<string | number | undefined>(seg, 'layer.spacing', '0');
            const spacing = typeof spacingValue === 'string' ? parseFloat(spacingValue) || 0 : (spacingValue || 0);
            
            if (isValidNumber(productWidth) && isValidNumber(layerQuantity) && 
                productWidth > 0 && layerQuantity > 0) {
              remainingWidth -= (productWidth * layerQuantity) + spacing;
            }
          }
        }
        
        // Verifica a largura do produto atual
        const currentProductWidth = safelyGetProperty<number>(layer, 'product.width', 0);
        if (!isValidNumber(currentProductWidth) || currentProductWidth <= 0) {
          validationError.value = 'Largura do produto não disponível';
          return false;
        }
        
        // Calcula quantidade máxima possível para este segmento
        const maxProducts = Math.floor(remainingWidth / currentProductWidth);
        
        // Garante que maxProducts seja um número válido
        const safeMaxProducts = isValidNumber(maxProducts) && maxProducts >= 0 ? maxProducts : 0;
        
        validationError.value = `A quantidade máxima de produtos para esta camada é ${safeMaxProducts}.`;
        return false;
      }
      
      return true;
    } catch (error) {
      console.error('Erro na validação de largura do segmento:', error);
      validationError.value = 'Ocorreu um erro durante a validação';
      return false;
    }
  };
  
  /**
   * Limpa o erro de validação
   */
  const clearValidationError = (): void => {
    validationError.value = null;
  };
  
  return {
    validateSegmentWidth,
    validationError,
    clearValidationError
  };
}