<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryStockLog extends Model
{
    protected $fillable = ['inventory_item_id', 'admin_id', 'type', 'quantity', 'note'];

    public function inventoryItem() {
        return $this->belongsTo(InventoryItem::class);
    }

    public function admin() {
        return $this->belongsTo(Admin::class);
    }
}