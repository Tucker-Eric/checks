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
        $val = $this->attributes[$param] ?? null;

        if (method_exists($this, $method = 'get'.studly_case($param).'Attribute')) {
            return call_user_func([$this, $method], $val);
        }

        return $val;
    }

    public function __set($param, $val)
    {
        $this->attributes[$param] = $val;
    }

    public function __isset($param)
    {
        return array_key_exists($param, $this->attributes);
    }

    public function getAccountInfoAttribute()
    {
        return implode('', [
            'o',
            str_pad($this->check_number, 10, '0', STR_PAD_LEFT),
            'o',
            ' ',
            't',
            $this->routing_number,
            't',
            ' ',
            $this->account_number,
            'o'
        ]);
    }

    public function getCheckStubAttribute()
    {
        return implode("\n", [
            'To: '.$this->pay_to,
            'Amount: $'.number_format($this->amount, 2),
            'Merchant Transaction Id: '.$this->transaction_id,
            'Memo: '.$this->memo
        ]);
    }

}
