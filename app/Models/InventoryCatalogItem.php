<?php

namespace App\Models;

use Database\Factories\InventoryCatalogItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name'])]
class InventoryCatalogItem extends Model
{
    /** @use HasFactory<InventoryCatalogItemFactory> */
    use HasFactory;
}
