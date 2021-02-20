<?php
namespace WordLand;

use WordLand\Abstracts\Data;

class Agent extends Data
{
    public $name;
    public $phoneNumber;

    public function __construct($name, $phoneNumber = null)
    {
        $this->name = $name;

        if (!is_null($phoneNumber)) {
            $this->setPhoneNumber($phoneNumber);
        }
    }

    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }
}
