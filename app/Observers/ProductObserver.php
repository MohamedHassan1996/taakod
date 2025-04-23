<?php

namespace App\Observers;

use App\Models\Product\Product;
use Illuminate\Support\Facades\Storage;

class ProductObserver
{
    /**
     * Handle the Category "deleting" event.
     * Deletes images before deleting subcategories.
     */
    public function deleting(Product $product)
    {
        // Delete the main category's image if it exists
        if ($product->productMedia()->exists()) {
            $productMedia = $product->productMedia()->get();
            foreach ($productMedia as $media) {
                Storage::disk('public')->delete($media->getRawOriginal('path'));
                $media->delete();
            }
        }

    }
}
