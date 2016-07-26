<?php

namespace Ibtikar\ShareEconomyUMSBundle\Entity;

use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="Ibtikar\ShareEconomyUMSBundle\Repository\UserRepository")
 * @UniqueEntity(fields={"email"}, groups={"signup", "edit", "email"})
 */
class User implements AdvancedUserInterface, EquatableInterface
{

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     *
     * @Assert\NotBlank
     * @Assert\Email(strict=true)
     */
    private $email;

    /**
     * @var string $oldPassword
     *
     * @Assert\NotBlank(groups={"oldPassword"})
     * @SecurityAssert\UserPassword(groups={"oldPassword"})
     */
    private $oldPassword;

    /**
     * @var string $userPassword
     *
     * @Assert\NotBlank(groups={"signup", "password"})
     */
    private $userPassword;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="salt", type="string", length=32)
     */
    private $salt;

    /**
     * @var array
     *
     * @ORM\Column(name="roles", type="simple_array", nullable=true)
     */
    private $roles;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="emailVerified", type="boolean")
     */
    private $emailVerified = false;

    /**
     * @var string
     *
     * @ORM\Column(name="emailVerificationToken", type="string", length=32, nullable=true)
     */
    private $emailVerificationToken;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="emailVerificationTokenExpiryTime", type="datetime", nullable=true)
     */
    private $emailVerificationTokenExpiryTime;

    /**
     * @var string
     *
     * @ORM\Column(name="changePasswordToken", type="string", length=32, nullable=true)
     */
    private $changePasswordToken;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="changePasswordTokenExpiryTime", type="datetime", nullable=true)
     */
    private $changePasswordTokenExpiryTime;

    /**
     * @var string
     *
     * @ORM\Column(name="fullName", type="string", length=255)
     *
     * @Assert\NotBlank
     * @Assert\Length(min = 4, max = 25)
     */
    private $fullName;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255)
     *
     * @Assert\NotBlank
     */
    private $phone;

    /**
     * @var bool
     *
     * @ORM\Column(name="systemUser", type="boolean")
     */
    private $systemUser;

    /**
     * @var \DateTime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @var \DateTime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updated;

    public function __construct()
    {
        $this->salt = md5(uniqid(rand()));
    }

    public function __toString()
    {
        return "$this->firstName $this->lastName";
    }

    public function __sleep()
    {
        $classVars = get_object_vars($this);
        // unset all object proxies not the collections
//        unset($classVars['city']);
        return array_keys($classVars);
    }

    /**
     * this function will set the valid password for the user
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function setValidPassword()
    {
        //check if we have a password
        if ($this->getUserPassword()) {
            //hash the password
            $this->setPassword($this->hashPassword($this->getUserPassword()));
        } else {
            //check if the object is new
            if ($this->getId() === NULL) {
                //new object set a random password
                $this->setRandomPassword();
                //hash the password
                $this->setPassword($this->hashPassword($this->getUserPassword()));
            }
        }
    }

    /**
     * this function will hash a password and return the hashed value
     * the encoding has to be the same as the one in the project security.yml file
     * @param string $password the password to return it is hash
     */
    private function hashPassword($password)
    {
        //create an encoder object
        $encoder = new MessageDigestPasswordEncoder('sha512', true, 10);
        //return the hashed password
        return $encoder->encodePassword($password, $this->getSalt());
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set roles
     *
     * @param array $roles
     *
     * @return User
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get roles
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return User
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return bool
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set emailVerified
     *
     * @param boolean $emailVerified
     *
     * @return User
     */
    public function setEmailVerified($emailVerified)
    {
        $this->emailVerified = $emailVerified;

        return $this;
    }

    /**
     * Get emailVerified
     *
     * @return bool
     */
    public function getEmailVerified()
    {
        return $this->emailVerified;
    }

    /**
     * Set emailVerificationToken
     *
     * @param string $emailVerificationToken
     *
     * @return User
     */
    public function setEmailVerificationToken($emailVerificationToken)
    {
        $this->emailVerificationToken = $emailVerificationToken;

        return $this;
    }

    /**
     * Get emailVerificationToken
     *
     * @return string
     */
    public function getEmailVerificationToken()
    {
        return $this->emailVerificationToken;
    }

    /**
     * Set emailVerificationTokenExpiryTime
     *
     * @param \DateTime $emailVerificationTokenExpiryTime
     *
     * @return User
     */
    public function setEmailVerificationTokenExpiryTime($emailVerificationTokenExpiryTime)
    {
        $this->emailVerificationTokenExpiryTime = $emailVerificationTokenExpiryTime;

        return $this;
    }

    /**
     * Get emailVerificationTokenExpiryTime
     *
     * @return \DateTime
     */
    public function getEmailVerificationTokenExpiryTime()
    {
        return $this->emailVerificationTokenExpiryTime;
    }

    /**
     * Set changePasswordToken
     *
     * @param string $changePasswordToken
     *
     * @return User
     */
    public function setChangePasswordToken($changePasswordToken)
    {
        $this->changePasswordToken = $changePasswordToken;

        return $this;
    }

    /**
     * Get changePasswordToken
     *
     * @return string
     */
    public function getChangePasswordToken()
    {
        return $this->changePasswordToken;
    }

    /**
     * Set changePasswordTokenExpiryTime
     *
     * @param \DateTime $changePasswordTokenExpiryTime
     *
     * @return User
     */
    public function setChangePasswordTokenExpiryTime($changePasswordTokenExpiryTime)
    {
        $this->changePasswordTokenExpiryTime = $changePasswordTokenExpiryTime;

        return $this;
    }

    /**
     * Get changePasswordTokenExpiryTime
     *
     * @return \DateTime
     */
    public function getChangePasswordTokenExpiryTime()
    {
        return $this->changePasswordTokenExpiryTime;
    }

    /**
     * Set fullName
     *
     * @param string $fullName
     *
     * @return User
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * Get fullName
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return User
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set systemUser
     *
     * @param boolean $systemUser
     *
     * @return User
     */
    public function setSystemUser($systemUser)
    {
        $this->systemUser = $systemUser;

        return $this;
    }

    /**
     * Get systemUser
     *
     * @return bool
     */
    public function getSystemUser()
    {
        return $this->systemUser;
    }

    /**
     * Set salt
     *
     * @param string $salt
     *
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function getUsername()
    {
        return $this->email;
    }

    public function eraseCredentials()
    {
        $this->userPassword = null;
        $this->oldPassword = null;
    }

    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function isEqualTo(UserInterface $user)
    {
        if (!is_a($user, get_class($this))) {
            return false;
        }
        if ($this->enabled !== $user->getEnabled()) {
            return false;
        }
        if ($this->id !== $user->getId()) {
            return false;
        }
        return true;
    }

    public function getOldPassword()
    {
        return $this->oldPassword;
    }

    public function setOldPassword($oldPassword)
    {
        $this->oldPassword = $oldPassword;
        return $this;
    }

    public function getUserPassword()
    {
        return $this->userPassword;
    }

    public function setUserPassword($userPassword)
    {
        $this->userPassword = $userPassword;
        return $this;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return User
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return User
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }
}
