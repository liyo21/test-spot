<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Url extends Model
{
    use HasFactory;

    protected $table = 'urls';
    protected $fillable = ['original_url', 'shortened_url'];

    public static function shortenUrl($originalUrl)
    {
        Log::info("[UrlModel][shortenUrl][ ".$originalUrl." ] Inicia el método de almacenado");

        $link = self::create([
            'original_url'  => $originalUrl,
            'shortened_url' => Str::random(8)
        ]);

        Log::info("[UrlModel][shortenUrl][ ".$link->shortened_url." ] Almacenada correctamente");

        return $link->shortened_url;
    }
}
