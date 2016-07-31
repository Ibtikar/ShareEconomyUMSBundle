<?php

namespace Ibtikar\ShareEconomyUMSBundle\APIResponse;

use Symfony\Component\Validator\Constraints as Assert;

class RegisterUserFail extends Fail {

    /**
     * @var array
     *
     * @Assert\NotBlank
     */
    public $errors;

}
