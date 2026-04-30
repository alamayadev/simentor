<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SkpResource extends JsonResource
{
    private bool $isDetailView = false;

    public function setIsDetailView(bool $isDetailView): void
    {
        $this->isDetailView = $isDetailView;
    }

    public function toArray($request): array
    {
        $data = [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'jenis' => $this->jenis,
            'nama' => $this->nama,
            'bulan' => $this->bulan,
            'tahun' => $this->tahun,
            'link' => $this->link,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => $this->whenLoaded('user'),
        ];

        $data['konten'] = $this->konten;

        return $data;
    }

    public function with($request): array
    {
        return [
            'user',
        ];
    }
}
