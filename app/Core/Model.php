<?php

declare(strict_types=1);

namespace App\Core;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use PDO;

class Model extends EloquentModel
{
    /**
     * Mass assignable attributes. Empty guarded allows all attributes by default.
     */
    protected $guarded = [];

    /**
     * Default timestamps.
     */
    public $timestamps = true;

    /**
     * Get the table name associated with the model.
     * Supports both $table property and legacy static table() method.
     */
    public function getTable()
    {
        if (isset($this->table)) {
            return $this->table;
        }

        if (method_exists(static::class, 'table')) {
            return static::table();
        }

        return parent::getTable();
    }

    /**
     * Get the direct PDO connection for raw/complex SQL queries when desired.
     */
    public static function db(): PDO
    {
        return Database::getConnection();
    }

    /**
     * Dynamic static calls proxy to support legacy Model::update($id, $data)
     * and Model::delete($id) signatures alongside standard Eloquent builder calls.
     */
    public static function __callStatic($method, $parameters)
    {
        if ($method === 'update' && count($parameters) === 2 && (is_int($parameters[0]) || is_string($parameters[0])) && is_array($parameters[1])) {
            return (bool) static::query()->where((new static)->getKeyName(), $parameters[0])->update($parameters[1]);
        }

        if ($method === 'delete' && count($parameters) === 1 && (is_int($parameters[0]) || is_string($parameters[0]))) {
            return (bool) static::query()->where((new static)->getKeyName(), $parameters[0])->delete();
        }

        return parent::__callStatic($method, $parameters);
    }
}
