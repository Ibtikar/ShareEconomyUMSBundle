<?php

namespace Ibtikar\ShareEconomyUMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Ibtikar\ShareEconomyUMSBundle\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class ApiController extends Controller
{

    /**
     * Register a customer to the system
     *
     * @ApiDoc(
     *  description="Register a customer to the system",
     *  parameters={
     *      {"name"="fullName", "dataType"="string", "required"=true},
     *      {"name"="email", "dataType"="string", "required"=true},
     *      {"name"="phone", "dataType"="string", "required"=true},
     *      {"name"="password", "dataType"="string", "required"=true}
     *  }
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function registerCustomerAction(Request $request)
    {
        $user = new User();
        $user->setFullName($request->get('fullName'));
        $user->setEmail($request->get('email'));
        $user->setPhone($request->get('phone'));
        $user->setUserPassword($request->get('password'));

        $validator = $this->get('validator');
        $errors    = $validator->validate($user);

        if (count($errors) > 0) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $output = ['status' => true, 'user' => $errors];
        } else {
             $output = ['status' => false];
        }

        return JsonResponse($output);
    }
}