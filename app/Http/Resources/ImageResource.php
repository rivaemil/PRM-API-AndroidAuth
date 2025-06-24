<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'imageable_type' => $this->imageable_type,
            'imageable_id' => $this->imageable_id,
            'related' => $this->whenLoaded('imageable', function () {
                return [
                    'name' => $this->imageable->name ?? 'Sin nombre',
                    'description' => $this->imageable->description ?? 'Sin descripción',
                    'price' => $this->imageable->price ?? rand(100, 999), // si no tiene precio, genera uno aleatorio
                ];
            }),
        ];
    }
}
