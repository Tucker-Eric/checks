<?php

namespace CheckWriter;

class Check
{
    /**
     * @var array
     */
    protected $attributes;

    public function __construct($attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function __get($param)
    {
        return $this->attributes[$param] ?? null;
    }

    public function __set($param, $val)
    {
        $this->attributes[$param] = $val;
    }

    public function __isset($param)
    {
        return array_key_exists($param, $this->attributes);
    }



}
