<?php

namespace Picqer\Carriers\SendCloud;

abstract class Model
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var array The model's attributes
     */
    protected $attributes = [];

    /**
     * @var array The model's fillable attributes
     */
    protected $fillable = [];

    /**
     * @var string The URL endpoint of this model
     */
    protected $url = '';

    /**
     * @var string Name of the primary key for this model
     */
    protected $primaryKey = 'id';

    /**
     * @var array Defines the single and plural names for this model as used in API
     */
    protected $namespaces = [
        'singular' => '',
        'plural' => ''
    ];

    /**
     * Model constructor.
     * @param Connection $connection
     * @param array $attributes
     */
    public function __construct(Connection $connection, array $attributes = [])
    {
        $this->connection = $connection;
        $this->fill($attributes);
    }

    /**
     * Get the connection instance
     *
     * @return Connection
     */
    public function connection()
    {
        return $this->connection;
    }

    /**
     * Get the model's attributes
     *
     * @return array
     */
    public function attributes()
    {
        return $this->attributes;
    }

    /**
     * Fill the entity from an array
     *
     * @param array $attributes
     */
    protected function fill(array $attributes)
    {
        if (array_key_exists($this->namespaces['singular'], $attributes)) {
            $attributes = $attributes[$this->namespaces['singular']];
        }

        foreach ($this->fillableFromArray($attributes) as $key => $value) {
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            }
        }
    }

    /**
     * Get the fillable attributes of an array
     *
     * @param array $attributes
     * @return array
     */
    protected function fillableFromArray(array $attributes)
    {
        if (count($this->fillable) > 0) {
            return array_intersect_key($attributes, array_flip($this->fillable));
        }

        return $attributes;
    }

    /**
     * @param $key
     * @return bool
     */
    protected function isFillable($key)
    {
        return in_array($key, $this->fillable);
    }

    /**
     * @param $key
     * @param $value
     */
    protected function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function __get($key)
    {
        if (isset($this->attributes[$key])) {
            return $this->attributes[$key];
        }

        return null;
    }

    /**
     * @param $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * @param $key
     * @param $value
     */
    public function __set($key, $value)
    {
        if ($this->isFillable($key)) {
            $this->setAttribute($key, $value);
        }
    }

    /**
     * @return bool
     */
    public function exists()
    {
        if ( ! in_array($this->primaryKey, $this->attributes)) return false;

        return ! empty($this->attributes[$this->primaryKey]);
    }

    /**
     * Returns the JSON representation of a value
     * @link https://php.net/manual/en/function.json-encode.php
     * @param int $options [optional] Bitmask consisting of JSON constants
     * @param int $depth   [optional] Set the maximum depth. Must be greater than zero.
     * @return string|false a JSON encoded string on success or FALSE on failure.
     */
    public function json($options = 0, $depth = 512)
    {
        $json = [
            $this->namespaces['singular'] => $this->attributes
        ];
        return json_encode($json, $options, $depth);
    }

    /**
     * Make var_dump and print_r look pretty
     *
     * @return array
     */
    public function __debugInfo()
    {
        $result = [];
        foreach ($this->fillable as $attribute) {
            $result[$attribute] = $this->$attribute;
        }
        return $result;
    }
}
