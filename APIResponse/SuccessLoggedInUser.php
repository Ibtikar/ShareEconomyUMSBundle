<?php

namespace Ibtikar\ShareEconomyUMSBundle\APIResponse;

use Ibtikar\ShareEconomyToolsBundle\APIResponse\Success as ToolsSuccessResponse;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
class SuccessLoggedInUser extends ToolsSuccessResponse
{

    /**
     * @Assert\Type(type="LoggedInUser")
     */
    public $user;

}
