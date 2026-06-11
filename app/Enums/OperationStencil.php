<?php

declare(strict_types=1);

namespace App\Enums;

enum OperationStencil: string
{
    case New = 'new';
    case Repeat = 'repeat';

    public function label(): string
    {
        return match ($this) {
            self::New => __('dobs.operation_stencil_new'),
            self::Repeat => __('dobs.operation_stencil_repeat'),
        };
    }

    /**
     * @return list<self>
     */
    public static function casesForSelect(): array
    {
        return [self::New, self::Repeat];
    }
}
