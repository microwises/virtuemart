<?php
/**
*
* Country View
*
* @package	VirtueMart
* @subpackage Country
* @author RickG
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
if(!class_exists('VmView'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmview.php');

/**
 * HTML View class for maintaining the list of countries
 *
 * @package	VirtueMart
 * @subpackage Country
 * @author RickG
 */
class VirtuemartViewCountry extends VmView {

    function display($tpl = null) {

		// Load the helper(s)


		$this->loadHelper('html');


		$model = VmModel::getModel('country');
		$zoneModel = VmModel::getModel('worldzones');

		$this->SetViewTitle();


		$layoutName = JRequest::getWord('layout', 'default');
		if ($layoutName == 'edit') {
			$country = $model->getData();

		    $this->assignRef('country',	$country);
		    $this->assignRef('worldZones',	$zoneModel->getWorldZonesSelectList());

			$this->addStandardEditViewCommands();

		}
		else {
			$filter_country = JRequest::getWord('filter_country', false);
			$countries = $model->getCountries(false, false, $filter_country);
			$this->assignRef('countries',	$countries);

			$this->addStandardDefaultViewCommands(true,false);
			$this->addStandardDefaultViewLists($model);


		}

		parent::display($tpl);
    }

}
// pure php no closing tag
