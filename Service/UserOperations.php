<?php

namespace Ibtikar\ShareEconomyUMSBundle\Service;

use Ibtikar\ShareEconomyUMSBundle\APIResponse\User as ResponseUser;
use Ibtikar\ShareEconomyUMSBundle\Entity\User;
use Ibtikar\ShareEconomyToolsBundle\Service\APIOperations;

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
class UserOperations extends APIOperations
{

    /**
     * @param string $assetsDomain
     */
    public function __construct($assetsDomain)
    {
        parent::__construct($assetsDomain);
    }

    /**
     * @param User $user
     * @return array
     */
    public function getUserData(User $user)
    {
        $responseUser = new ResponseUser();
        $responseUser->id = $user->getId();
        $responseUser->fullName = $user->getFullName();
        $responseUser->email = $user->getEmail();
        $responseUser->phone = $user->getPhone();
        $responseUser->emailVerified = $user->getEmailVerified();
        $responseUser->isPhoneVerified = $user->getIsPhoneVerified();
        if ($user->getImage()) {
            $responseUser->image = $this->assetsDomain . '/' . $user->getWebPath();
        }
        return $this->getObjectDataAsArray($responseUser);
    }
}
