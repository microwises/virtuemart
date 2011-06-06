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
jimport( 'joomla.application.component.view');

/**
 * HTML View class for maintaining the list of countries
 *
 * @package	VirtueMart
 * @subpackage Country
 * @author RickG
 */
class VirtuemartViewCountry extends JView {

    function display($tpl = null) {

		$option = JRequest::getCmd( 'option');
		$mainframe = JFactory::getApplication() ;

		// Load the helper(s)
		$this->loadHelper('adminMenu');
		$this->loadHelper('shopFunctions');


		$model = $this->getModel();
		$zoneModel = $this->getModel('Worldzones');

		$viewName=ShopFunctions::SetViewTitle('vm_countries_48');
		$this->assignRef('viewName',$viewName);

		$layoutName = JRequest::getVar('layout', 'default');
		if ($layoutName == 'edit') {
			$country = $model->getData();

		    $this->assignRef('country',	$country);
		    $this->assignRef('worldZones',	$zoneModel->getWorldZonesSelectList());

			ShopFunctions::addStandardEditViewCommands();

		}
		else {

			ShopFunctions::addStandardDefaultViewCommands(true,false);

			$pagination = $model->getPagination();
			$this->assignRef('pagination',	$pagination);

			$countries = $model->getCountries(false);
			$this->assignRef('countries',	$countries);
			$lists['order']     = $mainframe->getUserStateFromRequest( $option.'filter_order', 'filter_order', 'ordering', 'cmd' );
			$lists['order_Dir'] = $mainframe->getUserStateFromRequest( $option.'filter_order_Dir', 'filter_order_Dir', '', 'word' );
			$this->assignRef('lists', $lists);
		}

		parent::display($tpl);
    }

}
// pure php no closing tag
