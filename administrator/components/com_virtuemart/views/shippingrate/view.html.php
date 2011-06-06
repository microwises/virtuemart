<?php
/**
*
* Shipping Rate View
*
* @package	VirtueMart
* @subpackage ShippingRate
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
 * HTML View class for maintaining the list of shipping rates
 *
 * @package	VirtueMart
 * @subpackage ShippingRate
 * @author RickG
 * @author Max Milbers
 */
class VirtuemartViewShippingRate extends JView {

	function display($tpl = null) {

		// Load the helper(s)
		$this->loadHelper('adminMenu');
		$this->loadHelper('shopFunctions');

		$model = $this->getModel();
        $shippingRate = $model->getShippingRate();

		$viewName=ShopFunctions::SetViewTitle('vm_shipping_rates_48');
		$this->assignRef('viewName',$viewName);

		$layoutName = JRequest::getVar('layout', 'default');
		if ($layoutName == 'edit') {

			$this->assignRef('rate', $shippingRate);

			$carrierModel = $this->getModel('shippingcarrier');
        	$carriers = $carrierModel->getShippingCarriers(false, true);
        	$this->assignRef('carriers', $carriers);

			$currencyModel = $this->getModel('currency');
        	$currencies = $currencyModel->getCurrencies();
        	$this->assignRef('currencies', $currencies);

			$countrymodel = $this->getModel('country');
        	$countries = $countrymodel->getCountries(false, true);
        	$this->assignRef('countries', $countries);

        	$taxrates = $this->renderTaxList($shippingRate->shipping_rate_vat_id);

        	$this->assignRef('taxRates', $taxrates);
			ShopFunctions::addStandardEditViewCommands();
        } else {
			// IS this nothing missing
			// JToolBarHelper::addNewX();
			// JToolBarHelper::editListX();
			// JToolBarHelper::deleteList();


			$shippingRates = $model->getShippingRates();
			$this->assignRef('shippingRates', $shippingRates);

			ShopFunctions::addStandardDefaultViewCommands();
			$lists = ShopFunctions::addStandardDefaultViewLists($model);
			$this->assignRef('lists', $lists);			
		}

		parent::display($tpl);
	}

	/**
	 * Renders the list for the tax rules
	 *
	 * @author Max Milbers
	 */
	function renderTaxList($selected){
		$this->loadHelper('modelfunctions');
//		$selected = modelfunctions::prepareTreeSelection($selected);

		if(!class_exists('VirtueMartModelCalc')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'calc.php');
		$taxes = VirtueMartModelCalc::getTaxes();

		$taxrates = array();
		$taxrates[] = JHTML::_('select.option', '0', JText::_('COM_VIRTUEMART_PRODUCT_TAX_NO_SPECIAL'), 'shipping_rate_vat_id' );
		foreach($taxes as $tax){
			$taxrates[] = JHTML::_('select.option', $tax->virtuemart_calc_id, $tax->calc_name, 'shipping_rate_vat_id');
		}
		$listHTML = JHTML::_('Select.genericlist', $taxrates, 'shipping_rate_vat_id', 'multiple', 'shipping_rate_vat_id', 'text', $selected );
		return $listHTML;
	}
}
// pure php no closing tag
