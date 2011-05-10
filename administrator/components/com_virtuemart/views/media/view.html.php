<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage
* @author
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id$
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the view framework
jimport( 'joomla.application.component.view');

/**
 * HTML View class for the VirtueMart Component
 *
 * @package		VirtueMart
 * @author
 */
class VirtuemartViewMedia extends JView {

	function display($tpl = null) {

		// Load the helper(s)
		$this->loadHelper('adminMenu');

		$model = $this->getModel('media');
		$this->loadHelper('permissions');
		$this->assignRef('perms', Permissions::getInstance());

		//@todo should be depended by loggedVendor
		$vendorId=1;
		$this->assignRef('vendorId', $vendorId);

//		$layoutName = JRequest::getVar('layout', 'default');
		$task = JRequest::getCmd('task', '');
		if ($task == 'edit' || $task== 'add' ) {

			$media = $model->getFile();
			$this->assignRef('media',	$media);

			$isNew = ($media->file_id < 1);
			if ($isNew) {
				JToolBarHelper::title(  JText::_('COM_VIRTUEMART_MEDIA_FORM_LBL').JText::_('COM_VIRTUEMART_FORM_NEW'), 'vm_countries_48');

				$usermodel = $this->getModel('user', 'VirtuemartModel');
				$usermodel->setCurrent();
				$userDetails = $usermodel->getUser();
				if(empty($userDetails->vendor_id)){
					JError::raiseError(403,'Forbidden for non vendors');
				}
				if(empty($media->vendor_id))$media->vendor_id = $userDetails->vendor_id;
			}
			else {
				JToolBarHelper::title( JText::_('COM_VIRTUEMART_MEDIA_FORM_LBL').JText::_('COM_VIRTUEMART_FORM_EDIT'), 'vm_countries_48');
			}

			JToolBarHelper::divider();
			JToolBarHelper::save();
                        JToolBarHelper::apply();
			JToolBarHelper::cancel();

        }
        else {
			JToolBarHelper::title( JText::_('COM_VIRTUEMART_MEDIA_LIST_LBL'), 'vm_countries_48' );
			JToolBarHelper::publishList();
			JToolBarHelper::unpublishList();
			JToolBarHelper::deleteList('', 'remove', 'Delete');
			JToolBarHelper::editListX();
			JToolBarHelper::addNewX();

			$pagination = $model->getPagination();
			$this->assignRef('pagination',	$pagination);

			$files = $model->getFiles();
			$this->assignRef('files',	$files);

		}

		parent::display($tpl);
	}

}
// pure php no closing tag