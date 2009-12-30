<?php
/**
 * Country View
 *
 * @package	VirtueMart
 * @subpackage Country
 * @author RickG
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

jimport( 'joomla.application.component.view');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'adminMenu.php');

/**
 * HTML View class for maintaining the list of countries
 *
 * @package	VirtueMart
 * @subpackage Country
 * @author RickG
 */
class VirtuemartViewCountry extends JView {

    function display($tpl = null) {
	$model = $this->getModel();
	$zoneModel = $this->getModel('ShippingZone');

	$country = $model->getCountry();

	$layoutName = JRequest::getVar('layout', 'default');
	$isNew = ($country->country_id < 1);

	if ($layoutName == 'edit') {
	    if ($isNew) {
		JToolBarHelper::title(  JText::_('VM_COUNTRY_LIST_ADD' ).': <small><small>[ New ]</small></small>', 'vm_countries_48');
		JToolBarHelper::divider();
		JToolBarHelper::save();
		JToolBarHelper::cancel();
	    }
	    else {
		JToolBarHelper::title( JText::_('VM_COUNTRY_LIST_ADD' ).': <small><small>[ Edit ]</small></small>', 'vm_countries_48');
		JToolBarHelper::divider();
		JToolBarHelper::save();
		JToolBarHelper::cancel('cancel', 'Close');
	    }

	    $this->assignRef('country',	$country);
	    $this->assignRef('shippingZones',	$zoneModel->getShippingZoneSelectList());
	}
	else {
	    JToolBarHelper::title( JText::_( 'VM_COUNTRY_LIST_LBL' ), 'vm_countries_48' );
	    JToolBarHelper::publishList();
	    JToolBarHelper::unpublishList();
	    JToolBarHelper::deleteList('', 'remove', 'Delete');
	    JToolBarHelper::editListX();
	    JToolBarHelper::addNewX();

	    $pagination = $model->getPagination();
	    $this->assignRef('pagination',	$pagination);

	    $countries = $model->getCountries();
	    $this->assignRef('countries',	$countries);
	}
	parent::display($tpl);
    }

}
?>
