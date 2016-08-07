<?php

namespace Ibtikar\ShareEconomyUMSBundle\APIResponse;

use Ibtikar\ShareEconomyToolsBundle\APIResponse\Success;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
class SuccessLoggedInUser extends Success
{

    /**
     * @Assert\Type(type="LoggedInUser")
     */
    public $user;

}
