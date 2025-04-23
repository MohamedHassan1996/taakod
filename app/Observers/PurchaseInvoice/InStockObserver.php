<?php

namespace App\Observers\PurchaseInvoice;

use App\Models\Stock\ProductStock;

class InStockObserver
{
    public function deleting($model)
    {
        $productStockIds = $model->inStockItems()->pluck('product_stock_id')->toArray();

        ProductStock::whereIn('id', $productStockIds)->delete();

        $model->inStockItems()->delete();

    }
}
