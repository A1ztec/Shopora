<?php

namespace App\Http\Resources\Api\User;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar' => $this->avatar ? Storage::disk('s3')->url($this->avatar) : null,
            'status' => $this->status->value,
            'status_title' => $this->status->title(),
            'email_verified_at' => $this->email_verified_at,
        ];
    }
}
