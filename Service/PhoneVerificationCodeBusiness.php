<?php

namespace Ibtikar\ShareEconomyUMSBundle\Service;

use Ibtikar\ShareEconomyUMSBundle\Entity\PhoneVerificationCode;

/**
 * @author Karim Shendy <kareem.elshendy@ibtikar.net.sa>
 */
class PhoneVerificationCodeBusiness
{
    private $codeExpiryMinutes;

    /**
     *
     * @param type $codeExpiryMinutes
     */
    public function __construct($codeExpiryMinutes)
    {
        $this->codeExpiryMinutes = $codeExpiryMinutes;
    }

    /**
     * check code validity
     *
     * @author Karim Shendy <kareem.elshendy@ibtikar.net.sa>
     * @return boolean
     */
    public function isValidCode(PhoneVerificationCode $verificationCode)
    {
        $minCreationTime = new \DateTime('-' . $this->codeExpiryMinutes . ' minutes');

        return $minCreationTime < $verificationCode->getCreatedAt();
    }

    /**
     * get validity remaining seconds
     *
     * @author Karim Shendy <kareem.elshendy@ibtikar.net.sa>
     * @return integer
     */
    public function getValidityRemainingSeconds(PhoneVerificationCode $verificationCode)
    {
        $now  = new \DateTime();
        $diff = $now->format('U') - $verificationCode->getCreatedAt()->format('U');

        return $diff > ( $this->codeExpiryMinutes * 60 ) ? 0 : ( $this->codeExpiryMinutes * 60 ) - $diff;
    }
}