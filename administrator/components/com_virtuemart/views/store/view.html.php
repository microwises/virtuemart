<?php
/**
 * Store View
 *
 * @package	VirtueMart
 * @subpackage Store
 * @author RickG
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */
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
