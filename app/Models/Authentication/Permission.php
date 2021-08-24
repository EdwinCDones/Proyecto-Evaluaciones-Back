<?php

namespace App\Models\Authentication;

// Laravel
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as Auditing;

// Application
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\App\Institution;

class Permission extends Model implements Auditable
{

    use HasFactory;
    use SoftDeletes;
    use HasFactory;
    use Auditing;
    use SoftDeletes;


    protected $connection = 'pgsql-authentication';
    protected $table = 'authentication.permissions';

    protected static $instance;
    

    protected $fillable = [
        'state',
        'actions'
    ];

    protected $casts = [
        'actions' => 'array',
    ];

    public static function getInstance($id)
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }
        static::$instance->id = $id;
        return static::$instance;
    }

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function system()
    {
        return $this->belongsTo(System::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function shortcut()
    {
        return $this->hasOne(Shortcut::class);
    }
}
