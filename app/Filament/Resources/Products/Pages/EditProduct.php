<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\Concerns\HandlesSimpleProductPricing;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    use HandlesSimpleProductPricing;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    /** Prefill the simple Price + Stock fields from the existing default variant. */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Product $product */
        $product = $this->getRecord();

        // Variant products don't use the simple fields (they're hidden); skip.
        if ($product->variants()->where('is_active', true)->count() > 1) {
            return $data;
        }

        $variant = $product->variants()
            ->orderByDesc('is_default')
            ->orderBy('position')
            ->first();

        if ($variant !== null) {
            // Money object — MoneyInput's formatStateUsing renders it as rupees.
            $data['simple_price'] = $variant->price_amount;
            $data['simple_stock'] = (int) ($variant->inventory?->quantity_on_hand ?? 0);
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->extractSimpleProductPricing($data);
    }

    protected function afterSave(): void
    {
        $this->syncSimpleProductPricing();
    }
}
