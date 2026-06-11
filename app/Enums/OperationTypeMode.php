<?php

declare(strict_types=1);

namespace App\Enums;

enum OperationTypeMode: string
{
    case Offset = 'offset';
    case SilkScreen = 'silk_screen';
    case General = 'general';

    public function label(): string
    {
        return match ($this) {
            self::Offset => __('dobs.operation_type_mode_offset'),
            self::SilkScreen => __('dobs.operation_type_mode_silk_screen'),
            self::General => __('dobs.operation_type_mode_general'),
        };
    }

    /**
     * @return list<self>
     */
    public static function casesForSelect(): array
    {
        return [self::Offset, self::SilkScreen, self::General];
    }
}
