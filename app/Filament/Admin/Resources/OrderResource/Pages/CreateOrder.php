<?php

namespace App\Filament\Admin\Resources\OrderResource\Pages;

use App\Filament\Admin\Resources\OrderResource;
use App\Models\OrderLog;
use App\Models\Product;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    public function afterCreate()
    {
        $record = $this->getRecord();
        $data = $this->form->getState();

        $vat = 0;
        $discount = 0;
        $total = 0;
        foreach ($data['items'] as $item) {
            $getProduct = Product::find($item['product_id']);
            $getDiscount = 0;
            if ($getProduct->discount_to && Carbon::parse($getProduct->discount_to)->isFuture()) {
                $getDiscount = $getProduct->discount;
            }

            $record->ordersItems()->create([
                'account_id' => $record->account_id,
                'product_id' => $item['product_id'],
                'qty' => $item['qty'],
                'price' => $getProduct->price,
                'vat' => $getProduct->vat,
                'discount' => $getDiscount,
                'total' => ((($getProduct->price + $getProduct->vat) - $getDiscount) * $item['qty']),
            ]);

            $vat += $getProduct->vat;
            $discount += $getDiscount;
            $total += ((($getProduct->price + $getProduct->vat) - $getDiscount) * $item['qty']);
        }

        $record->user_id = auth()->user()->id;
        $record->vat = $vat;
        $record->discount = $discount;
        $record->shipping = setting('ordering_active_shipping_fees') ? (int) setting('ordering_shipping_fees') : 0;
        $record->total = $total + $record->shipping;
        $record->save();

        $orderLog = new OrderLog;
        $orderLog->user_id = auth()->user()->id;
        $orderLog->order_id = $record->id;
        $orderLog->status = $record->status;
        $orderLog->is_closed = 1;
        $orderLog->note = 'Order created by '.auth()->user()->name.' and Total: '.number_format($record->total, 2);
        $orderLog->save();
    }
}
