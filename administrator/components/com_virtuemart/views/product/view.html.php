<?php
/**
*
* View class for the product
*
* @package	VirtueMart
* @subpackage
* @author
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
 * HTML View class for the VirtueMart Component
 *
 * @package		VirtueMart
 * @author RolandD,Max Milbers
 */
class VirtuemartViewProduct extends JView {

	function display($tpl = null) {

		$mainframe = Jfactory::getApplication();
		$option = JRequest::getVar('option');

		/* Get the task */
		$task = JRequest::getVar('task');

		/* Load helpers */
		$this->loadHelper('currencydisplay');
		$this->loadHelper('adminMenu');
		$this->loadHelper('shopFunctions');
		JView::loadHelper('image');

		/* Load some common models */
		$category_model = $this->getModel('category');

		/* Handle any publish/unpublish */
		switch ($task) {
			case 'add':
			case 'edit':
				/* Load some behaviour */
				jimport('joomla.html.pane');
				$pane = JPane::getInstance();
				JHTML::_('behavior.tooltip');
				JHTML::_('behavior.calendar');
				$editor = JFactory::getEditor();

				/* Load the product */
				$product_model = $this->getModel('product');
//				$product = $this->get('Product');
				$product = $product_model->getProductSingle('',false,false,false);

				/* Get the category tree */
				if (isset($product->categories)) $category_tree = ShopFunctions::categoryListTree($product->categories);
				else $category_tree = ShopFunctions::categoryListTree();
				$this->assignRef('category_tree', $category_tree);

				/* Load the product price */
				if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
				$calculator = calculationHelper::getInstance();
				$product->prices = $calculator -> getProductPrices($product->virtuemart_product_id);

				$dbTax = JText::_('COM_VIRTUEMART_RULES_EFFECTING') ;
				foreach($calculator->rules['dBTax'] as $rule){

					$dbTax .= $rule['calc_name']. '<br />';
				}
				$this->assignRef('dbTaxRules', $dbTax);

				$tax = JText::_('COM_VIRTUEMART_TAX_EFFECTING');
				foreach($calculator->rules['tax'] as $rule){
					$tax .= $rule['calc_name']. '<br />';
				}
				$this->assignRef('taxRules', $tax);

				$daTax = JText::_('COM_VIRTUEMART_RULES_EFFECTING');
				foreach($calculator->rules['dATax'] as $rule){
					$daTax .= $rule['calc_name']. '<br />';
				}
				$this->assignRef('daTaxRules', $daTax);

				$this->assignRef('override', $calculator->override);
				$this->assignRef('product_override_price', $calculator->product_override_price);

				$lists['taxrates'] = $this -> renderTaxList($product->product_tax_id);
				$lists['discounts'] = $this -> renderDiscountList($product->product_discount_id);

//				$lists['dbdiscounts'] = $this -> renderDiscountList($product->product_discount_id,1);
//				$lists['dadiscounts'] = $this -> renderDiscountList($product->product_discount_id,0);

				if(!class_exists('VirtueMartModelConfig')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'config.php');
				$productLayouts = VirtueMartModelConfig::getLayoutList('productdetails');
				$this->assignRef('productLayouts', $productLayouts);

				/* Load Images */
				$product_model->addImagesToProducts($product);

				if(is_Dir(VmConfig::get('vmtemplate').DS.'images'.DS.'availability/')){
					$imagePath = VmConfig::get('vmtemplate').DS.'images'.DS.'availability/';
				} else {
					$imagePath = 'components'.DS.'com_virtuemart'.DS.'assets'.DS.'images'.DS.'availability/';
				}
				$this->assignRef('imagePath', $imagePath);

				/* Load the vendors */
				$vendor_model = $this->getModel('vendor');

				$vendors = $vendor_model->getVendors();
				$lists['vendors'] = JHTML::_('select.genericlist', $vendors, 'virtuemart_vendor_id', '', 'virtuemart_vendor_id', 'vendor_name', $product->virtuemart_vendor_id);

				/* Load the currencies */
				$currency_model = $this->getModel('currency');

				$vendor_model->setId($product->virtuemart_vendor_id);
				$vendor = $vendor_model->getVendor();
				if(empty($product->product_currency)){
					$product->product_currency = $vendor->vendor_currency;
				}
				$currencies = JHTML::_('select.genericlist', $currency_model->getCurrencies(), 'product_currency', '', 'currency_id', 'currency_name', $product->product_currency);
				$currency = $currency_model->getCurrency($product->product_currency);
				$this->assignRef('product_currency', $currency->currency_symbol);
				$currency = $currency_model->getCurrency($vendor->vendor_currency);
				$this->assignRef('vendor_currency', $currency->currency_symbol);
//				$product_currency_symbol = $currency->currency_symbol;
				/* Load the manufacturers */
				$mf_model = $this->getModel('manufacturer');
				$manufacturers = $mf_model->getManufacturerDropdown($product->manufacturer_id);

				$lists['manufacturers'] = JHTML::_('select.genericlist', $manufacturers, 'manufacturer_id', 'class="inputbox"', 'value', 'text', $product->manufacturer_id );

				/* Load the attribute names */
				$product->attribute_names = $this->get('ProductAttributeNames');

				/* Load the attribute values */
				$product->attribute_values = $this->get('ProductAttributeValues');

				/* TODO remove this */
				$product->child_products = null;

				if( empty( $product->product_available_date )) {
					$product->product_available_date = time();
				}

				/* Get the minimum and maximum order levels */
				$min_order = 0;
				$max_order = 0;
				if(strstr($product->product_order_levels, ',')) {
					$order_levels = explode(',', $product->product_order_levels);
					$min_order = $order_levels[0];
					$max_order = $order_levels[1];
				}

				/* Get the related products */
				$related_products = $product_model->getRelatedProducts($product->virtuemart_product_id);
				if (!$related_products) $related_products = array();
				$lists['related_products'] = JHTML::_('select.genericlist', $related_products, 'related_products[]', 'autocomplete="off" multiple="multiple" size="10" ondblclick="removeSelectedOptions(\'related_products\')"', 'id', 'text', $related_products);

				/* Load waiting list */
				if ($product->virtuemart_product_id) {
					$waitinglist = $this->get('waitingusers', 'waitinglist');
					$this->assignRef('waitinglist', $waitinglist);
				}

				$this->loadHelper('customhandler');
				$fieldTypes = VmCustomHandler::getField_types();
				$this->assignRef('fieldTypes', $fieldTypes);
				/* Load product types lists */
				$productTypes = $this->get('productTypes');
				$this->assignRef('productTypes', $productTypes);

				/* Load affected product  customs fields */
				//$productCustoms = $this->get('productCustomsList');
				//if (!$productCustoms) $productCustoms = array();
				//$lists['product_customs'] = JHTML::_('select.genericlist', $productCustoms, 'productCustoms[]', 'autocomplete="off" multiple="multiple" size="10" ondblclick="removeSelectedOptions(\'productCustoms\')"', 'id', 'text', $productCustoms);

				//$this->assignRef('product_customs', $productCustoms);

				/* Load product types lists */
				$customsList = VmCustomHandler::getCustomsList();
				$this->assignRef('customsList', JHTML::_('select.genericlist', $customsList,'customlist','size="5"'));


				/* Set up labels */
				if ($product->product_parent_id > 0) {
					$info_label = JText::_('COM_VIRTUEMART_PRODUCT_FORM_ITEM_INFO_LBL');
					$status_label = JText::_('COM_VIRTUEMART_PRODUCT_FORM_ITEM_STATUS_LBL');
					$dim_weight_label = JText::_('COM_VIRTUEMART_PRODUCT_FORM_ITEM_DIM_WEIGHT_LBL');
					$images_label = JText::_('COM_VIRTUEMART_PRODUCT_FORM_ITEM_IMAGES_LBL');
					$delete_message = JText::_('COM_VIRTUEMART_PRODUCT_FORM_DELETE_ITEM_MSG');
				}
				else {
					if ($task == 'add') $action = JText::_('COM_VIRTUEMART_PRODUCT_FORM_NEW_PRODUCT_LBL');
					else $action = JText::_('COM_VIRTUEMART_PRODUCT_FORM_UPDATE_ITEM_LBL');

					$info_label = JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRODUCT_INFO_LBL');
					$status_label = JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRODUCT_STATUS_LBL');
					$dim_weight_label = JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRODUCT_DIM_WEIGHT_LBL');
					$images_label = JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRODUCT_IMAGES_LBL');
					$delete_message = JText::_('COM_VIRTUEMART_PRODUCT_FORM_DELETE_PRODUCT_MSG');
				}

				/* Assign the values */
				$this->assignRef('pane', $pane);
				$this->assignRef('editor', $editor);
				$this->assignRef('lists', $lists);
				$this->assignRef('product', $product);
				$this->assignRef('currencies', $currencies);
				$this->assignRef('manufacturers', $manufacturers);
				$this->assignRef('min_order', $min_order);
				$this->assignRef('max_order', $max_order);
				$this->assignRef('related_products', $related_products);

				/* Assign label values */
				$this->assignRef('action', $action);
				$this->assignRef('info_label', $info_label);
				$this->assignRef('status_label', $status_label);
				$this->assignRef('dim_weight_label', $dim_weight_label);
				$this->assignRef('images_label', $images_label);
				$this->assignRef('delete_message', $delete_message);

				/* Toolbar */
				if ($task == 'add') $text = JText::_('COM_VIRTUEMART_PRODUCT_FORM_LBL').JText::_('COM_VIRTUEMART_FORM_NEW');
				else $text = JText::_('COM_VIRTUEMART_PRODUCT_FORM_LBL').JText::_('COM_VIRTUEMART_FORM_EDIT').' :: '.$product->product_sku.' :: '.$product->product_name;



				JToolBarHelper::title($text, 'vm_product_48');
				JToolBarHelper::divider();
				JToolBarHelper::save();
                                JToolBarHelper::apply();
				JToolBarHelper::cancel();

				break;
			case 'addproducttype':
				/* Get the product types that can be chosen */
				$producttypes = JHTML::_('select.genericlist', $this->get('ProductTypeList'), 'virtuemart_producttype_id');
				$this->assignRef('producttypes', $producttypes);

				/* Get the product */
				$product = $this->get('ProductDetails');
				$this->assignRef('product', $product);

				/* Toolbar */
				$text = JText::_('COM_VIRTUEMART_PRODUCT_PRODUCT_TYPE_FORM_LBL').' :: '.$product->product_sku.' :: '.$product->product_name;
				JToolBarHelper::title($text, 'vm_product_48');
				JToolBarHelper::divider();
//				JToolBarHelper::apply('saveproducttype');
//				JToolBarHelper::save('saveproducttype');
				JToolBarHelper::cancel();
				break;
			default:
				switch ($task) {
					case 'publish':
						$this->get('Publish');
						break;
					case 'unpublish':
						$this->get('Publish');
						break;
					case 'saveorder':
						$this->get('SaveOrder');
						break;
					case 'orderup':
						$this->get('OrderUp');
						break;
					case 'orderdown':
						$this->get('OrderDown');
						break;
				}
				/* Start model */
				$model = $this->getModel();

				/* Get the list of products */
				$productlist = $this->get('ProductList');

				/* Get the category tree */
				$categoryId = JRequest::getInt('virtuemart_category_id');
//				if(!empty($categoryId)){
					$category_tree = ShopFunctions::categoryListTree(array($categoryId));
					$this->assignRef('category_tree', $category_tree);
//				}


				/* Check for child products if it is a parent item */
				if (JRequest::getInt('product_parent_id', 0) == 0) {
					foreach ($productlist as $virtuemart_product_id => $product) {
						$product->haschildren = $model->checkChildProducts($virtuemart_product_id);
					}
				}

				/* Check for Media Items and Reviews, set the price*/
				if(!class_exists('VirtueMartModelMedia')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'media.php');
				$media = new VirtueMartModelMedia();

				if(!class_exists('VirtueMartModelRatings')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'ratings.php');
				$productreviews = new VirtueMartModelRatings();
				$currencydisplay = CurrencyDisplay::getCurrencyDisplay();

				/* Load the product price */
				if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');

				$calculator = calculationHelper::getInstance();
				$vendor_model = $this->getModel('vendor');

				foreach ($productlist as $virtuemart_product_id => $product) {
					$product->mediaitems = count($product->file_ids);
					$product->reviews = $productreviews->countReviewsForProduct($virtuemart_product_id);

					$vendor_model->setId($product->virtuemart_vendor_id);
					$vendor = $vendor_model->getVendor();
					$calculator->setVendorCurrency($vendor->vendor_currency);
					$currencyDisplay = CurrencyDisplay::getCurrencyDisplay($vendor->virtuemart_vendor_id,$vendor->vendor_currency);
					$price = $calculator->convertCurrencyTo($product->product_currency,$product->product_price,true);
					$product->product_price_display = $currencyDisplay->getFullValue($price);

					/* Write the first 5 categories in the list */
					if(!class_exists('modelfunctions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'modelfunctions.php');
					$product->categoriesList = modelfunctions::buildGuiList('virtuemart_category_id','#__virtuemart_product_categories','virtuemart_product_id',$product->virtuemart_product_id,'category_name','#__virtuemart_categories','virtuemart_category_id');

//					$product->product_price_display = $calculator->priceDisplay($product->product_price,$product->product_currency,true);//$currencydisplay->getValue($product->product_price);
				}

				/* Get the pagination */
				$pagination = $this->get('Pagination');
				$lists['filter_order'] = $mainframe->getUserStateFromRequest($option.'filter_order', 'filter_order', '', 'cmd');
				$lists['filter_order_Dir'] = $mainframe->getUserStateFromRequest($option.'filter_order_Dir', 'filter_order_Dir', '', 'word');

				/* Create filter */
				/* Search type */
				$options = array();
				$options[] = JHTML::_('select.option', '', JText::_('COM_VIRTUEMART_SELECT'));
				$options[] = JHTML::_('select.option', 'product', JText::_('COM_VIRTUEMART_PRODUCT_LIST_SEARCH_BY_DATE_TYPE_PRODUCT'));
				$options[] = JHTML::_('select.option', 'price', JText::_('COM_VIRTUEMART_PRODUCT_LIST_SEARCH_BY_DATE_TYPE_PRICE'));
				$options[] = JHTML::_('select.option', 'withoutprice', JText::_('COM_VIRTUEMART_PRODUCT_LIST_SEARCH_BY_DATE_TYPE_WITHOUTPRICE'));
				$lists['search_type'] = JHTML::_('select.genericlist', $options, 'search_type', '', 'value', 'text', JRequest::getVar('search_type'));

				/* Search order */
				$options = array();
				$options[] = JHTML::_('select.option', 'bf', JText::_('COM_VIRTUEMART_PRODUCT_LIST_SEARCH_BY_DATE_BEFORE'));
				$options[] = JHTML::_('select.option', 'af', JText::_('COM_VIRTUEMART_PRODUCT_LIST_SEARCH_BY_DATE_AFTER'));
				$lists['search_order'] = JHTML::_('select.genericlist', $options, 'search_order', '', 'value', 'text', JRequest::getVar('search_order'));

				/* Toolbar */
				JToolBarHelper::title(JText::_('COM_VIRTUEMART_PRODUCT_LIST'), 'vm_product_48');
				JToolBarHelper::custom('createchild', 'virtuemart_child_32', 'virtuemart_child_32', JText::_('COM_VIRTUEMART_PRODUCT_CHILD'), true);
				JToolBarHelper::custom('cloneproduct', 'virtuemart_clone_32', 'virtuemart_clone_32', JText::_('COM_VIRTUEMART_PRODUCT_CLONE'), true);
//				JToolBarHelper::custom('addattribute', 'icon-32-new', '', JText::_('COM_VIRTUEMART_ADD_ATTRIBUTE'), true);
//				JToolBarHelper::custom('addproducttype', 'icon-32-new', '', JText::_('COM_VIRTUEMART_ADD_PRODUCT_TYPE'), true);
				JToolBarHelper::custom('addrating', 'icon-32-new', '', JText::_('COM_VIRTUEMART_ADD_RATING'), true);
				JToolBarHelper::divider();
				JToolBarHelper::publish();
				JToolBarHelper::unpublish();
				JToolBarHelper::deleteListX();
                                JToolBarHelper::editListX();
				JToolBarHelper::addNewX();

				/* Assign the data */
				$this->assignRef('productlist', $productlist);
				$this->assignRef('pagination',	$pagination);
				$this->assignRef('lists', $lists);
				break;
		}

		parent::display($tpl);
	}

	function renderMail() {
		$this->setLayout('mail_html_waitlist');
		$this->subject = JText::sprintf('COM_VIRTUEMART_PRODUCT_WAITING_LIST_EMAIL_SUBJECT', $this->productName);
		$notice_body = JText::sprintf('COM_VIRTUEMART_PRODUCT_WAITING_LIST_EMAIL_TEXT', $this->productName, $this->url);

		parent::display();
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
		$taxrates[] = JHTML::_('select.option', '0', JText::_('COM_VIRTUEMART_PRODUCT_TAX_NO_SPECIAL'), 'product_tax_id' );
		foreach($taxes as $tax){
			$taxrates[] = JHTML::_('select.option', $tax->virtuemart_calc_id, $tax->calc_name, 'product_tax_id');
		}
		$listHTML = JHTML::_('Select.genericlist', $taxrates, 'product_tax_id', 'multiple', 'product_tax_id', 'text', $selected );
		return $listHTML;
	}

	/**
	 * Renders the list for the discount rules
	 *
	 * @author Max Milbers
	 */
	function renderDiscountList($selected,$before=false){
		$this->loadHelper('modelfunctions');
//		$selected = modelfunctions::prepareTreeSelection($selected);

		$discounts = VirtueMartModelCalc::getDiscounts();
//		if($before){
//			$discounts = VirtueMartModelCalc::getDBDiscounts();
//		} else {
//			$discounts = VirtueMartModelCalc::getDADiscounts();
//		}

		$discountrates = array();
		$discountrates[] = JHTML::_('select.option', '0', JText::_('COM_VIRTUEMART_PRODUCT_DISCOUNT_NO_SPECIAL'), 'product_discount_id' );
//		$discountrates[] = JHTML::_('select.option', 'override', JText::_('COM_VIRTUEMART_PRODUCT_DISCOUNT_OVERRIDE'), 'product_discount_id');
		foreach($discounts as $discount){
			$discountrates[] = JHTML::_('select.option', $discount->virtuemart_calc_id, $discount->calc_name, 'product_discount_id');
		}
		$listHTML = JHTML::_('Select.genericlist', $discountrates, 'product_discount_id', 'multiple', 'product_discount_id', 'text', $selected );
		return $listHTML;

	}
}

//pure php no closing tag
