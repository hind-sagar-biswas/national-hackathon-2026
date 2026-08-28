<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'severity' => $this->type,
            'type' => $this->type,
            'title' => $this->data['title'] ?? 'Notification',
            'message' => $this->data['message'] ?? '',
            'notification_type' => $this->data['type'] ?? 'info',
            'icon' => $this->data['icon'] ?? 'bell',
            'action_url' => $this->data['action_url'] ?? null,
            'action_text' => $this->data['action_text'] ?? 'View',
            'read_at' => $this->read_at ? $this->read_at->diffForHumans() : null,
            'is_read' => ! is_null($this->read_at),
            'created_at' => $this->created_at->diffForHumans(),
        ];
    }
}
