<?php

namespace App\Models\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as Auditing;
use phpseclib3\Math\BigInteger;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property BigInteger id
 * @property string name
 * @property string description
 * @property string extension
 * @property string directory
 */
class Image extends Model implements Auditable
{
    use HasFactory;
    use Auditing;
    use SoftDeletes;

    protected static $instance;

    protected $connection = 'pgsql-app';
    protected $table = 'app.images';

    protected $fillable = [
        'name',
        'description',
        'extension',
        'directory',
    ];

    protected $hidden = ['imageable_type'];

    protected $appends = ['full_name', 'full_path'];

    // Instance
    public static function getInstance($id)
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }
        static::$instance->id = $id;
        return static::$instance;
    }

    // Relationships
    public function imageable()
    {
        return $this->morphTo();
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return "{$this->attributes['id']}.{$this->attributes['extension']}";
    }

    public function getFullPathAttribute()
    {
        return "files/{$this->attributes['id']}.{$this->attributes['extension']}";
    }
}
