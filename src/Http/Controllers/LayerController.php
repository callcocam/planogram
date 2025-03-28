<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Planogram\Http\Controllers;

use App\Http\Controllers\Controller;
use Callcocam\Planogram\Http\Requests\Layer\StoreLayerRequest;
use Callcocam\Planogram\Http\Requests\Layer\UpdateLayerRequest;
use Callcocam\Planogram\Http\Resources\LayerResource;
use Callcocam\Planogram\Models\Layer;
use Callcocam\Planogram\Models\Segment;

class LayerController extends Controller
{
    public function store(StoreLayerRequest $request, Segment $segment)
    {
        $layer = $segment->layer()->create($request->validated());

        return response()->json([
            'data' => new LayerResource($layer),
        ], 201);
    }

    public function update(UpdateLayerRequest $request, Layer $layer)
    {
        try {
            $layer->update($request->validated());
            return redirect()->back()->with('success', 'Camada atualizada com sucesso');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao atualizar a camada: ' . $e->getMessage());
        }
    }


    public function destroy(Layer $layer)
    {
        try {
            $layer->delete();
            $layer->segment()->delete();
            return redirect()->back()->with('success', 'Camada excluída com sucesso');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao excluir a camada: ' . $e->getMessage());
        }
    }
}
