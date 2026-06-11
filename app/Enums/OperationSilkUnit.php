<?php

declare(strict_types=1);

namespace App\Enums;

enum OperationSilkUnit: string
{
    case Piece = 'piece';
    case Carton = 'carton';

    public function label(): string
    {
        return match ($this) {
            self::Piece => __('dobs.operation_silk_unit_piece'),
            self::Carton => __('dobs.operation_silk_unit_carton'),
        };
    }

    /**
     * @return list<self>
     */
    public static function casesForSelect(): array
    {
        return [self::Piece, self::Carton];
    }
}
