<?php
/**
*
* Extensions View
*
* @package	VirtueMart
* @subpackage Extensions
* @author StephanieS
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
 * HTML View class for maintaining the list of extensions
 *
 * @package	VirtueMart
 * @subpackage Extensions
 * @author Max Milbers
 */
class VirtuemartViewUsergroups extends JView {

	function display( $tpl = null ){

		$this->loadHelper('adminMenu');
		$this->loadHelper('shopFunctions');
		$model = $this->getModel();
		// TODO icon for this view
		$viewName=ShopFunctions::SetViewTitle('vm_countries_48');
		$this->assignRef('viewName',$viewName);

		$layoutName = JRequest::getVar('layout', 'default');
		if ($layoutName == 'edit') {

			$usergroup = $model->getUsergroup();
			$this->assignRef('usergroup',	$usergroup);

			ShopFunctions::addStandardEditViewCommands();

		} else {

			$ugroups = $model->getUsergroups(false,true);
			$this->assignRef('usergroups',	$ugroups);

			ShopFunctions::addStandardDefaultViewCommands();
			$lists = ShopFunctions::addStandardDefaultViewLists($model);
			$this->assignRef('lists', $lists);

		}

		parent::display($tpl);
	}

}
// pure php no closing tag
