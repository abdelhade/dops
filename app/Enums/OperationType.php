<?php

declare(strict_types=1);

namespace App\Enums;

enum OperationType: string
{
    case Offset = 'offset';
    case SilkScreen = 'silk_screen';

    public function label(): string
    {
        return match ($this) {
            self::Offset => __('dobs.operation_type_offset'),
            self::SilkScreen => __('dobs.operation_type_silk_screen'),
        };
    }

    public function serialPrefix(): string
    {
        return match ($this) {
            self::Offset => 'OFF',
            self::SilkScreen => 'SS',
        };
    }

    /**
     * @return list<self>
     */
    public static function casesForSelect(): array
    {
        return [self::Offset, self::SilkScreen];
    }
}
