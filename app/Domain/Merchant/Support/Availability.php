<?php

namespace App\Domain\Merchant\Support;

enum Availability: string
{
    case InStock = 'in_stock';
    case OutOfStock = 'out_of_stock';
    case Preorder = 'preorder';
    case Backorder = 'backorder';

    public function schemaUrl(): string
    {
        return match ($this) {
            self::InStock => 'https://schema.org/InStock',
            self::OutOfStock => 'https://schema.org/OutOfStock',
            self::Preorder => 'https://schema.org/PreOrder',
            self::Backorder => 'https://schema.org/BackOrder',
        };
    }
}
