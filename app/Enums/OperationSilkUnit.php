<?php

declare(strict_types=1);

namespace App\Enums;

enum OperationSilkUnit: string
{
    case Kilo = 'kilo';
    case Piece = 'piece';
    case Meter = 'meter';
    case Carton = 'carton';
    case Sheet = 'sheet';

    public function label(): string
    {
        return match ($this) {
            self::Kilo => __('dobs.operation_silk_unit_kilo'),
            self::Piece => __('dobs.operation_silk_unit_piece'),
            self::Meter => __('dobs.operation_silk_unit_meter'),
            self::Carton => __('dobs.operation_silk_unit_carton'),
            self::Sheet => __('dobs.operation_silk_unit_sheet'),
        };
    }

    /**
     * @return list<self>
     */
    public static function casesForSelect(): array
    {
        return [
            self::Kilo,
            self::Piece,
            self::Meter,
            self::Carton,
            self::Sheet,
        ];
    }
}
