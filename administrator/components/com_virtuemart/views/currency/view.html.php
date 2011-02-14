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
			$isNew = ($currency->currency_id < 1);

			if ($isNew) {
				JToolBarHelper::title(  JText::_('VM_CURRENCY_LIST_ADD' ).': <small><small>[ New ]</small></small>', 'vm_currency_48');
				JToolBarHelper::divider();
				JToolBarHelper::save();
				JToolBarHelper::cancel();
			}
			else {
				JToolBarHelper::title( JText::_('VM_CURRENCY_LIST_ADD' ).': <small><small>[ Edit ]</small></small>', 'vm_currency_48');
				JToolBarHelper::divider();
				JToolBarHelper::save();
				JToolBarHelper::cancel('cancel', 'Close');
			}

			$usermodel = $this->getModel('user', 'VirtuemartModel');
			$usermodel->setCurrent();
			$userDetails = $usermodel->getUser();
			if(empty($userDetails->vendor_id)){
				JError::raiseError(403,'Forbidden for non vendors');
			}
			if(empty($currency->vendor_id))$currency->vendor_id = $userDetails->vendor_id;
//			$this->assignRef('vendor_id', $vendorCurrency);
			
			require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'currencydisplay.php');
			$cd = CurrencyDisplay::getCurrencyDisplay($currency->currency_id);
	    	$this->assignRef('currencyDisplay',$cd);
			
//			
//			$userDetails->vendor_id;
        }
        else {
			JToolBarHelper::title( JText::_( 'VM_CURRENCY_LIST_LBL' ), 'vm_currency_48' );
			JToolBarHelper::deleteList('', 'remove', 'Delete');
			JToolBarHelper::editListX();
			JToolBarHelper::addNewX();

			$pagination = $model->getPagination();
			$this->assignRef('pagination',	$pagination);

			$currencies = $model->getCurrenciesList();
			$this->assignRef('currencies',	$currencies);
		}

		parent::display($tpl);
	}

}
// pure php no closing tag
