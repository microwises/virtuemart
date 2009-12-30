<?php
/**
* @package		VirtueMart
*/

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the VirtueMart Component
 *
 * @package		VirtueMart
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
		require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'htmlTools.class.php');
		
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
				$product = $this->get('Product');
				
				/* Get the category tree */
				if (isset($product->categories)) $category_tree = ShopFunctions::categoryListTree('', 0, 0, $product->categories);
				else $category_tree = ShopFunctions::categoryListTree();
				$this->assignRef('category_tree', $category_tree);
				
				/* Load the product price */
				$product_price = $this->get('ProductPrice');
				
				/* Load the currencies */
				$currency_model = $this->getModel('currency');
				$currencies = JHTML::_('select.genericlist', $currency_model->getCurrencies(), 'product_currency', '', 'currency_code', 'currency_name', $product->product_currency);
				
				/* Load the tax rates */
				$tax_model = $this->getModel('taxRate');
				$taxrates = $tax_model->getTaxRates();
				$lists['taxrates'] = JHTML::_('select.genericlist', $taxrates, 'product_tax_id', '"updateGross();"', 'tax_rate_id', 'select_list_name', $product->product_tax_id);
				
				/* Load the tax rates */
				$discount_model = $this->getModel('discount');
				$discounts = $discount_model->getDiscounts();
				$discounts[] = JHTML::_('select.option', 'override', JText::_('VM_PRODUCT_DISCOUNT_OVERRIDE'), 'discount_id', 'amount');
				$lists['discounts'] = JHTML::_('select.genericlist', $discounts, 'product_discount_id', 'onchange="updateDiscountedPrice();"', 'discount_id', 'amount', $product->product_discount_id);
				
				/* Load the vendors */
				$vendor_model = $this->getModel('vendor');
				$vendors = $vendor_model->getVendors();
				$lists['vendors'] = JHTML::_('select.genericlist', $vendors, 'vendor_id', '', 'vendor_id', 'vendor_name', $product->vendor_id);				
				/* Load the manufacturers */
				$mf_model = $this->getModel('manufacturer');
				$manufacturers = $mf_model->getManufacturerDropdown($product->manufacturer_id);
				$lists['manufacturers'] = JHTML::_('select.genericlist',   $manufacturers, 'mf_category_id', 'class="inputbox" onchange="this.form.submit()"', 'value', 'text', $product->manufacturer_id );
				
				/* Load the attribute names */
				$product->attribute_names = $this->get('ProductAttributeNames');
				
				/* Load the attribute values */
				$product->attribute_values = $this->get('ProductAttributeValues');
				
				/* Load the child products */
				if ($product->product_id > 0 && $product->product_parent_id == 0) {
					$product->child_products = $product_model->getChildAttributes($product->product_id);
				}
				else $product->child_products = null;
				
				/* Get the minimum and maximum order levels */
				$min_order = 0;
				$max_order = 0;
				if(strstr($product->product_order_levels, ',')) {
					$order_levels = explode(',', $product->product_order_levels);
					$min_order = $order_levels[0];
					$max_order = $order_levels[1];
				}
				
				/* Get the related products */
				$related_products = $product_model->getRelatedProducts($product->product_id);
				if (!$related_products) $related_products = array();
				$lists['related_products'] = JHTML::_('select.genericlist', $related_products, 'related_products[]', 'autocomplete="off" multiple="multiple" size="10" ondblclick="removeSelectedOptions(\'related_products\')"', 'id', 'text', $related_products);
				
				/* Get the parent settings */
				$product->desc_width = '20%';
				$product->attrib_width = '10%';
				$product->display_use_parent_disabled = false;
				if($product->product_parent_id !=0) {
					$product->display_use_parent_disabled = true;
				}
				if (is_null($product->child_options)) {
					$product->child_options = 'N,N,N,N,N,Y,20%,10%,,`#__{vm}_product`.`product_sku`';
				}
				list($product->display_use_parent, 
					$product->product_list,
					$product->display_header,
					$product->product_list_child,
					$product->product_list_type,
					$product->display_desc,
					$product->desc_width,
					$product->attrib_width,
					$product->child_class_sfx,
					$product->child_order_by) = explode(',', $product->child_options);
				/* Get the quantity types */
				if (is_null($product->quantity_options)) {
					$product->quantity_options = 'none,0,0,1';
				}
				list($product->display_type, 
					$product->quantity_start,
					$product->quantity_end,
					$product->quantity_step) = explode(',', $product->quantity_options);
				
				/* Set some default values */
				if (is_null($product->desc_width)) $product->desc_width = '20%';
				if (is_null($product->attrib_width)) $product->attrib_width = '10%';
				if (is_null($product->display_type)) $product->display_type = 'none';
				if (is_null($product->quantity_start)) $product->quantity_start = 0;
				if (is_null($product->quantity_end)) $product->quantity_end = 0;
				if (is_null($product->quantity_step)) $product->quantity_step = 1;
				
				/* Load waiting list */
				if ($product->product_id) {
					$waitinglist = $this->get('waitingusers', 'waitinglist');
					$this->assignRef('waitinglist', $waitinglist);
				}
				
				/* Set up labels */
				if ($product->product_parent_id > 0) {
					$info_label = JText::_('VM_PRODUCT_FORM_ITEM_INFO_LBL');
					$status_label = JText::_('VM_PRODUCT_FORM_ITEM_STATUS_LBL');
					$dim_weight_label = JText::_('VM_PRODUCT_FORM_ITEM_DIM_WEIGHT_LBL');
					$images_label = JText::_('VM_PRODUCT_FORM_ITEM_IMAGES_LBL');
					$display_label = JText::_('VM_PRODUCT_FORM_ITEM_IMAGES_LBL');
					$delete_message = JText::_('VM_PRODUCT_FORM_DELETE_ITEM_MSG');
				}
				else {
					if ($task == 'add') $action = JText::_('VM_PRODUCT_FORM_NEW_PRODUCT_LBL');
					else $action = JText::_('VM_PRODUCT_FORM_UPDATE_ITEM_LBL');
					
					$info_label = JText::_('VM_PRODUCT_FORM_PRODUCT_INFO_LBL');
					$status_label = JText::_('VM_PRODUCT_FORM_PRODUCT_STATUS_LBL');
					$dim_weight_label = JText::_('VM_PRODUCT_FORM_PRODUCT_DIM_WEIGHT_LBL');
					$images_label = JText::_('VM_PRODUCT_FORM_PRODUCT_IMAGES_LBL');
					$delete_message = JText::_('VM_PRODUCT_FORM_DELETE_PRODUCT_MSG');
				}
				
				/* Assign the values */
				$this->assignRef('pane', $pane);
				$this->assignRef('editor', $editor);
				$this->assignRef('lists', $lists);
				$this->assignRef('product', $product);
				$this->assignRef('currencies', $currencies);
				$this->assignRef('manufacturers', $manufacturers);
				$this->assignRef('taxrates', $taxrates);
				$this->assignRef('discounts', $discounts);
				$this->assignRef('min_order', $min_order);
				$this->assignRef('max_order', $max_order);
				$this->assignRef('related_products', $related_products);
				
				/* Assign label values */
				$this->assignRef('action', $action);
				$this->assignRef('info_label', $info_label);
				$this->assignRef('status_label', $status_label);
				$this->assignRef('dim_weight_label', $dim_weight_label);
				$this->assignRef('images_label', $images_label);
				$this->assignRef('display_label', $display_label);
				$this->assignRef('delete_message', $delete_message);
				
				/* Toolbar */
				if ($task == 'add') $text = JText::_( 'ADD_PRODUCT' );
				else $text = JText::_( 'EDIT_PRODUCT' ).' :: '.$product->product_sku.' :: '.$product->product_name;
				JToolBarHelper::title($text, 'vm_product_48');
				JToolBarHelper::save();
				JToolBarHelper::cancel();
				break;
			case 'addproducttype':
				/* Get the product types that can be chosen */
				$producttypes = JHTML::_('select.genericlist', $this->get('ProductTypeList'), 'product_type_id');
				$this->assignRef('producttypes', $producttypes);
				
				/* Get the product */
				$product = $this->get('ProductDetails');
				$this->assignRef('product', $product);
				
				/* Toolbar */
				$text = JText::_( 'VM_PRODUCT_PRODUCT_TYPE_FORM_LBL' ).' :: '.$product->product_sku.' :: '.$product->product_name;
				JToolBarHelper::title($text, 'vm_product_48');
				JToolBarHelper::save('saveproducttype');
				JToolBarHelper::cancel();
				break;
			default:
				switch ($task) {
					case 'publish':
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
				$category_tree = ShopFunctions::categoryListTree( JRequest::getInt('category_id') );
				$this->assignRef('category_tree', $category_tree);
				
				/* Check for child products if it is a parent item */
				if (JRequest::getInt('product_parent_id', 0) == 0) {
					foreach ($productlist as $product_id => $product) {
						$product->haschildren = $model->checkChildProducts($product_id);
					}
				}
				
				/* Check for Media Items and Reviews, set the price*/
				$media = new VirtueMartModelMedia();
				$productreviews = new VirtueMartModelRatings();
				$currencydisplay = new CurrencyDisplay();
				foreach ($productlist as $product_id => $product) {
					$product->mediaitems = $media->countFilesForProduct($product_id);
					$product->reviews = $productreviews->countReviewsForProduct($product_id);
					$product->product_price_display = $currencydisplay->getValue($product->product_price);
				}
				
				/* Get the pagination */
				$pagination = $this->get('Pagination');
				$lists['filter_order'] = $mainframe->getUserStateFromRequest($option.'filter_order', 'filter_order', '', 'cmd');
				$lists['filter_order_Dir'] = $mainframe->getUserStateFromRequest($option.'filter_order_Dir', 'filter_order_Dir', '', 'word');
				
				/* Create filter */
				/* Search type */
				$options = array();
				$options[] = JHTML::_('select.option', '', JText::_('SELECT'));
				$options[] = JHTML::_('select.option', 'product', JText::_('VM_PRODUCT_LIST_SEARCH_BY_DATE_TYPE_PRODUCT'));
				$options[] = JHTML::_('select.option', 'price', JText::_('VM_PRODUCT_LIST_SEARCH_BY_DATE_TYPE_PRICE'));
				$options[] = JHTML::_('select.option', 'withoutprice', JText::_('VM_PRODUCT_LIST_SEARCH_BY_DATE_TYPE_WITHOUTPRICE'));
				$lists['search_type'] = JHTML::_('select.genericlist', $options, 'search_type', '', 'value', 'text', JRequest::getVar('search_type'));
				
				/* Search order */
				$options = array();
				$options[] = JHTML::_('select.option', 'bf', JText::_('VM_PRODUCT_LIST_SEARCH_BY_DATE_BEFORE'));
				$options[] = JHTML::_('select.option', 'af', JText::_('VM_PRODUCT_LIST_SEARCH_BY_DATE_AFTER'));
				$lists['search_order'] = JHTML::_('select.genericlist', $options, 'search_order', '', 'value', 'text', JRequest::getVar('search_order'));
				
				/* Toolbar */
				JToolBarHelper::title(JText::_( 'PRODUCT_LIST' ), 'vm_product_48');
				JToolBarHelper::custom('addattribute', 'icon-32-new', '', JText::_('ADD_ATTRIBUTE'), true);
				JToolBarHelper::custom('addproducttype', 'icon-32-new', '', JText::_('ADD_PRODUCT_TYPE'), true);
				JToolBarHelper::custom('addrating', 'icon-32-new', '', JText::_('ADD_RATING'), true);
				JToolBarHelper::divider();
				JToolBarHelper::publish();
				JToolBarHelper::unpublish();
				JToolBarHelper::custom('cloneproduct', 'virtuemart_clone_32', 'virtuemart_clone_32', JText::_('VM_PRODUCT_CLONE'), true);
				JToolBarHelper::deleteListX();
				JToolBarHelper::addNew();
				
				/* Assign the data */
				$this->assignRef('productlist', $productlist);
				$this->assignRef('pagination',	$pagination);
				$this->assignRef('lists', $lists);
				break;
		}
		
		parent::display($tpl);
	}
	
}
?>
