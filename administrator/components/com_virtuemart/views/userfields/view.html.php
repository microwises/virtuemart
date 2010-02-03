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

/**
 * HTML View class for maintaining the list of countries
 *
 * @package	VirtueMart
 * @subpackage Country
 * @author RickG
 */
class VirtuemartViewUserfields extends JView {

    function display($tpl = null) {
		// Load the helper(s)
		$this->loadHelper('adminMenu');

		$model = $this->getModel();
		$zoneModel = $this->getModel('Userfields');

		$userfield = $model->getUserfield();

		$layoutName = JRequest::getVar('layout', 'default');
		$isNew = ($userfield->userfield_id < 1);

		if ($layoutName == 'edit') {
		    if ($isNew) {
				JToolBarHelper::title(  JText::sprintf('VM_USERFIELD_LIST_ADD',  ': <small><small>[ New ]</small></small>'), 'vm_userfields_48');
				JToolBarHelper::divider();
				JToolBarHelper::save();
				JToolBarHelper::cancel();
		    }
		    else {
				JToolBarHelper::title( JText::sprintf('VM_USERFIELD_LIST_ADD', ': <small><small>[ Edit ]</small></small>'), 'vm_userfields_48');
				JToolBarHelper::divider();
				JToolBarHelper::save();
				JToolBarHelper::cancel('cancel', 'Close');
		    }

		    $this->assignRef('userfield',	$userfield);
		}
		else {
			JToolBarHelper::title( JText::_( 'VM_USERFIELD_LIST_LBL' ), 'vm_userfields_48' );
			JToolBarHelper::publishList();
			JToolBarHelper::unpublishList();
			JToolBarHelper::deleteList('', 'remove', 'Delete');
			JToolBarHelper::editListX();
			JToolBarHelper::addNewX();
			
			$pagination = $model->getPagination();
			$this->assignRef('pagination',	$pagination);

			$userfields = $model->getUserfields();
			$this->assignRef('userfields',	$userfields);
		}
		parent::display($tpl);
    }

}
?>
