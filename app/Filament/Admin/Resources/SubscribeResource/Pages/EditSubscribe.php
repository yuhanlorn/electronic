<?php

namespace App\Filament\Admin\Resources\SubscribeResource\Pages;

use App\Filament\Admin\Resources\SubscribeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubscribe extends EditRecord
{
    protected static string $resource = SubscribeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
