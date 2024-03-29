<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use ZiffMedia\LaravelEloquentImagery\Eloquent\EloquentImageCast;
use ZiffMedia\LaravelEloquentImagery\Eloquent\HasEloquentImagery;

class SingleImageExample extends Model
{
    use HasEloquentImagery;

    protected $casts = [
        'variations' => 'json',
        'image' => EloquentImageCast::class . ':single-image-examples/{id}.{extension}'
            . ',thumbnail=fit_resize|size_50x50|v'
    ];
}
