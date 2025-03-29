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
            // Validar e atualizar o planograma
            $data = $request->validated();
            $planogram->update($data);

            // Se temos dados para criar uma nova gôndola
            if ($request->has('gondola_name')) {
                // Dados da gôndola
                $gondolaData = [
                    'planogram_id' => $planogram->id,
                    'name' => $request->input('gondola_name'),
                    'num_modulos' => $request->input('num_modulos'),
                    'tipo_produto' => $request->input('tipo_produto'),
                    'location' => $request->input('location'),
                    'height' => $request->input('height'),
                    'width' => $request->input('width'),
                    'thickness' => $request->input('thickness', 4),
                    'base_height' => $request->input('base_height', 17),
                    'shelf_height' => $request->input('shelf_height', 4),
                    'hole_spacing' => $request->input('hole_spacing', 2),
                    'hole_diameter' => $request->input('hole_diameter', 2),
                    'scale_factor' => $request->input('scale_factor', 3),
                    'status' => $request->input('status', 'published'),
                ];

                // Criar a gôndola
                $gondola = $planogram->gondolas()->create($gondolaData);

                // Se temos dados para criar uma seção
                if ($request->has('section')) {
                    $sectionData = $request->input('section');
                    $num_modulos = $sectionData['num_modulos'] ?? 1;
                    for ($num = 0; $num < $num_modulos; $num++) {
                        // Preparar dados da seção
                        $sectionToCreate = [
                            'gondola_id' => $gondola->id,
                            'name' => $num . '# Seção',
                            'code' => 'S' . now()->format('ymd') . rand(1000, 9999),
                            'width' => $sectionData['width'] ?? $gondolaData['width'],
                            'status' => 'published'
                        ];

                        // Criar a seção
                        $section = $gondola->sections()->create($sectionToCreate);

                        // Criar prateleiras com base na quantidade solicitada
                        $shelfQty = $sectionData['shelf_qty'] ?? 5;
                        $depth = $sectionData['depth'] ?? 40;

                        // Calcular espaçamento entre prateleiras
                        $totalHeight = $gondolaData['height'];
                        $baseHeight = $gondolaData['base_height'];
                        $availableHeight = $totalHeight - $baseHeight;
                        $spacing = $shelfQty > 1 ? $availableHeight / ($shelfQty - 1) : 0;

                        // Criar prateleiras
                        for ($i = 0; $i < $shelfQty; $i++) {
                            // Calcular posição vertical da prateleira
                            $position = $i === 0 ? 0 : $baseHeight + ($i - 1) * $spacing;

                            $shelfData = [
                                'section_id' => $section->id,
                                'code' => 'SLF' . $i . '-' . now()->format('ymd') . rand(100, 999),
                                'height' => $gondolaData['shelf_height'],
                                'depth' => $depth,
                                'position' => round($position),
                                'ordering' => $i,
                                'status' => 'published'
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
            $planogram = Planogram::create($request->validated());
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
