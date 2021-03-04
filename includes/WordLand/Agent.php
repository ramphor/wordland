<?php
namespace WordLand;

use WordLand\Abstracts\Data;

class Agent extends Data
{
    protected $user_id = 0;

    protected $name;
    protected $phoneNumber;
    protected $email;

    public function __construct($name = null, $phoneNumber = null, $email = null)
    {
        if (!is_null($name)) {
            $this->setName($name);
        }
        if (!is_null($phoneNumber)) {
            $this->setPhoneNumber($phoneNumber);
        }
        if (!is_null($email)) {
            $this->setEmail($email);
        }
    }

    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function createFakeEmail()
    {
    }

    public function save()
    {
    }
}
