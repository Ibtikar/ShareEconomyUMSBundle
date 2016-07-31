<?php

namespace Ibtikar\ShareEconomyUMSBundle\APIResponse;

use Symfony\Component\Validator\Constraints as Assert;

class Fail
{

    /**
     * @Assert\NotBlank
     */
    public $status = false;

    /**
     * @Assert\NotBlank
     */
    public $code = 422;

    /**
     * @Assert\NotBlank
     */
    public $message = '';

}