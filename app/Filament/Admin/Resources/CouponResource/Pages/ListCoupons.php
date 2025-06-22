<?php

namespace App\Filament\Admin\Resources\CouponResource\Pages;

use App\Filament\Admin\Resources\CouponResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ListCoupons extends ManageRecords
{
    protected static string $resource = CouponResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
