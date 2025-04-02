<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Planogram\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Callcocam\Planogram\Http\Requests\Segment\Api\UpdateSegmentRequest;
use Callcocam\Planogram\Http\Requests\Segment\Api\StoreSegmentRequest;
use Callcocam\Planogram\Models\Segment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SegmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $query = Segment::query()
            ->with(['tenant'])
            ->when($request->tenant_id, fn($query) => $query->where('tenant_id', $request->tenant_id))
            ->when($request->status, fn($query) => $query->where('status', $request->status))
            ->when($request->search, fn($query) => $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%");
            }));

        $segments = $query->latest()->get();

        return $segments;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(StoreSegmentRequest $request)
    {
        $validated = $request->validated();

        $segment = Segment::create($validated);

        return response()->json($segment, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param Segment $segment
     * @return Response
     */
    public function show(Segment $segment)
    {
        return response()->json($segment->load('tenant'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Segment $segment
     * @return Response
     */
    public function update(UpdateSegmentRequest $request, Segment $segment)
    {
        $data =   $request->all();
        DB::beginTransaction();
        try {
            if ($request->has('increaseQuantity')) {
                $segment->update([
                    'quantity' => data_get($data, 'quantity')
                ]);
            } elseif ($request->has('decreaseQuantity')) {
                if ($segment->quantity > 1) {
                    $segment->update([
                        'quantity' => data_get($data, 'quantity')
                    ]);
                }
            } else {
                $segment->update($request->validated());
            }

            DB::commit();
            return response()->json([
                'title' => 'Camada de produto atualizada com sucesso',
                'description' => 'Camada de produto atualizada com sucesso',
                'variant' => 'default',
                'message' => 'Camada de produto atualizada com sucesso'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'title' => 'Erro ao atualizar camada de produto',
                'description' => 'Erro ao atualizar camada de produto: ' . $e->getMessage(),
                'variant' => 'destructive',
                'message' => 'Erro ao atualizar camada de produto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Segment $segment
     * @return Response
     */
    public function destroy(Segment $segment)
    {
        $segment->delete();

        return response()->json(null, 204);
    }
}
