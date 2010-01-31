<?php
/**
*
* Store View
*
* @package	VirtueMart
* @subpackage Store
* @author RickG, jseros
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
 * HTML View class for maintaining the store
 *
 * @package	VirtueMart
 * @subpackage Store
 * @author RickG, jseros
 */
class VirtueMartViewStore extends JView {

    function display($tpl = null) {

	$model = $this->getModel();
	$this->loadHelper('image');
        $this->loadHelper('adminMenu');
	// loading the ShopFunctions Helper by jseros
	$this->loadHelper('shopFunctions');

	// If there is only one store go directly to the edit layout.
	if ($model->getTotalNbrOfStores() == 1) {
	    $storeId = $model->getIdOfOnlyStore();
	    $model->setId($storeId);
	    $this->setLayout('edit');
	}

    $store = $model->getStore();
	$isNew = ($store->vendor_id < 1);

	if ($this->getLayout() == 'edit') {
	    if ($isNew) {
		JToolBarHelper::title(  JText::_('VM_STORE_FORM_LBL' ).': <small><small>[ New ]</small></small>', 'vm_store_48');
		JToolBarHelper::divider();
		JToolBarHelper::save();
		JToolBarHelper::cancel();
	    }
	    else {
		JToolBarHelper::title( JText::_('VM_STORE_FORM_LBL' ).': <small><small>[ Edit ]</small></small>', 'vm_store_48');
		JToolBarHelper::divider();
		JToolBarHelper::save();
		JToolBarHelper::cancel('cancel', 'Close');
	    }

	    $this->loadHelper('currencyDisplay');
	    CurrencyDisplay::setCurrencyDisplayToStyleStr($store->vendor_currency_display_style);
	    $currencyModel = $this->getModel('currency');
	    $this->assignRef('store', $store);
	    $currencies = $currencyModel->getCurrencies();
	    $this->assignRef('currencies', $currencies);

	    //singleton instance of editor
	    $editor = JFactory::getEditor();
	    $this->assignRef('editor', $editor);
	    
	    $countriesList = ShopFunctions::renderCountryList($store->userInfo->country_id);
		$this->assignRef('countriesList', $countriesList);
		
		$statesList = ShopFunctions::renderStateList($store->userInfo->state_id, $store->userInfo->country_id, 'country_id');
		$this->assignRef('statesList', $statesList);
	}
	else {

	    JToolBarHelper::title( JText::_( 'VM_STORE_FORM_LBL' ), 'vm_store_48' );
	    JToolBarHelper::deleteList('', 'remove', 'Delete');
	    JToolBarHelper::editListX();
	    JToolBarHelper::addNewX();

	    $pagination = $model->getPagination();
	    $this->assignRef('pagination',	$pagination);

	    $stores = $model->getStores();
	    $this->assignRef('stores',	$stores);
	}

	parent::display($tpl);
    }

}
?>
