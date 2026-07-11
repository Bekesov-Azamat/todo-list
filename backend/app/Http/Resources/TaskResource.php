<?php

namespace App\Http\Resources;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Task
 */
class TaskResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $authenticatedUser = $request->user();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'due_date' => $this->due_date?->toDateString(),
            'status' => $this->status->value,

            'owner' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],

            'permissions' => [
                'update' => $authenticatedUser instanceof User
                    && $authenticatedUser->can('update', $this->resource),

                'delete' => $authenticatedUser instanceof User
                    && $authenticatedUser->can('delete', $this->resource),
            ],

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
