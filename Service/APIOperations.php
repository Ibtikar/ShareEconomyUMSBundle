<?php

namespace Ibtikar\ShareEconomyUMSBundle\Service;

use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Ibtikar\ShareEconomyUMSBundle\APIResponse\User as ResponseUser;
use Ibtikar\ShareEconomyUMSBundle\Entity\User;

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
class APIOperations
{

    /** @var $tranlator TranslatorInterface */
    private $translator;

    /** @var $locale string */
    private $locale;

    /** @var $assetsDomain string */
    private $assetsDomain;

    /**
     * @param TranslatorInterface $translator
     * @param string $locale
     * @param string $assetsDomain
     */
    public function __construct(TranslatorInterface $translator, $locale, $assetsDomain)
    {
        $this->translator = $translator;
        $this->locale = $locale;
        $this->assetsDomain = $assetsDomain;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     * @return APIOperations
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @param ConstraintViolationList $errorsObjects
     * @return JsonResponse
     */
    public function getValidationErrorsJsonResponse(ConstraintViolationList $errorsObjects)
    {
        $errors = array();
        foreach ($errorsObjects as $error) {
            $errors[$error->getPropertyPath()] = $this->translator->trans($error->getMessage(), array(), 'validation', $this->locale);
        }
        return $this->getErrorsJsonResponse($errors);
    }

    /**
     * @param array $errors array of "field name" => "error"
     * @return JsonResponse
     */
    public function getErrorsJsonResponse(array $errors)
    {
        return new JsonResponse(array(
            'status' => 'errors',
            'code' => 422,
            'errors' => $errors
        ));
    }

    /**
     * @param string $message
     * @return JsonResponse
     */
    public function getErrorResponse($message = 'We are sorry the server is down.')
    {
        return new JsonResponse(array(
            'status' => 'error',
            'code' => 500,
            'message' => $this->translator->trans($message, array(), 'messages', $this->locale)
        ));
    }

    /**
     * @param array $data
     * @return JsonResponse
     */
    public function getSuccessResponse(array $data = array('status' => 'success'))
    {
        if (!isset($data['code'])) {
            $data['code'] = 200;
        }
        return new JsonResponse($data);
    }

    /**
     * @param object $object
     * @return JsonResponse
     */
    public function getObjectSuccessResponse($object)
    {
        return $this->getSuccessResponse($this->getObjectDataAsArray($object));
    }

    /**
     * @param object $object
     * @return array
     */
    public function getObjectDataAsArray($object)
    {
        return json_decode(json_encode($object), true);
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
        $responseUser->image = $this->assetsDomain . '/' . $user->getWebPath();
        return $this->getObjectDataAsArray($responseUser);
    }
}
