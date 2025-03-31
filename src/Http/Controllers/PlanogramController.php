<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Planogram\Http\Controllers;

use App\Http\Controllers\Controller;
use Callcocam\Planogram\Http\Requests\Planogram\StorePlanogramRequest;
use Callcocam\Planogram\Http\Requests\Planogram\UpdatePlanogramRequest;
use Callcocam\Planogram\Models\Planogram;
use Callcocam\Planogram\Models\Gondola;
use Callcocam\Planogram\Models\Section;
use Callcocam\Planogram\Models\Shelf;
use Callcocam\Planogram\Services\ShelfPositioningService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Class PlanogramController
 * 
 * Controller responsável por gerenciar operações CRUD para o modelo Planogram.
 * Herda funcionalidades do RaptorController para automatizar tarefas comuns.
 */
class PlanogramController extends Controller
{
    public function update(UpdatePlanogramRequest $request, Planogram $planogram)
    {
        DB::beginTransaction();

        try {
            $planogram->gondolas->map(function ($gondola) use ($request) {
                // Atualizar gôndola
                $gondola->sections->map(function ($section) use ($request) {
                    // Atualizar seção
                    $section->shelves->map(function ($shelf) use ($request) {
                        // Atualizar prateleira
                        $shelf->forceDelete();
                    });
                    $section->forceDelete();
                });
                $gondola->forceDelete();
            });
            // Validar e atualizar o planograma
            $data = $request->validated();
            $planogram->update($data);

            // Se temos dados para criar uma nova gôndola
            if ($request->has('gondola_name')) {
                $shelfService =  new ShelfPositioningService();
                // Obtém os dados da seção, se disponíveis
                $sectionData = $request->has('section') ? $request->input('section') : [];

                // Dados da gôndola
                $gondolaData = [
                    'planogram_id' => $planogram->id,
                    'name' => $request->input('gondola_name'),
                    'location' => $request->input('location'),
                    'side' => $request->input('side', null),
                    'flow' => $request->input('flow', 'left_to_right'),
                    'scale_factor' => $request->input('scale_factor', 3),
                    'num_modulos' => isset($sectionData['num_modulos']) ? (int)$sectionData['num_modulos'] : 1,
                    'status' => $request->input('status', 'draft'),
                    'user_id' => auth()->id(),
                    'tenant_id' => $planogram->tenant_id,
                ];

                // Criar a gôndola
                $gondola = $planogram->gondolas()->create($gondolaData);

                // Se temos dados para criar seções
                if ($request->has('section')) {
                    $sectionData = $request->input('section');
                    $num_modulos = isset($sectionData['num_modulos']) ? (int)$sectionData['num_modulos'] : 1;

                    for ($num = 0; $num < $num_modulos; $num++) {
                        // Nome da seção
                        $sectionName = $num . '# ' . ($sectionData['name'] ?? 'Seção');
                        if ($sectionData['name'] == 'undefined - Seção') {
                            $sectionName = $num . '# Seção';
                        }

                        $sectionSettings = $sectionData['settings'] ?? [];
                        $sectionSettings['holes'] = $shelfService->calculateHoles($sectionData, $gondola->scale_factor);

                        // Preparar dados da seção
                        $sectionToCreate = [
                            'gondola_id' => $gondola->id,
                            'name' => $sectionName,
                            'code' => 'S' . now()->format('ymd') . rand(1000, 9999),
                            'width' => data_get($sectionData, 'width', 130),
                            'height' => data_get($sectionData, 'height', 180),
                            'num_shelves' =>  data_get($sectionData, 'num_shelves', 4),
                            'base_height' => data_get($sectionData, 'base_height', 17),
                            'base_depth' => data_get($sectionData, 'base_depth', 40),
                            'base_width' => data_get($sectionData, 'base_width', 17),
                            'cremalheira_width' => data_get($sectionData, 'cremalheira_width', 4),
                            'hole_height' => data_get($sectionData, 'hole_height', 2),
                            'hole_width' => data_get($sectionData, 'hole_width', 2),
                            'hole_spacing' => data_get($sectionData, 'hole_spacing', 2),
                            'ordering' => $num,
                            'settings' =>  $sectionSettings,
                            'status' => $request->input('status', 'draft'),
                            'user_id' => auth()->id(),
                            'tenant_id' => $planogram->tenant_id,
                        ];

                        // Criar a seção
                        $section = $gondola->sections()->create($sectionToCreate);

                        // Definir a quantidade de prateleiras
                        $shelfQty = data_get($sectionData, 'num_shelves', 4);
                        $product_type = data_get($sectionData, 'product_type', 'normal'); 

                        // Criar prateleiras
                        for ($i = 0; $i < $shelfQty; $i++) {
                            // Calcular posição vertical da prateleira (shelf_position)
                            $position = $shelfService->calculateShelfPosition($shelfQty, data_get($sectionData, 'shelf_height', 4), data_get($sectionSettings, 'holes', []), $i, $gondola->scale_factor);

                            $shelfData = [
                                'section_id' => $section->id,
                                'code' => 'SLF' . $i . '-' . now()->format('ymd') . rand(100, 999),
                                'product_type' => $product_type,
                                'shelf_width' => data_get($sectionData, 'shelf_width', 130),
                                'shelf_height' => data_get($sectionData, 'shelf_height', 4),
                                'shelf_depth' => data_get($sectionData, 'shelf_depth', 40),
                                'shelf_position' => round($position),
                                'ordering' => $i,
                                'settings' => [],
                                'status' => $request->input('status', 'draft'),
                                'user_id' => auth()->id(),
                                'tenant_id' => $planogram->tenant_id,
                            ];

                            $section->shelves()->create($shelfData);
                        }
                    }
                }

                DB::commit();
                return redirect()->back()->with('success', 'Planograma, gôndola, seção e prateleiras criados com sucesso');
            }

            DB::commit();
            return redirect()->back()->with('success', 'Planograma atualizado com sucesso');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar planograma: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao atualizar o planograma: ' . $e->getMessage());
        }
    }

    public function store(StorePlanogramRequest $request)
    {
        try {
            $data = $request->validated();
            $data['user_id'] = auth()->id();
            $data['tenant_id'] = $request->input('tenant_id', null);
            $planogram = Planogram::create($data);
            return redirect()->back()->with('success', 'Planogram criado com sucesso');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao criar o planogram: ' . $e->getMessage());
        }
    }

    public function destroy(Planogram $planogram)
    {
        try {
            $planogram->delete();
            return redirect()->back()->with('success', 'Planogram excluído com sucesso');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao excluir o planogram: ' . $e->getMessage());
        }
    }
 
}
