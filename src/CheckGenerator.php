<?php

namespace CheckWriter;


class CheckGenerator
{

    protected $checks = [];

    protected $writer;

    public function __construct()
    {
        $this->writer = new Writer;
    }

    /**
     * @param array $check
     * @return Check
     */
    public function createCheck(array $check = [])
    {
        return new Check($check);
    }

    /**
     * @param $check
     * @return $this
     */
    public function addCheck($check)
    {
        $this->checks[] = $check instanceof Check ? $check : $this->createCheck($check);

        return $this;
    }

    public function printChecks()
    {
        $this->writer->printChecks($this->checks);
    }

}
