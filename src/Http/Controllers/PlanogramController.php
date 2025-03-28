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
use Closure;

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
        try {
            $data = $request->validated();
            $gondola = $request->input('gondola', []);
            $planogram->update($data);
            if (data_get($gondola, 'id')) {
                $gondola = $planogram->gondolas()->update($gondola);
            } else {
                $planogram->gondolas()->create($gondola);
            }
            return redirect()->back()->with('success', 'Planogram atualizado com sucesso');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao atualizar o planogram: ' . $e->getMessage());
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
