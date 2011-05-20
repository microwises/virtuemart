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
		$this->loadHelper('shopFunctions');

		$viewName=ShopFunctions::SetViewTitle('vm_states_48');
		$this->assignRef('viewName',$viewName);

		$model = $this->getModel();

		$stateId = JRequest::getVar('virtuemart_state_id');
		$model->setId($stateId);
		$state = $model->getSingleState();

		$countryId = JRequest::getInt('virtuemart_country_id', 0);
		if(empty($countryId)) $countryId = $state->virtuemart_country_id;
		$this->assignRef('virtuemart_country_id',	$countryId);

        $isNew = (count($state) < 1);

		if(empty($countryId) && $isNew){
			JError::raiseWarning(412,'Country id is 0');
			return false;
		}

		$country = $this->getModel('country');
		$country->setId($countryId);
		$this->assignRef('country_name', $country->getData()->country_name);


		$layoutName = JRequest::getVar('layout', 'default');
		if ($layoutName == 'edit') {


			$this->assignRef('state', $state);

			$zoneModel = $this->getModel('Worldzones');
			$this->assignRef('worldZones', $zoneModel->getWorldZonesSelectList());

			ShopFunctions::addStandardEditViewCommands();

		} else {

			ShopFunctions::addStandardDefaultViewCommands();

			$pagination = $model->getPagination();
			$this->assignRef('pagination',	$pagination);

			$states = $model->getStates($countryId);
			$this->assignRef('states',	$states);

		}

		parent::display($tpl);
	}

}
// pure php no closing tag
