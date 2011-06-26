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
		$this->loadHelper('shopFunctions');
		$mainframe = JFactory::getApplication();
		$option = JRequest::getWord('option');

		// Get the task
		$task = JRequest::getWord('task');

		// Load helpers
		$this->loadHelper('currencydisplay');
		$this->loadHelper('adminMenu');
		$this->loadHelper('shopFunctions');
		$this->loadHelper('html');
		JView::loadHelper('image');

		// Load some common models
		$category_model = $this->getModel('category');

		// Handle any publish/unpublish
		switch ($task) {
			case 'add':
			case 'edit':

				$viewName = ShopFunctions::SetViewTitle('vm_product_48' );
				/* Load the product */
				$product_model = $this->getModel('product');

				$virtuemart_product_id = JRequest::getInt('virtuemart_product_id', array());
				if(is_array($virtuemart_product_id) && count($virtuemart_product_id) > 0) $virtuemart_product_id = $virtuemart_product_id[0];
				$product = $product_model->getProductSingle($virtuemart_product_id,false);
				$product_child = $product_model->getProductChilds($virtuemart_product_id);
				$product_parent= $product_model->getProductParent($product->product_parent_id);

				// Get the category tree
				if (isset($product->categories)) $category_tree = ShopFunctions::categoryListTree($product->categories);
				else $category_tree = ShopFunctions::categoryListTree();
				$this->assignRef('category_tree', $category_tree);

				// Load the product price
				if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
				$calculator = calculationHelper::getInstance();
				$product->prices = $calculator -> getProductPrices($product);

				$dbTax = JText::_('COM_VIRTUEMART_RULES_EFFECTING') ;
				foreach($calculator->rules['dBTax'] as $rule){

					$dbTax .= $rule['calc_name']. '<br />';
				}
				$this->assignRef('dbTaxRules', $dbTax);

				$tax = JText::_('COM_VIRTUEMART_TAX_EFFECTING');
				foreach($calculator->rules['Tax'] as $rule){
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

				$lists['taxrates'] = ShopFunctions::renderTaxList($product->product_tax_id,'product_tax_id');
				$lists['discounts'] = $this -> renderDiscountList($product->product_discount_id);

				if(!class_exists('VirtueMartModelConfig')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'config.php');
				$productLayouts = VirtueMartModelConfig::getLayoutList('productdetails');
				$this->assignRef('productLayouts', $productLayouts);

				// Load Images
				$product_model->addImages($product);

				if(is_Dir(VmConfig::get('vmtemplate').DS.'images'.DS.'availability/')){
					$imagePath = VmConfig::get('vmtemplate').DS.'images'.DS.'availability/';
				} else {
					$imagePath = 'components'.DS.'com_virtuemart'.DS.'assets'.DS.'images'.DS.'availability/';
				}
				$this->assignRef('imagePath', $imagePath);

				// Load the vendors
				$vendor_model = $this->getModel('vendor');

				$vendors = $vendor_model->getVendors();
				$lists['vendors'] = JHTML::_('select.genericlist', $vendors, 'virtuemart_vendor_id', '', 'virtuemart_vendor_id', 'vendor_name', $product->virtuemart_vendor_id);

				// Load the currencies
				$currency_model = $this->getModel('currency');

				$vendor_model->setId(1);
				$vendor = $vendor_model->getVendor();
				if(empty($product->product_currency)){
					$product->product_currency = $vendor->vendor_currency;
				}
				$currencies = JHTML::_('select.genericlist', $currency_model->getCurrencies(), 'product_currency', '', 'virtuemart_currency_id', 'currency_name', $product->product_currency);
				$currency = $currency_model->getCurrency($product->product_currency);
				$this->assignRef('product_currency', $currency->currency_symbol);
				$currency = $currency_model->getCurrency($vendor->vendor_currency);
				$this->assignRef('vendor_currency', $currency->currency_symbol);

				/* Load the manufacturers */
				$mf_model = $this->getModel('manufacturer');
				$manufacturers = $mf_model->getManufacturerDropdown($product->virtuemart_manufacturer_id);

				$lists['manufacturers'] = JHTML::_('select.genericlist', $manufacturers, 'virtuemart_manufacturer_id', 'class="inputbox"', 'value', 'text', $product->virtuemart_manufacturer_id );

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
				// $related_products = $product_model->getRelatedProducts($product->virtuemart_product_id);
				// if (!$related_products) $related_products = array();
				// $lists['related_products'] = JHTML::_('select.genericlist', $related_products, 'related_products[]', 'autocomplete="off" multiple="multiple" size="10" ondblclick="removeSelectedOptions(\'related_products\')"', 'id', 'text', $related_products);

				/* Load waiting list */
				if ($product->virtuemart_product_id) {
					//$waitinglist = $this->get('waitingusers', 'waitinglist');

					if(!class_exists('VirtueMartModelWaitingList')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'waitinglist.php');
					$waitinglistmodel = new VirtueMartModelWaitingList();
					$waitinglist = $waitinglistmodel->getWaitingusers($product->virtuemart_product_id);
					$this->assignRef('waitinglist', $waitinglist);
				}

				$this->loadHelper('customhandler');
				$fieldTypes = VmCustomHandler::getField_types();
				$this->assignRef('fieldTypes', $fieldTypes);

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


				$config = JFactory::getConfig();
				$tzoffset = $config->getValue('config.offset');

				$this->assignRef('tzoffset',	$tzoffset);

				// Assign the values
				$this->assignRef('pane', $pane);
				$this->assignRef('editor', $editor);
				$this->assignRef('lists', $lists);
				$this->assignRef('product', $product);
				$this->assignRef('currencies', $currencies);
				$this->assignRef('manufacturers', $manufacturers);
				$this->assignRef('min_order', $min_order);
				$this->assignRef('max_order', $max_order);
				$this->assignRef('related_products', $related_products);
				$this->assignRef('product_child', $product_child);
				$this->assignRef('product_parent', $product_parent);
				/* Assign label values */
				$this->assignRef('action', $action);
				$this->assignRef('info_label', $info_label);
				$this->assignRef('status_label', $status_label);
				$this->assignRef('dim_weight_label', $dim_weight_label);
				$this->assignRef('images_label', $images_label);
				$this->assignRef('delete_message', $delete_message);

				// Toolbar
				$text="";
				if ($task == 'edit') {
					if ($product->product_sku) $sku=' ('.$product->product_sku.')'; else $sku="";
					$text =  $product->product_name.$sku;
				}

				ShopFunctions::SetViewTitle('vm_product_48','',$text ) ;
                                  $viewName = ShopFunctions::SetViewTitle('vm_product_48', 'PRODUCT',$text);
                                $this->assignRef('viewName', $viewName);
				ShopFunctions::addStandardEditViewCommands ();

				break;

			default:
				$model = $this->getModel();
				if ($product_parent_id=JRequest::getVar('product_parent_id',false) ) {
					$product_parent= $model->getProduct($product_parent_id);
					$title='PRODUCT_CHILDREN_LIST' ;
					$link_to_parent =  JHTML::_('link', JRoute::_('index.php?view=product&task=edit&virtuemart_product_id='.$product_parent->virtuemart_product_id.'&option='.$option), $product_parent->product_name, array('title' => JText::_('COM_VIRTUEMART_EDIT_PARENT').' '.$product_parent->product_name));
					$msg= JText::_('COM_VIRTUEMART_PRODUCT_OF'). " ".$link_to_parent;
				} else {
					$title='PRODUCT';
					$msg="";
				}
				$this->db = JFactory::getDBO();

				$viewName = ShopFunctions::SetViewTitle('vm_product_48',$title, $msg );
				/* Start model */
				$model = $this->getModel();

				/* Get the list of products */
				$productlist = $model->getProductListing(false,false,false,false);

				/* Get the category tree */
				$categoryId = JRequest::getInt('virtuemart_category_id');
//				if(!empty($categoryId)){
					$category_tree = ShopFunctions::categoryListTree(array($categoryId));
					$this->assignRef('category_tree', $category_tree);
//				}

				/* Check for child products if it is a parent item */
//				if (JRequest::getInt('product_parent_id', 0) == 0) {
//					foreach ($productlist as $virtuemart_product_id => $product) {
//						$product->haschildren = $model->checkChildProducts($virtuemart_product_id);
//					}
//				}

				// Check for Media Items and Reviews, set the price
				if(!class_exists('VirtueMartModelMedia')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'media.php');
				$media = new VirtueMartModelMedia();

				if(!class_exists('VirtueMartModelRatings')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'ratings.php');
				$productreviews = new VirtueMartModelRatings();

				/* Load the product price */
				if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');

				if(!class_exists('VirtueMartModelVendor')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'vendor.php');
				$vendor_model = new VirtueMartModelVendor();

				foreach ($productlist as $virtuemart_product_id => $product) {
					$product->mediaitems = count($product->virtuemart_media_id);
					$product->reviews = $productreviews->countReviewsForProduct($product->virtuemart_product_id);

					$vendor_model->setId($product->virtuemart_vendor_id);
					$vendor = $vendor_model->getVendor();

					$currencyDisplay = CurrencyDisplay::getInstance($vendor->vendor_currency,$vendor->virtuemart_vendor_id);

					$product->product_price_display = $currencyDisplay->priceDisplay($product->product_price,(int)$product->product_currency,true);

					/* Write the first 5 categories in the list */
					if(!class_exists('shopfunctions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'shopfunctions.php');
					$product->categoriesList = shopfunctions::renderGuiList('virtuemart_category_id','#__virtuemart_product_categories','virtuemart_product_id',$product->virtuemart_product_id,'category_name','#__virtuemart_categories','virtuemart_category_id','category');

				}

				/* Create filter */
				/* Search type */
		    	       $options = array( '' => JText::_('COM_VIRTUEMART_LIST_EMPTY_OPTION'),
		    				'product' => JText::_('COM_VIRTUEMART_PRODUCT_LIST_SEARCH_BY_DATE_TYPE_PRODUCT'),
							'price' => JText::_('COM_VIRTUEMART_PRODUCT_LIST_SEARCH_BY_DATE_TYPE_PRICE'),
							'withoutprice' => JText::_('COM_VIRTUEMART_PRODUCT_LIST_SEARCH_BY_DATE_TYPE_WITHOUTPRICE')
							);
				$lists['search_type'] = VmHTML::selectList('search_type', JRequest::getVar('search_type'),$options);

				/* Search order */
		    	        $options = array( 'bf' => JText::_('COM_VIRTUEMART_PRODUCT_LIST_SEARCH_BY_DATE_BEFORE'),
								  'af' => JText::_('COM_VIRTUEMART_PRODUCT_LIST_SEARCH_BY_DATE_AFTER')
							);
				$lists['search_order'] = VmHTML::selectList('search_order', JRequest::getVar('search_order'),$options);

				/* Toolbar */
				//JToolBarHelper::title(JText::_('COM_VIRTUEMART_PRODUCT_LIST'), 'vm_product_48');


				JToolBarHelper::custom('createchild', 'new', 'new', JText::_('COM_VIRTUEMART_PRODUCT_CHILD'), true);
				JToolBarHelper::custom('cloneproduct', 'copy', 'copy', JText::_('COM_VIRTUEMART_PRODUCT_CLONE'), true);
				JToolBarHelper::custom('addrating', 'default', '', JText::_('COM_VIRTUEMART_ADD_RATING'), true);
				ShopFunctions::addStandardDefaultViewCommands();
				$lists = array_merge($lists , ShopFunctions::addStandardDefaultViewLists($model,'product_name'));


				/* Assign the data */
				$this->assignRef('viewName', $viewName);
				$this->assignRef('productlist', $productlist);
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
	 * Renders the list for the discount rules
	 *
	 * @author Max Milbers
	 */
	function renderDiscountList($selected,$before=false){

		if(!class_exists('VirtueMartModelCalc')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'calc.php');
		$discounts = VirtueMartModelCalc::getDiscounts();
//		if($before){
//			$discounts = VirtueMartModelCalc::getDBDiscounts();
//		} else {
//			$discounts = VirtueMartModelCalc::getDADiscounts();
//		}

		$discountrates = array();
		$discountrates[] = JHTML::_('select.option', '-1', JText::_('COM_VIRTUEMART_PRODUCT_DISCOUNT_NONE'), 'product_discount_id' );
		$discountrates[] = JHTML::_('select.option', '0', JText::_('COM_VIRTUEMART_PRODUCT_DISCOUNT_NO_SPECIAL'), 'product_discount_id' );
//		$discountrates[] = JHTML::_('select.option', 'override', JText::_('COM_VIRTUEMART_PRODUCT_DISCOUNT_OVERRIDE'), 'product_discount_id');
		foreach($discounts as $discount){
			$discountrates[] = JHTML::_('select.option', $discount->virtuemart_calc_id, $discount->calc_name, 'product_discount_id');
		}
		$listHTML = JHTML::_('Select.genericlist', $discountrates, 'product_discount_id', 'multiple', 'product_discount_id', 'text', $selected );
		return $listHTML;

	}

	function displayLinkToChildList($product_id, $product_name) {

		//$this->db = JFactory::getDBO();
		$this->db->setQuery(' SELECT COUNT( * ) FROM `#__virtuemart_products` WHERE `product_parent_id` ='.$product_id);
		if ($result = $this->db->loadResult()){
		$result = JText::sprintf('COM_VIRTUEMART_X_CHILD_PRODUCT', $result);
		echo JHTML::_('link', JRoute::_('index.php?view=product&product_parent_id='.$product_id.'&option=com_virtuemart'), $result, array('title' => JText::sprintf('COM_VIRTUEMART_PRODUCT_LIST_X_CHILDREN',$product_name) ));
		}
	}

	function displayLinkToParent($product_parent_id) {

		//$this->db = JFactory::getDBO();
		$this->db->setQuery(' SELECT * FROM `#__virtuemart_products` WHERE `virtuemart_product_id` = '.$product_parent_id);
		if ($parent = $this->db->loadObject()){
		$result = JText::sprintf('COM_VIRTUEMART_LIST_CHILDREN_FROM_PARENT', $parent->product_name);
		echo JHTML::_('link', JRoute::_('index.php?view=product&product_parent_id='.$product_parent_id.'&option=com_virtuemart'), $parent->product_name, array('title' => $result));
		}
	}

}

//pure php no closing tag
