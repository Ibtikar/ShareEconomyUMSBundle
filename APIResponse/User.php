<?php

namespace Ibtikar\ShareEconomyUMSBundle\APIResponse;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
class User
{

    /**
     * @Assert\Type(type="string")
     */
    public $email;

    /**
     * @Assert\Type(type="string")
     */
    public $fullName;

}
