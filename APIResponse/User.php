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
    public $id;

    /**
     * @Assert\Type(type="string")
     */
    public $email;

    /**
     * @Assert\Type(type="string")
     */
    public $fullName;

    /**
     * @Assert\Type(type="string")
     */
    public $phone;

    /**
     * @Assert\Type(type="string")
     */
    public $image;

    /**
     * @Assert\Type(type="bool")
     */
    public $emailVerified;

    /**
     * @Assert\Type(type="bool")
     */
    public $isPhoneVerified;

    /**
     * @Assert\NotBlank
     */
    public $token;

}
