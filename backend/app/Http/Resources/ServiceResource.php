<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'category' => $this->category,
            'icon' => $this->icon,
            'image' => $this->image,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'main_category' => $this->main_category,
            'sub_category' => $this->sub_category,
            'parent_id' => $this->parent_id,
            'selection_mode' => $this->selection_mode,
        ];
    }
}
