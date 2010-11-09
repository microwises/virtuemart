<?php
/**
*
* State View
*
* @package	VirtueMart
* @subpackage State
* @author RickG, Max Milbers
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
 * HTML View class for maintaining the list of states
 *
 * @package	VirtueMart
 * @subpackage State
 * @author Max Milbers
 */
class VirtuemartViewState extends JView {

	function display($tpl = null) {

		// Load the helper(s)
		$this->loadHelper('adminMenu');

		$model = $this->getModel();
		$zoneModel = $this->getModel('ShippingZone');

		$state =& $model->getState();

        $layoutName = JRequest::getVar('layout', 'default');
		$countryId = JRequest::getVar('country_id', '');
		$published = JRequest::getVar('published', '');

		$this->assignRef('country_id',	$countryId);
 		$this->assignRef('published',	$published);

        $isNew = ($state < 1);

		if ($layoutName == 'edit') {
			if ($isNew) {
				JToolBarHelper::title(  JText::_('VM_STATE_LIST_ADD' ).': <small><small>[ New ]</small></small>', 'vm_states_48');
				JToolBarHelper::divider();
				JToolBarHelper::save();
				JToolBarHelper::cancel();
			}
			else {
				JToolBarHelper::title( JText::_('VM_STATE_LIST_ADD' ).': <small><small>[ Edit ]</small></small>', 'vm_states_48');
				JToolBarHelper::divider();
				JToolBarHelper::save();
				JToolBarHelper::cancel('cancel', 'Close');
			}
			$this->assignRef('state', $state);

			$this->assignRef('shippingZones', $zoneModel->getShippingZoneSelectList());
        }
        else {
			JToolBarHelper::title( JText::_( 'VM_STATE_LIST_LBL' ), 'vm_states_48' );
			JToolBarHelper::publishList();
			JToolBarHelper::unpublishList();
			JToolBarHelper::deleteList('', 'remove', 'Delete');
			JToolBarHelper::editListX();
			JToolBarHelper::addNewX();

			$pagination = $model->getPagination();
			$this->assignRef('pagination',	$pagination);

			$states = $model->getStates($countryId);

			$this->assignRef('states',	$states);
		}

		parent::display($tpl);
	}

}
// pure php no closing tag
