<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Planogram\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Callcocam\Planogram\Models\Layer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LayerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $query = Layer::query()
            ->with(['tenant'])
            ->when($request->tenant_id, fn($query) => $query->where('tenant_id', $request->tenant_id))
            ->when($request->status, fn($query) => $query->where('status', $request->status))
            ->when($request->search, fn($query) => $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%");
            }));

        $layers = $query->latest()->get();

        return $layers;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'tenant_id' => 'required|exists:tenants,id',
            'status' => 'nullable|string|max:20',
        ]);

        $layer = Layer::create($validated);

        return response()->json($layer, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param Layer $layer
     * @return Response
     */
    public function show(Layer $layer)
    {
        return response()->json($layer->load('tenant'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Layer $layer
     * @return Response
     */
    public function update(Request $request, Layer $layer)
    {
        DB::beginTransaction();
        try {
            if ($request->has('increaseQuantity')) {
                $layer->update([
                    'quantity' => $request->increaseQuantity,
                ]);
            } elseif ($request->has('decreaseQuantity')) {
                if ($layer->quantity > 1) {
                    $layer->update([
                        'quantity' => $request->decreaseQuantity,
                    ]);
                }
            } else {
                $layer->update($request->validated());
            }

            DB::commit();
            return response()->json(['message' => 'Camada de produto atualizada com sucesso'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erro ao atualizar camada de produto: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Layer $layer
     * @return Response
     */
    public function destroy(Layer $layer)
    {
        $layer->delete();

        return response()->json(null, 204);
    }
}
