<?php

namespace Ibtikar\ShareEconomyUMSBundle\APIResponse;

use Symfony\Component\Validator\Constraints as Assert;

class InvalidCredentials
{

    /**
     * @Assert\Type(type="boolean")
     */
    public $status = false;

    /**
     * @Assert\Type(type="integer")
     */
    public $code = 401;

    /**
     * @Assert\Type(type="string")
     */
    public $message = 'Invalid credentials';

}
