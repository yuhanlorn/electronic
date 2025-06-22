<?php

namespace App\Filament\Admin\Resources\SubscribePlanResource\Pages;

use App\Filament\Admin\Resources\SubscribePlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSubscribePlans extends ListRecords
{
    protected static string $resource = SubscribePlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
