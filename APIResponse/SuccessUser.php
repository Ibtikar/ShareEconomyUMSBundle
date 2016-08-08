<?php

namespace Ibtikar\ShareEconomyUMSBundle\APIResponse;

use Ibtikar\ShareEconomyToolsBundle\APIResponse\Success;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
class SuccessUser extends Success
{

    /**
     * @Assert\Type(type="User")
     */
    public $user;

}
