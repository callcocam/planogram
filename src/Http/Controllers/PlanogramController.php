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
            // $planogram->gondolas->map(function ($gondola) use ($request) {
            //     // Atualizar gôndola
            //     $gondola->sections->map(function ($section) use ($request) {
            //         // Atualizar seção
            //         $section->shelves->map(function ($shelf) use ($request) {
            //             // Atualizar prateleira
            //             $shelf->forceDelete();
            //         });
            //         $section->forceDelete();
            //     });
            //     $gondola->forceDelete();
            // });
            // Validar e atualizar o planograma
            $data = $request->validated();
            $planogram->update($data);

            // Se temos dados para criar uma nova gôndola
            if ($request->has('gondola_name')) {
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
                    'tenant_id' => $request->input('tenant_id', null),
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

                        // Preparar dados da seção
                        $sectionToCreate = [
                            'gondola_id' => $gondola->id,
                            'name' => $sectionName,
                            'code' => 'S' . now()->format('ymd') . rand(1000, 9999),
                            'width' => $sectionData['width'] ?? 130,
                            'height' => $sectionData['height'] ?? 180,
                            'base_height' => $sectionData['base_height'] ?? 17,
                            'base_depth' => $sectionData['base_depth'] ?? 40,
                            'base_width' => $sectionData['base_width'] ?? 130,
                            'cremalheira_width' => $sectionData['cremalheira_width'] ?? 4,
                            'hole_height' => $sectionData['hole_height'] ?? 2,
                            'hole_width' => $sectionData['hole_width'] ?? 2,
                            'hole_spacing' => $sectionData['hole_spacing'] ?? 2,
                            'ordering' => $num,
                            'settings' => $sectionData['settings'] ?? null,
                            'status' => $request->input('status', 'draft'),
                            'user_id' => auth()->id(),
                            'tenant_id' => $request->input('tenant_id', null),
                        ];

                        // Criar a seção
                        $section = $gondola->sections()->create($sectionToCreate);

                        // Definir a quantidade de prateleiras
                        $shelfQty = isset($sectionData['shelf_qty']) ? (int)$sectionData['shelf_qty'] : 5;
                        $product_type = $sectionData['product_type'] ?? 'normal';

                        // Calcular espaçamento entre prateleiras
                        $totalHeight = isset($sectionData['height']) ? (int)$sectionData['height'] : 180;
                        $baseHeight = isset($sectionData['base_height']) ? (int)$sectionData['base_height'] : 17;
                        $availableHeight = $totalHeight - $baseHeight;
                        $spacing = $shelfQty > 1 ? $availableHeight / ($shelfQty - 1) : 0;

                        // Criar prateleiras
                        for ($i = 0; $i < $shelfQty; $i++) {
                            // Calcular posição vertical da prateleira (shelf_position)
                            $position = $i === 0 ? 0 : $baseHeight + ($i - 1) * $spacing;

                            $shelfData = [
                                'section_id' => $section->id,
                                'code' => 'SLF' . $i . '-' . now()->format('ymd') . rand(100, 999),
                                'product_type' => $product_type,
                                'shelf_width' => $sectionData['shelf_width'] ?? 4,
                                'shelf_height' => $sectionData['shelf_height'] ?? 4,
                                'shelf_depth' => $sectionData['shelf_depth'] ?? 40,
                                'shelf_position' => round($position),
                                'ordering' => $i,
                                'settings' => $sectionData['settings'] ?? null,
                                'status' => $request->input('status', 'draft'),
                                'user_id' => auth()->id(),
                                'tenant_id' => $request->input('tenant_id', null),
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
