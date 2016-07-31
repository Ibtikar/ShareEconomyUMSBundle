<?php

namespace Ibtikar\ShareEconomyUMSBundle\APIResponse;

use Symfony\Component\Validator\Constraints as Assert;

class RemainingTime {

    /**
     * @Assert\NotBlank
     */
    public $seconds = 0;

}
