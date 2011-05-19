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
		$this->loadHelper('adminMenu');
		$this->loadHelper('shopFunctions');

		$model = $this->getModel();
        $layoutName = JRequest::getVar('layout', 'default');

		$db = JFactory::getDBO();
		$config =& JFactory::getConfig();
		$tzoffset = $config->getValue('config.offset');
		$this->assignRef('tzoffset',	$tzoffset);

		$dateformat = VmConfig::get('dateformat');
		$this->assignRef('dateformat',	$dateformat);

		if ($layoutName == 'edit') {

			$currency = $model->getCurrency(true);
			$this->assignRef('currency',	$currency);
			$isNew = ($currency->virtuemart_currency_id < 1);

			if ($isNew) {
				JToolBarHelper::title(  JText::_('COM_VIRTUEMART_CURRENCY_FORM').JText::_('COM_VIRTUEMART_FORM_NEW'), 'vm_currency_48');
			} else {
				JToolBarHelper::title( JText::_('COM_VIRTUEMART_CURRENCY_FORM').JText::_('COM_VIRTUEMART_FORM_EDIT'), 'vm_currency_48');
			}

			ShopFunctions::addStandardEditViewCommands();

       }
        else {
			JToolBarHelper::title( JText::_('COM_VIRTUEMART_CURRENCY_LIST_LBL'), 'vm_currency_48' );

			ShopFunctions::addStandardDefaultViewCommands();

			$pagination = $model->getPagination();
			$this->assignRef('pagination',	$pagination);

			$currencies = $model->getCurrenciesList();
			$this->assignRef('currencies',	$currencies);
		}

		parent::display($tpl);
	}

}
// pure php no closing tag
