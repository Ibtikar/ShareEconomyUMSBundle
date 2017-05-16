<?php

namespace Ibtikar\ShareEconomyUMSBundle\Entity;

use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Ibtikar\ShareEconomyUMSBundle\Validator\Constraints\CustomEmail as AssertEmail;

/**
 * User
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity(fields={"email"}, groups={"signup", "edit", "email"}, message="email_exist")
 * @UniqueEntity(fields={"phone"}, groups={"signup", "edit", "phone"}, message="phone_exist", repositoryMethod="findPhoneByUniqueCriteria")
 */
class BaseUser implements AdvancedUserInterface, EquatableInterface
{

    use \Ibtikar\ShareEconomyToolsBundle\Entity\TrackableTrait;

    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_CUSTOMER = 'ROLE_CUSTOMER';
    const ROLE_SERVICE_PROVIDER = 'ROLE_SERVICE_PROVIDER';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=190)
     *
     * @Assert\NotBlank(message="fill_mandatory_field", groups={"signup", "edit"})
     * @AssertEmail(strict=true,checkMX=true, checkHost=true, message="invalid_email", groups={"signup", "edit"})
     */
    protected $email;

    /**
     * @var string $oldPassword
     *
     * @Assert\NotBlank(groups={"changePassword"})
     * @SecurityAssert\UserPassword(groups={"changePassword"})
     */
    protected $oldPassword;

    /**
     * @var string $userPassword
     *
     * @Assert\NotBlank(groups={"signup", "changePassword", "resetPassword"}, message="fill_mandatory_field")
     * @Assert\Length(min = 6, max = 12, groups={"signup", "changePassword", "resetPassword"}, maxMessage="password_not_valid_max", minMessage="password_not_valid_min")
     * @Assert\Regex(pattern="/[a-zA-Zأ-ي]/", message="password_not_valid_no_number", groups={"signup", "changePassword", "resetPassword"})
     * @Assert\Regex(pattern="/[0-9٠-٩]/", message="password_not_valid_no_text", groups={"signup", "changePassword", "resetPassword"})
     */
    protected $userPassword;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=190)
     *
     * @Assert\NotBlank
     */
    protected $password;

    /**
     * @var string
     *
     * @ORM\Column(name="salt", type="string", length=32)
     */
    protected $salt;

    /**
     * @var array
     *
     * @ORM\Column(name="roles", type="simple_array", nullable=true)
     */
    protected $roles;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean", options={"default": true})
     */
    protected $enabled = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="emailVerified", type="boolean", options={"default": false})
     */
    protected $emailVerified = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="isPhoneVerified", type="boolean", options={"default": false})
     */
    protected $isPhoneVerified = false;

    /**
     * @var string
     *
     * @ORM\Column(name="emailVerificationToken", type="string", length=100, nullable=true)
     */
    protected $emailVerificationToken;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="emailVerificationTokenExpiryTime", type="datetime", nullable=true)
     */
    protected $emailVerificationTokenExpiryTime;

    /**
     * @var string
     *
     * @ORM\Column(name="changePasswordToken", type="string", length=100, nullable=true)
     */
    protected $changePasswordToken;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="changePasswordTokenExpiryTime", type="datetime", nullable=true)
     */
    protected $changePasswordTokenExpiryTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lastForgetPasswordRequestDate", type="datetime", nullable=true)
     */
    protected $lastForgetPasswordRequestDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="forgetPasswordRequests", type="smallint", nullable=true)
     */
    protected $forgetPasswordRequests = 0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lastEmailVerificationRequestDate", type="datetime", nullable=true)
     */
    protected $lastEmailVerificationRequestDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="verificationEmailRequests", type="smallint", nullable=true)
     */
    protected $verificationEmailRequests = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="fullName", type="string", length=190)
     *
     * @Assert\NotBlank(message="fill_mandatory_field", groups={"signup", "edit", "joinrequest"})
     * @Assert\Length(min = 4, max = 25, groups={"signup", "edit", "joinrequest"}, maxMessage="fullname_length_not_valid", minMessage="fullname_length_not_valid")
     */
    protected $fullName;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=190)
     *
     * @Assert\NotBlank(message="fill_mandatory_field", groups={"signup", "phone", "edit", "joinrequest"})
     * @Assert\Regex("/^[+-]?\d+$/", groups={"edit"})
     */
    protected $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string", length=2, nullable=true, options={"fixed": true})
     */
    protected $locale;

    /**
     * @var bool
     *
     * @ORM\Column(name="systemUser", type="boolean", options={"default": false})
     */
    protected $systemUser = false;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Ibtikar\ShareEconomyUMSBundle\Entity\PhoneVerificationCode", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"id"="DESC"})
     * @ORM\JoinTable(name="users_verification_codes",
     *  joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")},
     *  inverseJoinColumns={@ORM\JoinColumn(name="phone_verification_code_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     *
     * @Assert\Valid
     */
    protected $phoneVerificationCodes;

    /**
     * @var string $image
     *
     * @ORM\Column(name="image", type="string", length=20, nullable=true)
     */
    protected $image;

    /**
     * a temp variable for storing the old image name to delete the old image after the update
     * @var string $temp
     */
    protected $temp;

    /**
     * @var UploadedFile $file
     *
     * @Assert\NotBlank(groups={"image-required"})
     * @Assert\Image(minWidth=300, minHeight=300, mimeTypes={"image/jpg", "image/jpeg", "image/pjpeg", "image/png"}, groups={"image", "Default", "edit"})
     * @Assert\Image(maxSize="1M", groups={"joinrequest", "edit"})
     */
    protected $file;

    public function __construct()
    {
        $this->salt = md5(uniqid(rand()));
        $this->phoneVerificationCodes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString()
    {
        return "$this->fullName";
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
     * Set image
     *
     * @param string $image
     * @return $this
     */
    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set file
     *
     * @param UploadedFile $file
     * @return $this
     */
    public function setFile($file)
    {
        $this->file = $file;
        //check if we have an old image
        if ($this->image) {
            //store the old name to delete on the update
            $this->temp = $this->image;
            $this->image = NULL;
        } else {
            $this->image = 'initial';
        }
        return $this;
    }

    /**
     * Get file
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * this function is used to delete the current image
     * the deleting of the current object will also delete the image and you do not need to call this function
     * if you call this function before you remove the object the image will not be removed
     */
    public function removeImage()
    {
        //check if we have an old image
        if ($this->image) {
            //store the old name to delete on the update
            $this->temp = $this->image;
            //delete the current image
            $this->image = NULL;
        }
    }

    /**
     * create the the directory if not found
     * @param string $directoryPath
     * @throws \Exception if the directory can not be created
     */
    private function createDirectory($directoryPath)
    {
        if (!@is_dir($directoryPath)) {
            $oldumask = umask(0);
            $success = @mkdir($directoryPath, 0755, TRUE);
            umask($oldumask);
            if (!$success) {
                throw new \Exception("Can not create the directory $directoryPath");
            }
        }
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (NULL !== $this->file && (NULL === $this->image || 'initial' === $this->image)) {
            //get the image extension
            $extension = $this->file->guessExtension();
            //generate a random image name
            $img = uniqid();
            //get the image upload directory
            $uploadDir = $this->getUploadRootDir();
            $this->createDirectory($uploadDir);
            //check that the file name does not exist
            while (@file_exists("$uploadDir/$img.$extension")) {
                //try to find a new unique name
                $img = uniqid();
            }
            //set the image new name
            $this->image = "$img.$extension";
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if (NULL !== $this->file) {
            // you must throw an exception here if the file cannot be moved
            // so that the entity is not persisted to the database
            // which the UploadedFile move() method does
            $this->file->move($this->getUploadRootDir(), $this->image);
            //remove the file as you do not need it any more
            $this->file = NULL;
        }
        //check if we have an old image
        if ($this->temp) {
            //try to delete the old image
            @unlink($this->getUploadRootDir() . '/' . $this->temp);
            //clear the temp image
            $this->temp = NULL;
        }
    }

    /**
     * @ORM\PostRemove()
     */
    public function postRemove()
    {
        //check if we have an image
        if ($this->image) {
            //try to delete the image
            @unlink($this->getAbsolutePath());
        }
    }

    /**
     * @return string the path of image starting of root
     */
    public function getAbsolutePath()
    {
        return $this->getUploadRootDir() . '/' . $this->image;
    }

    /**
     * @return string the relative path of image starting from web directory
     */
    public function getWebPath()
    {
        return NULL === $this->image ? NULL : $this->getUploadDir() . '/' . $this->image;
    }

    /**
     * @return string the path of upload directory starting of root
     */
    public function getUploadRootDir()
    {
        // the absolute directory path where uploaded documents should be saved
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
    }

    /**
     * @return string the document upload directory path starting from web folder
     */
    private function getUploadDir()
    {
        return 'uploads/users-images';
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
        $this->roles = (array)$roles;

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
     * Set isPhoneVerified
     *
     * @param boolean $isPhoneVerified
     *
     * @return User
     */
    public function setIsPhoneVerified($isPhoneVerified)
    {
        $this->isPhoneVerified = $isPhoneVerified;

        return $this;
    }

    /**
     * Get isPhoneVerified
     *
     * @return bool
     */
    public function getIsPhoneVerified()
    {
        return $this->isPhoneVerified;
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
     * Set lastForgetPasswordRequestDate
     *
     * @param \DateTime $lastForgetPasswordRequestDate
     *
     * @return User
     */
    public function setLastForgetPasswordRequestDate($lastForgetPasswordRequestDate)
    {
        $this->lastForgetPasswordRequestDate = $lastForgetPasswordRequestDate;

        return $this;
    }

    /**
     * Get lastForgetPasswordRequestDate
     *
     * @return \DateTime
     */
    public function getLastForgetPasswordRequestDate()
    {
        return $this->lastForgetPasswordRequestDate;
    }

    /**
     * Set forgetPasswordRequests
     *
     * @param integer $forgetPasswordRequests
     *
     * @return User
     */
    public function setForgetPasswordRequests($forgetPasswordRequests)
    {
        $this->forgetPasswordRequests = $forgetPasswordRequests;

        return $this;
    }

    /**
     * Get forgetPasswordRequests
     *
     * @return integer
     */
    public function getForgetPasswordRequests()
    {
        return $this->forgetPasswordRequests;
    }

    /**
     * Set lastEmailVerificationRequestDate
     *
     * @param \DateTime $lastEmailVerificationRequestDate
     *
     * @return User
     */
    public function setLastEmailVerificationRequestDate($lastEmailVerificationRequestDate)
    {
        $this->lastEmailVerificationRequestDate = $lastEmailVerificationRequestDate;

        return $this;
    }

    /**
     * Get lastEmailVerificationRequestDate
     *
     * @return \DateTime
     */
    public function getLastEmailVerificationRequestDate()
    {
        return $this->lastEmailVerificationRequestDate;
    }

    /**
     * Set verificationEmailRequests
     *
     * @param integer $verificationEmailRequests
     *
     * @return User
     */
    public function setVerificationEmailRequests($verificationEmailRequests)
    {
        $this->verificationEmailRequests = $verificationEmailRequests;

        return $this;
    }

    /**
     * Get verificationEmailRequests
     *
     * @return integer
     */
    public function getVerificationEmailRequests()
    {
        return $this->verificationEmailRequests;
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
     * Set locale
     *
     * @param string $locale
     *
     * @return User
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
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
     * Add phoneVerificationCode
     *
     * @param PhoneVerificationCode $phoneVerificationCode
     *
     * @return User
     */
    public function addPhoneVerificationCode(PhoneVerificationCode $phoneVerificationCode)
    {
        $this->phoneVerificationCodes[] = $phoneVerificationCode;

        return $this;
    }

    /**
     * Remove phoneVerificationCode
     *
     * @param PhoneVerificationCode $phoneVerificationCode
     */
    public function removePhoneVerificationCode(PhoneVerificationCode $phoneVerificationCode)
    {
        $this->phoneVerificationCodes->removeElement($phoneVerificationCode);
    }

    /**
     * Get phoneVerificationCodes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPhoneVerificationCodes()
    {
        return $this->phoneVerificationCodes;
    }

    /**
     * Get roles
     *
     * @return array
     */
    public function getAdminRoles()
    {
        return implode(',' , $this->roles);
    }

    /**
     * @return type
     */
    public static function getRolesList()
    {
        return array();

    }
}
