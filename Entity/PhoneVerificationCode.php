<?php

namespace Ibtikar\ShareEconomyUMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PhoneVerificationCode
 *
 * @ORM\Table(name="phone_verification_code", indexes={@ORM\Index(name="created_at", columns={"created_at"})})
 * @ORM\Entity(repositoryClass="Ibtikar\ShareEconomyUMSBundle\Repository\PhoneVerificationCodeRepository")
 */
class PhoneVerificationCode
{

    use \Ibtikar\ShareEconomyToolsBundle\Entity\TrackableTrait;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=20, nullable=false)
     */
    private $code;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_verified", type="boolean", options={"default": false})
     */
    private $isVerified = false;

    public function __construct()
    {
        if (is_null($this->id)) {
            $this->setCode(mt_rand(1000, 9999));
        }
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return PhoneVerificationCode
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set isVerified
     *
     * @param boolean $isVerified
     *
     * @return PhoneVerificationCode
     */
    public function setIsVerified($isVerified)
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * Get isVerified
     *
     * @return boolean
     */
    public function getIsVerified()
    {
        return $this->isVerified;
    }
}
