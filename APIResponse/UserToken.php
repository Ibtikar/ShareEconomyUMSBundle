<?php

namespace Ibtikar\ShareEconomyUMSBundle\APIResponse;

use Symfony\Component\Validator\Constraints as Assert;

class UserToken extends Success {

    /**
     * @Assert\NotBlank
     */
    public $token;

}
