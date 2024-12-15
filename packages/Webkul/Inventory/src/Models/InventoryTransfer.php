<?php

namespace Webkul\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Webkul\Inventory\Contracts\InventoryTransfer as InventoryTransferContract;
use Webkul\Product\Models\ProductProxy;

class InventoryTransfer extends Model implements InventoryTransferContract
{
    use HasFactory;

    protected $fillable = ['product_id', 'from_inventory_id', 'to_inventory_id', 'quantity'];

    /**
     * Get the product that owns the attribute value.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(ProductProxy::modelClass());
    }
}
