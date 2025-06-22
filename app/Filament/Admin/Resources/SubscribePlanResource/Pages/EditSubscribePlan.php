<?php

namespace App\Filament\Admin\Resources\SubscribePlanResource\Pages;

use App\Filament\Admin\Resources\SubscribePlanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubscribePlan extends EditRecord
{
    protected static string $resource = SubscribePlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
