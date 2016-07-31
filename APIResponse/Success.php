<?php

namespace Ibtikar\ShareEconomyUMSBundle\APIResponse;

use Symfony\Component\Validator\Constraints as Assert;

class Success {

    /**
     * @Assert\NotBlank
     */
    public $status = true;

    /**
     * @Assert\NotBlank
     */
    public $code = 200;

}
