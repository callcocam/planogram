<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Planogram\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SegmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'user_id' => $this->user_id,
            'shelf_id' => $this->shelf_id,
            'segment_id' => $this->segment_id,
            'width' => $this->width,
            'ordering' => $this->ordering,
            'position' => $this->position,
            'quantity' => $this->quantity,
            'spacing' => $this->spacing,
            'layer' => new LayerResource($this->whenLoaded('layer')),
        ];
    }
}
