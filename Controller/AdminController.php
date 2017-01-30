<?php

namespace Ibtikar\ShareEconomyUMSBundle\Controller;

use Ibtikar\ShareEconomyDashboardDesignBundle\Controller\DashboardController;

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

}
