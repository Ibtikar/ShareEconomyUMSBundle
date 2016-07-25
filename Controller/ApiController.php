<?php

namespace Ibtikar\ShareEconomyUMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Ibtikar\ShareEconomyUMSBundle\Entity\User;

class ApiController extends Controller
{

    public function registerCustomerAction(Request $request)
    {
        $entity = new User();
        $form   = $this->createForm('\\Ibtikar\\ShareEconomyUMSBundle\\Form\\UserType', $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_admins_list'));
        }

        return $this->render('AdminBundle::Admins/new.html.twig', array(
                'entity' => $entity,
                'form'   => $form->createView(),
        ));
    }
}