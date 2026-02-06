<?php

namespace App\Enum;

enum CollaborationStatus: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';

    public function getLabel(): string
    {
        return match($this) {
            self::PENDING => 'En attente',
            self::ACCEPTED => 'Acceptée',
            self::REJECTED => 'Refusée',
        };
    }

    public function getBadgeClass(): string
    {
        return match($this) {
            self::PENDING => 'bg-warning text-dark',
            self::ACCEPTED => 'bg-success',
            self::REJECTED => 'bg-danger',
        };
    }
}
