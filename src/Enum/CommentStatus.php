<?php

namespace App\Enum;

enum CommentStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public function getLabel(): string
    {
        return match($this) {
            self::PENDING => 'En attente',
            self::APPROVED => 'Approuvé',
            self::REJECTED => 'Rejeté',
        };
    }

    public function getBadgeClass(): string
    {
        return match($this) {
            self::PENDING => 'bg-warning',
            self::APPROVED => 'bg-success',
            self::REJECTED => 'bg-danger',
        };
    }
}
