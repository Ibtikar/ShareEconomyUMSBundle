<?php

namespace Ibtikar\ShareEconomyUMSBundle\Controller;

use Ibtikar\ShareEconomyDashboardDesignBundle\Controller\DashboardController;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends DashboardController
{

    protected $listColumns = array(
        array('file', array('type'=>'image', 'image'=>'getWebPath', 'name'=>'Photo', 'isSearchable' => false, 'isSortable' => false)),
        array('fullName'),
        array('email'),
        array('roles', array('method' => 'getAdminRoles', 'selectSearch' => true, 'selectOptionsList'=>'getRolesList')),
        array('phone', array('class' => 'phoneNumberLtr'))
    );

    protected $defaultSort = array('column' => 'fullName', 'sort' => 'asc');

    protected $translationDomain = 'baseuser';

    protected $bundle = 'IbtikarShareEconomyDashboardDesignBundle';

    protected $pageTitle = 'Admin Users';

    protected $listGlobalActions = array("add"=> 'create_admin');

    protected $formType = 'Ibtikar\ShareEconomyUMSBundle\Form\AdminType';

    /**
     * @author Sarah Mostafa <sarah.marzouk@ibtikar.net.sa>
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function setContainer(\Symfony\Component\DependencyInjection\ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->className = $this->getParameter('ibtikar_share_economy_ums.className');
        $this->entityBundle = $this->getParameter('ibtikar_share_economy_ums.entityBundle');
    }
    /**
     * @author Sarah Mostafa <sarah.marzouk@ibtikar.net.sa>
     * @return type
     */
    public function getListQuery() {
        $query = parent::getListQuery();
        return $query->andWhere('e.systemUser = true');
    }

    protected function prePostParametersCreate(){
        return array('closeRedirection'=>$this->generateUrl('admin_list'));
    }

    protected function getPageTitle()
    {
        return $this->get('translator')->trans('Add New Admin User', array(), $this->translationDomain);
    }

    protected function getCreateFormOptions(){
        $options = array('translation_domain'=>$this->translationDomain, 'validation_groups' => array('email','phone'));
        return $options;
    }

    protected function postValidCreate(Request $request, $entity){

        $em = $this->get('doctrine')->getManager();
        $entity->setValidPassword();
        $entity->setSystemUser(true);
        $em->persist($entity);
        $em->flush();

        $this->getFlashBag("success", $this->get('translator')->trans('Done Successfully'));
        return $this->redirect($this->generateUrl('admin_list'));
    }
}
