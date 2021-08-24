<?php

namespace App\Models\Authentication;

// Laravel
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as Auditing;

// Application
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\App\Catalogue;
use App\Models\App\Image;

class Route extends Model implements Auditable
{
    use HasFactory;
    use Auditing;
    use SoftDeletes;


    protected $connection = 'pgsql-authentication';
    protected $table = 'authentication.routes';

    protected static $instance;

    protected $fillable = [
        'uri',
        'name',
        'description',
        'icon',
        'order',
        'logo',
    ];

    
    public static function getInstance($id)
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }
        static::$instance->id = $id;
        return static::$instance;
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function type()
    {
        return $this->belongsTo(Catalogue::class);
    }

    public function status()
    {
        return $this->belongsTo(Catalogue::class);
    }

    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }

    public function children()
    {
        return $this->hasMany(Route::class, 'parent_id');
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }
}
