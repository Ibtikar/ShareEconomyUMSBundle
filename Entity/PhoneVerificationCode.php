<?php

namespace Ibtikar\ShareEconomyUMSBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * PhoneVerificationCode
 *
 * @ORM\Table(name="phone_verification_code", indexes={@ORM\Index(name="user_id", columns={"user_id"})})
 * @ORM\Entity(repositoryClass="Ibtikar\ShareEconomyUMSBundle\Repository\PhoneVerificationCodeRepository")
 */
class PhoneVerificationCode
{
    const CODE_EXPIRY_MINUTES = 5;

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
     * @ORM\Column(name="is_verified", type="boolean", nullable=false)
     */
    private $isVerified = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @var \Ibtikar\ShareEconomyUMSBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="Ibtikar\ShareEconomyUMSBundle\Entity\User", inversedBy="phoneVerificationCodes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $user;

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

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return PhoneVerificationCode
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set user
     *
     * @param \Ibtikar\ShareEconomyUMSBundle\Entity\User $user
     *
     * @return PhoneVerificationCode
     */
    public function setUser(\Ibtikar\ShareEconomyUMSBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Ibtikar\ShareEconomyUMSBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * generate random verification code
     *
     * @author Karim Shendy <kareem.elshendy@ibtikar.net.sa>
     * @return integer
     */
    public function generateCode()
    {
        $this->setCode(mt_rand(1000, 9999));
    }

    /**
     * check code validity
     *
     * @author Karim Shendy <kareem.elshendy@ibtikar.net.sa>
     * @return boolean
     */
    public function isValid()
    {
        $minCreationTime = new \DateTime('- ' . self::CODE_EXPIRY_MINUTES . ' minutes');

        return $minCreationTime < $this->getCreatedAt();
    }

    /**
     * get validity remaining seconds
     *
     * @author Karim Shendy <kareem.elshendy@ibtikar.net.sa>
     * @return integer
     */
    public function getValidityRemainingSeconds()
    {
        $now = new \DateTime();
        $diff = $now->format('U') - $this->getCreatedAt()->format('U');

        return $diff > ( self::CODE_EXPIRY_MINUTES * 60 ) ? 0 : ( self::CODE_EXPIRY_MINUTES * 60 ) - $diff ;
    }
}