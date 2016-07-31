<?php

namespace Ibtikar\ShareEconomyUMSBundle\APIResponse;

use Symfony\Component\Validator\Constraints as Assert;

class RegisterUserSuccess extends Success {

    /**
     * @var Ibtikar\ShareEconomyUMSBundle\APIResponse\User
     *
     * @Assert\NotBlank
     */
    public $user;

}
