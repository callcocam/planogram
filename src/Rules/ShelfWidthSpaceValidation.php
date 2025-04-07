<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\Planogram\Rules;
use Callcocam\Planogram\Models\Layer;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
class ShelfWidthSpaceValidation implements ValidationRule
{
    protected $layerId;
    protected $request;
    public function __construct($layerId, $request)
    {
        $this->layerId = $layerId;
        $this->request = $request;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Obtém o segmento, a prateleira e a seção
        $layer = Layer::with(['product', 'segment.shelf.section'])->find($this->layerId);
        if (! $layer) {
            $fail('Layer inválido.');
            return;
        }
        $segment = $layer->segment;
        $shelf = $segment->shelf;
        $sectionWidth = $shelf->section->width;
        
        // Calcula a largura total de todos os segmentos
        $totalWidth = 0;
        
        foreach ($shelf->segments as $seg) {
            $productWidth = $seg->layer->product->width;
            $quantity = $seg->id === $segment->id ? $value : $seg->layer->quantity;
            $spacing = $seg->layer->spacing ? (float) $seg->layer->spacing : 0;
            
            // Para n produtos, precisamos de (n-1) espaçamentos entre eles
            // O espaçamento total seria spacing * (quantity - 1)
            // Se quantity for 0 ou 1, não há espaçamento
            $totalSpacing = $quantity > 1 ? $spacing * ($quantity - 1) : 0;
            
            // A largura total para este segmento é: largura dos produtos + espaçamento total
            $totalWidth += ($productWidth * $quantity) + $totalSpacing;
        }
        
        if ($totalWidth > $sectionWidth) {
            $remainingWidth = $sectionWidth;
            
            // Subtrai a largura dos outros segmentos
            foreach ($shelf->segments as $seg) {
                if ($seg->id !== $segment->id) {
                    $productWidth = $seg->layer->product->width;
                    $segQuantity = $seg->layer->quantity;
                    $spacing = $seg->layer->spacing ? (float) $seg->layer->spacing : 0;
                    
                    // Calcula o espaçamento total para este segmento
                    $totalSpacing = $segQuantity > 1 ? $spacing * ($segQuantity - 1) : 0;
                    
                    // Subtrai a largura deste segmento do espaço restante
                    $remainingWidth -= ($productWidth * $segQuantity) + $totalSpacing;
                }
            }
            
            // Calcula quantidade máxima possível para este segmento
            $productWidth = $layer->product->width;
            $spacing = $layer->spacing ? (float) $layer->spacing : 0;
            
            // Para calcular o número máximo de produtos, precisamos considerar:
            // Se tivermos n produtos, precisamos de (n-1) espaçamentos
            // Então: remainingWidth = productWidth * n + spacing * (n-1)
            // Simplificando: remainingWidth = n * productWidth + n * spacing - spacing
            // Resolvendo para n: n = (remainingWidth + spacing) / (productWidth + spacing)
            // E aplicamos Math.floor para obter um número inteiro
            $maxProducts = 0;
            
            if ($spacing > 0) {
                $maxProducts = floor(($remainingWidth + $spacing) / ($productWidth + $spacing));
            } else {
                $maxProducts = floor($remainingWidth / $productWidth);
            }
            
            // Garantir que maxProducts não seja negativo
            $maxProducts = max(0, $maxProducts);
            
            $fail("A quantidade máxima de produtos para esta camada é {$spacing}.");
        }
    }
}