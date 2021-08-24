<?php

namespace App\Models\Authentication;

// Laravel
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as Auditing;

class TransactionalCode extends Model implements Auditable
{
    use HasFactory;
    use Auditing;

    protected $connection = 'pgsql-authentication';

    protected static $instance;

    protected $fillable = [
        'username',
        'is_valid',
        'token'];

    public static function getInstance($id)
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }
        static::$instance->id = $id;
        return static::$instance;
    }
}
