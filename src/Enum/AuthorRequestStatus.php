<?php

namespace App\Enum;

enum AuthorRequestStatus: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'En attente',
            self::ACCEPTED => 'Acceptée',
            self::REJECTED => 'Refusée',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::PENDING => 'bg-warning text-dark',
            self::ACCEPTED => 'bg-success',
            self::REJECTED => 'bg-danger',
        };
    }
}
