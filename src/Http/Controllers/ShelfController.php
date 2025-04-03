<?php

/**
 * Controller responsável pelo gerenciamento de prateleiras no Planograma.
 * 
 * Gerencia as operações CRUD para o modelo Shelf (Prateleira) e suas relações
 * como Segments, Layers e Products.
 * 
 * @author Claudio Campos
 * @email callcocam@gmail.com, contato@sigasmart.com.br
 * @website https://www.sigasmart.com.br
 */

namespace Callcocam\Planogram\Http\Controllers;

use App\Http\Controllers\Controller;
use Callcocam\Planogram\Http\Requests\Shelf\StoreShelfRequest;
use Callcocam\Planogram\Http\Requests\Shelf\UpdateShelfRequest;
use Callcocam\Planogram\Models\Layer;
use App\Models\Product;
use Callcocam\Planogram\Models\Section;
use Callcocam\Planogram\Models\Segment;
use Callcocam\Planogram\Models\Shelf;
use Callcocam\Planogram\Services\ShelfPositioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class ShelfController extends Controller
{
    /**
     * Serviço para posicionamento de prateleiras
     * 
     * @var ShelfPositioningService
     */
    protected $shelfService;

    /**
     * Construtor do controller
     */
    public function __construct()
    {
        $this->shelfService = new ShelfPositioningService();
    }

    /**
     * Cria uma nova prateleira
     *
     * @param StoreShelfRequest $request Requisição validada para criação
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreShelfRequest $request)
    {
        $data = $request->validated();
        $section = Section::findOrFail(data_get($data, 'section_id'));

        // Calcula a posição padrão baseada na quantidade de prateleiras
        $quantity = $section->shelves()->count();
        $dataPosition = data_get($data, 'position', 0);
        $position = $quantity > 0 ? ($dataPosition ?? 40 * $quantity) : $dataPosition;

        // Cria a prateleira com valores padrão quando necessário
        $shelf = $section->shelves()->create([
            'user_id' => Auth::id(),
            'height' => $request->height ?? 4,
            'depth' => $request->depth ?? 40,
            'position' => $position,
            'ordering' => $section->shelves()->count(),
            'status' => 'published',
        ]);

        return redirect()->back()
            ->with('success', 'Prateleira criada com sucesso')
            ->with('record', $shelf);
    }

    /**
     * Atualiza uma prateleira existente
     *
     * @param UpdateShelfRequest $request Requisição validada para atualização
     * @param Shelf $shelf Prateleira a ser atualizada
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateShelfRequest $request, Shelf $shelf)
    {
        try {
            $validated = $request->validated();

            // Verifica se é uma operação de inversão de segmentos
            if ($request->has('invert')) {
                return $this->handleSegmentReordering($request, $shelf);
            }

            // Processa atualização normal com possível adição de segmento/camada
            $segment = data_get($validated, 'segment');
            $layer = data_get($segment, 'layer');

            // Valida capacidade da prateleira se houver novo segmento com camada
            if ($segment && $layer) {
                $validationError = $this->validateShelfCapacity($shelf, $segment, $layer);
                if ($validationError) {
                    return redirect()->back()->with('error', $validationError);
                }
            }

            // Atualiza a prateleira
            $shelf->update($validated);

            // Processa segmento existente ou cria novo
            if ($segment) {
                $this->processSegment($shelf, $segment, $layer);
            }

            return redirect()->back()
                ->with('success', 'Prateleira atualizada com sucesso')
                ->with('record', $this->loadShelfRelations($shelf));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Erro ao atualizar a prateleira: ' . $e->getMessage());
        }
    }

    /**
     * Remove uma prateleira
     *
     * @param Shelf $shelf Prateleira a ser excluída
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Shelf $shelf)
    {
        try {
            $shelf->delete();
            return redirect()->back()->with('success', 'Prateleira excluída com sucesso');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Erro ao excluir a prateleira: ' . $e->getMessage());
        }
    }

    /**
     * Atualiza a seção de uma prateleira
     *
     * @param Request $request Requisição com validação inline
     * @param Shelf $shelf Prateleira a ser atualizada
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSection(Request $request, Shelf $shelf)
    {
        $validated = $request->validate([
            'section_id' => 'required|exists:sections,id',
            'new_position' => 'nullable|integer|min:0',
        ]);

        try {
            // Encontra posição mais próxima baseada nos furos disponíveis
            $position = $this->shelfService->findClosestHole(
                data_get($validated, 'new_position', $shelf->shelf_position),
                data_get($shelf->section, 'settings.holes', [])
            );

            // Calcula posição ajustada
            $holeHeight = data_get($position, 'height', 0);
            $holePosition = data_get($position, 'position', 0);
            $scaledShelfHeight = $shelf->shelf_height;
            $heightDifference = $scaledShelfHeight - $holeHeight;
            $topPosition = $holePosition - $heightDifference / 2;

            // Atualiza a prateleira
            $shelf->update([
                'section_id' => $validated['section_id'],
                'shelf_position' => $topPosition
            ]);

            // Atualiza a contagem de prateleiras na seção
            $section = Section::find($validated['section_id']);
            $section->update(['num_shelves' => $section->shelves()->count()]);

            return redirect()->back()
                ->with('success', 'Seção da prateleira atualizada com sucesso')
                ->with('record', $shelf->load('section'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Erro ao atualizar seção: ' . $e->getMessage());
        }
    }

    /**
     * Processa a reordenação de segmentos
     * 
     * @param Request $request Requisição contendo segmentos
     * @param Shelf $shelf Prateleira relacionada
     * @return \Illuminate\Http\RedirectResponse
     */
    private function handleSegmentReordering(Request $request, Shelf $shelf)
    {
        $segments = $request->get('segments', []);

        foreach ($segments as $ordering => $segmentData) {
            $segment = Segment::find(data_get($segmentData, 'id'));
            if ($segment) {
                $segment->update(['ordering' => $ordering]);
            }
        }

        return redirect()->back()
            ->with('success', 'Segmentos reordenados com sucesso')
            ->with('record', $this->loadShelfRelations($shelf));
    }

    /**
     * Processa criação ou atualização de segmento
     * 
     * @param Shelf $shelf Prateleira relacionada
     * @param array $segmentData Dados do segmento
     * @param array|null $layerData Dados da camada (opcional)
     * @return void
     */
    private function processSegment(Shelf $shelf, array $segmentData, ?array $layerData = null)
    {
        $segmentId = data_get($segmentData, 'id');

        // Atualiza segmento existente
        if ($model = Segment::find($segmentId)) {
            $model->update([
                'shelf_id' => $shelf->id,
                'ordering' => $shelf->segments()->count(),
            ]);
        }
        // Cria novo segmento com possível camada
        elseif (!empty($segmentData)) {
            $newSegment = $shelf->segments()->create($segmentData);

            if ($newSegment && $layerData) {
                $newSegment->layer()->create($layerData);
            }
        }
    }

    /**
     * Carrega relações da prateleira para exibição
     * 
     * @param Shelf $shelf Prateleira a ser carregada
     * @return Shelf Prateleira com relações
     */
    private function loadShelfRelations(Shelf $shelf)
    {
        return $shelf->load([
            'segments',
            'segments.layer',
            'segments.layer.product',
            'segments.layer.product.image'
        ]);
    }

    /**
     * Valida se há espaço suficiente na prateleira para o segmento
     * 
     * @param Shelf $shelf Prateleira de destino
     * @param array $segment Dados do segmento
     * @param array $layer Dados da camada
     * @return string|null Mensagem de erro ou null se válido
     */
    protected function validateShelfCapacity(Shelf $shelf, array $segment, array $layer)
    {
        // Se for a primeira prateleira, não há restrições de espaço
        if ($shelf->segments()->count() === 0) {
            return null;
        }

        // Calcula a largura total ocupada por todos os segmentos na prateleira
        $totalWidth = 0;
        $lastWidth = 0;

        foreach ($shelf->segments as $seg) {
            if (!isset($seg->layer) || !isset($seg->layer->product)) {
                continue;
            }

            $productWidth = (float) $seg->layer->product->width;
            $quantity = (int) data_get($seg, 'layer.quantity', 1);
            $spacing = (float) data_get($seg, 'layer.spacing', 0);

            $totalWidth += $productWidth * $quantity;

            if ($spacing > 0) {
                $totalWidth += $spacing;
            }

            $lastWidth = $productWidth;
        }

        // Valida e adiciona a largura do novo produto/camada
        if ($layer) {
            $product = Product::find(data_get($layer, 'product_id'));

            if (!$product) {
                return "Produto não encontrado.";
            }

            $productWidth = (float) $product->width;
            $quantity = (int) data_get($layer, 'quantity', 1);
            $spacing = (float) data_get($layer, 'spacing', 0);

            if ($quantity <= 0) {
                return "A quantidade deve ser maior que zero.";
            }

            $totalWidth += $productWidth * $quantity;

            if ($spacing > 0) {
                $totalWidth += $spacing;
            }
        }

        // Verifica se a largura total excede a largura disponível na seção
        $sectionWidth = $shelf->section->width - $lastWidth;

        if ($totalWidth > $sectionWidth) {
            return "A quantidade máxima de produtos para esta camada foi atingida.";
        }

        return null;
    }
}
