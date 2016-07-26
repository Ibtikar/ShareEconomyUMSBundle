<?php

namespace Ibtikar\ShareEconomyUMSBundle\Controller\API;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class UserController extends Controller
{

    /**
     * Login with existing user
     *
     * @ApiDoc(
     *  tags={
     *      "testing"="red"
     *  },
     *  section="User",
     *  parameters={
     *      {"name"="username", "dataType"="string", "required"=true, "format"="{email address}"},
     *      {"name"="password", "dataType"="string", "required"=true, "format"="{length: min: 8, max: 4096}, {match: /[\D+]+/u}, {match: /\d+/u}"}
     *  },
     *  statusCodes={
     *      200="Returned on success"
     *  }
     * )
     * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
     * @return JsonResponse
     */
    public function loginAction()
    {
        // The security layer will intercept this request it should never reach here
        return new JsonResponse(array('code' => 401, 'message' => 'Bad credentials'));
    }
}
