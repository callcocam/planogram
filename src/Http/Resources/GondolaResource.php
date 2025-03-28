<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\Planogram\Http\Resources;
 
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GondolaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'width' => $this->width,
            'height' => $this->height,
            'base_height' => $this->base_height,
            'thickness' => $this->thickness, // espessura da gramalheira
            'scale_factor' => $this->scale_factor,
            'location' => $this->location,
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
                'color' => $this->status->color(),
            ],
            'sections' => SectionResource::collection($this->whenLoaded('sections')),
        ];
    }
}
