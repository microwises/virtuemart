<?php
/**
*
* Currency View
*
* @package	VirtueMart
* @subpackage Currency
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
 * HTML View class for maintaining the list of currencies
 *
 * @package	VirtueMart
 * @subpackage Currency
 * @author RickG, Max Milbers
 */
class VirtuemartViewCurrency extends JView {

	function display($tpl = null) {

		// Load the helper(s)
		$this->loadHelper('adminui');
		$this->loadHelper('shopFunctions');
		$this->loadHelper('html');

		$model = $this->getModel();


		$db = JFactory::getDBO();
		$config = JFactory::getConfig();
		$layoutName = JRequest::getWord('layout', 'default');
		if ($layoutName == 'edit') {
			$cid	= JRequest::getVar( 'cid' );

			$task = JRequest::getWord('task', 'add');
			//JArrayHelper::toInteger($cid);
			if($task!='add' && !empty($cid) && !empty($cid[0])){
				$cid = (int)$cid[0];
			} else {
				$cid = 0;
			}

			$model->setId($cid);
			$currency = $model->getCurrency();
			$viewName=ShopFunctions::SetViewTitle('',$currency->currency_name);
			$this->assignRef('currency',	$currency);

			ShopFunctions::addStandardEditViewCommands();

		} else {

			$viewName=ShopFunctions::SetViewTitle();
			ShopFunctions::addStandardDefaultViewCommands();

			$currencies = $model->getCurrenciesList(JRequest::getWord('search', false));
			$this->assignRef('currencies',	$currencies);

			$lists = ShopFunctions::addStandardDefaultViewLists($model);
			$this->assignRef('lists', $lists);


		}

		parent::display($tpl);
	}

}
// pure php no closing tag
