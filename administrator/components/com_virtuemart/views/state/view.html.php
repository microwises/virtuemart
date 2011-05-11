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

		$stateId = JRequest::getInt('state_id', null);
		$model->setId($stateId);
		$state =& $model->getSingleState();

        $layoutName = JRequest::getVar('layout', 'default');

		$published = JRequest::getBool('published', false);
		$this->assignRef('enabled',	$published);


		$countryId = JRequest::getInt('country_id', 0);
		if(empty($countryId)) $countryId = $state->country_id;
		$this->assignRef('country_id',	$countryId);


        $isNew = (count($state) < 1);

		if(empty($countryId) && $layoutName == 'edit' && $isNew){
			JError::raiseWarning(412,'Country id is 0');
			return false;
		}

		$country = $this->getModel('country');
		$country->setId($countryId);
		$this->assignRef('country_name', $country->getCountry()->country_name);

		if ($layoutName == 'edit') {
			if ($isNew) {
				JToolBarHelper::title(  JText::_('COM_VIRTUEMART_STATE_LIST_ADD').JText::_('COM_VIRTUEMART_FORM_NEW'), 'vm_states_48');
			} else {
				JToolBarHelper::title( JText::_('COM_VIRTUEMART_STATE_LIST_ADD').JText::_('COM_VIRTUEMART_FORM_EDIT'), 'vm_states_48');
			}
			JToolBarHelper::divider();
			JToolBarHelper::save();
                        JToolBarHelper::apply();
			JToolBarHelper::cancel();

			$this->assignRef('state', $state);

			$this->assignRef('shippingZones', $zoneModel->getShippingZoneSelectList());
        }
        else {
			JToolBarHelper::title( JText::_('COM_VIRTUEMART_STATE_LIST_LBL'), 'vm_states_48' );
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
