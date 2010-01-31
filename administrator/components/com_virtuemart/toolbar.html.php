<?php 
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id$
* @package VirtueMart
* @subpackage core
* @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/
$_REQUEST['keyword'] = urldecode(JRequest::getVar('keyword', ''));
$keyword = $_REQUEST['keyword'];


class TOOLBAR_virtuemart {
    
    /**
     * Function display a Joomla toolbar title and image based on thepage name. 
     * This toolbars are typically generated in the view.  Once views are complete this funciton
     * can be removed.
     *
     * @param string $pageName Name of the Virtuemart page that is being displayed.
     * @author bass28
     */
    function TITLE($pageName) {
        switch ($pageName) {
            case 'admin.show_cfg';
                JToolBarHelper::title(JText::_('VM_CONFIG'), 'vm_config_48');
                break; 
            case 'admin.module_list';
                JToolBarHelper::title(JText::_('VM_MODULE_LIST_LBL'), 'vm_module_48');
                break;     
            case 'admin.module_form';
                JToolBarHelper::title(JText::_('VM_MODULE_FORM_LBL'), 'vm_module_48');
                break;       
            case 'admin.plugin_list';
                JToolBarHelper::title(JText::_('Plugin List'), 'vm_module_48');
                break;     
            case 'admin.plugin_form';
                JToolBarHelper::title(JText::_('Plugin Details'), 'vm_module_48');
                break;                                                                  
            case 'admin.user_list';
                JToolBarHelper::title(JText::_('VM_USER_LIST_LBL'), 'vm_user_48');
                break;         
            case 'admin.user_form';
                JToolBarHelper::title(JText::_('VM_USER_FORM_LBL'), 'vm_user_48');
                break;                     
            case 'admin.usergroup_list';
                JToolBarHelper::title(JText::_('VM_USERGROUP_LIST_LBL'), 'vm_usergroup_48');
                break;   
            case 'admin.usergroup_form';
                JToolBarHelper::title(JText::_('VM_USERGROUP_FORM_LBL'), 'vm_usergroup_48');
                break;   
            case 'store.store_form';
                JToolBarHelper::title(JText::_('VM_STORE_FORM_LBL'), 'vm_store_48');
                break;    
            case 'store.payment_method_list';
                JToolBarHelper::title(JText::_('VM_PAYMENT_METHOD_LIST_LBL'), 'vm_payment_48');
                break;   
            case 'store.payment_method_form';
                JToolBarHelper::title(JText::_('VM_PAYMENT_METHOD_FORM_LBL'), 'vm_payment_48');
                break;          
            case 'store.shipping_module_list';
                JToolBarHelper::title(JText::_('Plugin List'), 'vm_module_48');
                break;     
            case 'store.shipping_module_form';
                JToolBarHelper::title(JText::_('Plugin Details'), 'vm_module_48');
                break;      
            case 'store.creditcard_list';
                JToolBarHelper::title(JText::_('VM_CREDITCARD_LIST_LBL'), 'vm_credit_48');
                break;        
            case 'store.creditcard_form';
                JToolBarHelper::title(JText::_('VM_CREDITCARD_FORM_LBL'), 'vm_credit_48');
                break;                                                                                                               
            case 'product.product_list';
                JToolBarHelper::title(JText::_('Product List'), 'vm_product_48');
                break;
            case 'product.product_form';
                JToolBarHelper::title(JText::_('Update Item'), 'vm_product_48');
                break;
            case 'product.product_category_list';
                JToolBarHelper::title(JText::_('VM_CATEGORY_LIST_LBL'), 'vm_categories_48');
                break;
            case 'product.product_category_form';
                JToolBarHelper::title(JText::_('VM_CATEGORY_FORM_LBL'), 'vm_categories_48');
                break;
            case 'product.file_list';
                JToolBarHelper::title(JText::_('VM_FILES_LIST'), 'vm_product_files_48');
                JToolBarHelper::EditListX();
                break;
            case 'admin.country_list';
                JToolBarHelper::title(JText::_('VM_COUNTRY_LIST_LBL'), 'vm_countries_48');
                break;
            case 'admin.country_form';
                JToolBarHelper::title(JText::_('VM_COUNTRY_LIST_ADD'), 'vm_countries_48');
                break;  
            case 'admin.curr_list';
                JToolBarHelper::title(JText::_('VM_CURRENCY_LIST_LBL'), 'vm_currency_48');
                break;
            case 'admin.curr_form';
                JToolBarHelper::title(JText::_('VM_CURRENCY_LIST_ADD'), 'vm_currency_48');
                break;  
            case 'order.order_list';
                JToolBarHelper::title(JText::_('VM_ORDER_LIST_LBL'), 'vm_orders_48');
                break;  
            case 'order.order_status_list';
                JToolBarHelper::title(JText::_('VM_ORDER_STATUS_LIST_MNU'), 'vm_orders_48');
                break;        
            case 'order.order_status_form';
                JToolBarHelper::title(JText::_('VM_ORDER_STATUS_FORM_LBL'), 'vm_orders_48');
                break;  
            case 'vendor.vendor_list';
                JToolBarHelper::title(JText::_('VM_VENDOR_LIST_LBL'), 'vm_vendors_48');
                break;        
            case 'vendor.vendor_status_form';
                JToolBarHelper::title(JText::_('VM_VENDOR_FORM_LBL'), 'vm_vendors_48');
                break;    
            case 'tax.tax_list';
                JToolBarHelper::title(JText::_('VM_TAX_LIST_LBL'), 'vm_tax_48');
                break;        
            case 'tax.tax_form';
                JToolBarHelper::title(JText::_('VM_TAX_FORM_LBL'), 'vm_tax_48');
                break;   
            case 'shipping.carrier_list';
                JToolBarHelper::title(JText::_('VM_CARRIER_LIST_LBL'), 'vm_ups_48');
                break;        
            case 'shipping.carrier_form';
                JToolBarHelper::title(JText::_('VM_CARRIER_FORM_LBL'), 'vm_ups_48');
                break;     
            case 'shipping.rate_list';
                JToolBarHelper::title(JText::_('VM_RATE_LIST_LBL'), 'vm_shipping_rates_48');
                break;        
            case 'shipping.rate_form';
                JToolBarHelper::title(JText::_('VM_RATE_FORM_LBL'), 'vm_shipping_rates_48');
                break;                    
            case 'shopper.shopper_group_list';
                JToolBarHelper::title(JText::_('VM_SHOPPER_GROUP_LIST_LBL'), 'vm_shop_users_48');
                break;        
            case 'shopper.shopper_group_form';
                JToolBarHelper::title(JText::_('VM_SHOPPER_GROUP_FORM_LBL'), 'vm_shop_users_48');
                break;     
            case 'manufacturer.manufacturer_list';
                JToolBarHelper::title(JText::_('VM_MANUFACTURER_LIST_LBL'), 'vm_manufacturer_48');
                break;        
            case 'manufacturer.manufacturer_form';
                JToolBarHelper::title(JText::_('VM_MANUFACTURER_FORM_LBL'), 'vm_manufacturer_48');
                break;                                                                                                                                                    
            default:
                JToolBarHelper::title(JText::_($pageName), 'vm_logo_48');
                break;
        }
    }
    
	/**
	* The function to handle all default page situations
	* not responsible for lists!
	*/
    function FORMS_MENU_SAVE_CANCEL() {     
        global $page, $limitstart;
		$no_menu = (int)$_REQUEST['no_menu'];
		//$bar=& JToolBar::getInstance( 'toolbar' );
		//$bar = & vmToolBar::getInstance('virtuemart');		
        
        $is_iframe = JRequest::getVar( 'is_iframe', 0 );
        $product_parent_id = JRequest::getVar(  'product_parent_id', 0 );
        $product_id = JRequest::getVar(  'product_id' );
        
        //bass28 6/12/09 - Need $limitstart in our controller
        JRequest::getVar('limitstart', $limitstart);
        
        $script = '';
        
		if( is_array( $product_id )) {
			$product_id = "";
		}
		
		// These editor arrays tell the toolbar to load correct "getEditorContents" script parts
		// This is necessary for WYSIWYG Editors like TinyMCE / mosCE / FCKEditor
        $editor1_array = Array('product.product_form' => 'product_desc', 'shopper.shopper_group_form' => 'shopper_group_desc',
								'product.product_category_form' => 'category_description', 'manufacturer.manufacturer_form' => 'mf_desc',
								'store.store_form' => 'vendor_store_desc',
								'product.product_type_parameter_form' => 'parameter_description',
								'product.product_type_form' => 'product_type_description',
								'vendor.vendor_form' => 'vendor_store_desc');
        $editor2_array = Array('store.store_form' => 'vendor_terms_of_service',
								'vendor.vendor_form' => 'vendor_terms_of_service');
		
		$editor1 = isset($editor1_array[$page]) ? $editor1_array[$page] : '';
		$editor2 = isset($editor2_array[$page]) ? $editor2_array[$page] : '';
		if( $no_menu ) {
			vmCommonHTML::loadExtJS();
		}
		$script .= '
var submitbutton = function(pressbutton){
	
	var form = document.adminForm;
	if (pressbutton == \'cancel\') {
		submitform( pressbutton );
		return;
	}	
';
        
    	if ($editor1 != '') {
			jimport('joomla.html.editor');
			$editor_type = $GLOBALS['mainframe']->getCfg('editor');
			if( $editor_type != 'none' ) {
				$editor = JEditor::getInstance();
				$script .= $editor->getContent($editor1);
			}
		}
		if ($editor2 != '') {
			jimport('joomla.html.editor');
			$editor_type = $GLOBALS['mainframe']->getCfg('editor');
			if( $editor_type != 'none' ) {
				$editor = JEditor::getInstance();
				$script .= $editor->getContent($editor2);
			}
		}
		if( $no_menu ) {
			$admin = defined('_VM_IS_BACKEND') ? '/administrator' : '';
			$script .= "
    // define some private variables
    var dialog, showBtn;

    var showDialog = function( content ) {
    	Ext.Msg.show( { 
            		title: '" . JText::_('PEAR_LOG_NOTICE') . "',
            		msg: content,
            		autoCreate: true,
                    width:400,
                    height:180,
                    modal: false,
                    resizable: false,
                    buttons: Ext.Msg.OK,
                    shadow:true,
                    animEl:Ext.get( 'vm-toolbar' )
            });
        ".(DEBUG ? "" : "setTimeout('Ext.Msg.hide()', 90000);")."
    };
    
    // return a public interface
    var onSuccess = function(o,c) {
		showDialog( o.responseText );
	};
    var onFailure = function(o) {
		Ext.Msg.alert( 'Error!', 'Save action failed: ' + o.statusText );
	};
	var onCallback=function(o,s,r) {
		//if( s ) alert( 'Success' );
		//else alert( 'Failure' );
	}
	
   	Ext.Ajax.request( { method: 'POST',
   						url: '{$_SERVER['PHP_SELF']}',
   						success: onSuccess,
   						failure: onFailure,
   						callback: onCallback,
   						isUpload: true,
   						form: document.adminForm,
   						params: { no_html:1 }
   						}
   					);
	";

		}
		else {
			$script .= "\n\t\t\tsubmitform( pressbutton );\n";
		}
		
		$script .= "\t\t}\n";
		
		// bass28 - 6/2/09 Move to Joomla toolbar
        //$vm_mainframe->addScriptDeclaration($script);		
		
		TOOLBAR_virtuemart::TITLE($page);
		if ($page == "product.product_form" && !empty($product_id)) {
			if( empty($product_parent_id) ) { 
				// add new attribute
				//$href=$_SERVER['PHP_SELF']."?option=com_virtuemart&page=product.product_attribute_form&product_id=". $product_id ."&limitstart=". $limitstart."&no_menu=$no_menu";
				$alt =  JText::_('VM_ATTRIBUTE_FORM_MNU');
				
				// bass28 - 6/2/09 Move to Joomla toolbar
				//$bar->customHref( $href, 'new', $alt );
				JToolBarHelper::custom('redirectToAddProductAttributeForm', 'new', 'new', $alt, false, false);		
			}
			else {
                // back to parent product
				//$href=$_SERVER['PHP_SELF']."?option=com_virtuemart&page=product.product_form&product_id=$product_parent_id&limitstart=".$limitstart."&no_menu=$no_menu";
				$alt =  JText::_('VM_PRODUCT_FORM_RETURN_LBL');
				// bass28 - 6/2/09 Move to Joomla toolbar
				//$bar->customHref( $href, $vmIcons['back_icon'], $vmIcons['back_icon2'], $alt );
				JToolBarHelper::back('back', $href);
				JToolBarHelper::custom('redirectToParentProductForm', 'back_icon', 'back_icon2', $alt, false, false);
				
				// new child product
				//$href=$_SERVER['PHP_SELF']."?option=com_virtuemart&page=product.product_form&product_parent_id=$product_parent_id&limitstart=". $limitstart."&no_menu=$no_menu";
				$alt =  JText::_('VM_PRODUCT_FORM_ADD_ANOTHER_ITEM_MNU');
				// bass28 - 6/2/09 Move to Joomla toolbar
				//$bar->customHref( $href, 'new', $alt );
				JToolBarHelper::addNew('new', $alt);
				JToolBarHelper::custom('redirectToAddChildProductForm', 'new', 'new', $alt, false, false);
				
			} 
			// Go to Price list
			//$href = $_SERVER['PHP_SELF']."?page=product.product_price_list&product_id=$product_id&product_parent_id=$product_parent_id&limitstart=$limitstart&return_args=&option=com_virtuemart&no_menu=$no_menu";
			$alt =  JText::_('VM_PRICE_LIST_MNU');
			// bass28 - 6/2/09 Move to Joomla toolbar
			//$bar->customHref( $href, 'new', $alt );
			JToolBarHelper::custom('redirectToProductPriceList', 'preview', 'preview', $alt, false, false);
			
	
			// add product type
			//$href= $_SERVER['PHP_SELF']."?option=com_virtuemart&page=product.product_product_type_form&product_id=$product_id&product_parent_id=$product_parent_id&limitstart=$limitstart&no_menu=$no_menu";
			$alt =  JText::_('VM_PRODUCT_PRODUCT_TYPE_FORM_MNU');
			// bass28 - 6/2/09 Move to Joomla toolbar
			//$bar->customHref( $href, 'new', $alt );
			//JToolBarHelper::addNew('new', $alt);
			JToolBarHelper::custom('redirectToAddProductTypeForm', 'new', 'new', $alt, false, false);
			
			
			/*** Adding an item is only pssible, if the product has attributes ***/
			if (ps_product::product_has_attributes( $product_id ) ) { 
				// Add Item
				//$href=$_SERVER['PHP_SELF']."?option=com_virtuemart&page=product.product_form&product_parent_id=$product_id&limitstart=$limitstart&no_menu=$no_menu";
				$alt =  JText::_('VM_PRODUCT_FORM_NEW_ITEM_LBL');
				// bass28 - 6/2/09 Move to Joomla toolbar
				//$bar->customHref( $href, 'new', $alt );
				//JToolBarHelper::addNew('new', $alt);
				JToolBarHelper::custom('redirectToAddChildProductForm', 'new', 'new', $alt, false, false);
				
			}
			// bass28 - 6/2/09 Move to Joomla toolbar
			//$bar->divider();
			JToolBarHelper::divider();
		}
		elseif( $page == "admin.country_form" ) {
            if( !empty( $_REQUEST['country_id'] )) {
				$href= $_SERVER['PHP_SELF'] ."?option=com_virtuemart&page=admin.country_state_form&country_id=". intval($_REQUEST['country_id']) ."&limitstart=$limitstart&no_menu=$no_menu";
				//$alt = JText::_('VM_ADD_STATE');
				// bass28 - 6/2/09 Move to Joomla toolbar
				//$bar->customHref( $href, 'new', $alt );		
				JToolBarHelper::custom('redirectToCountryStateForm', 'new', 'new', JText::_('VM_ADD_STATE'), false, false);		
				
				$href = $_SERVER['PHP_SELF'] ."?option=com_virtuemart&page=admin.country_state_list&country_id=". intval($_REQUEST['country_id']) ."&limitstart=$limitstart&no_menu=$no_menu";
				//$alt = JText::_('VM_LIST_STATES');
				// bass28 - 6/2/09 Move to Joomla toolbar
				//$bar->customHref( $href, 'new', $alt );		
				//$bar->divider();
				JToolBarHelper::custom('redirectToCountryStateList', 'preview', 'preview', JText::_('VM_LIST_STATES'), false, false);					
				JToolBarHelper::divider();
			}
		}
		
		// bass28 - 6/2/09 Move to Joomla toolbar
		//$bar->save( 'save', JText::_('CMN_SAVE') );						
		//$bar->apply( 'apply', JText::_('E_APPLY') );        
		//$bar->cancel();				
		JToolBarHelper::save('save', JText::_('CMN_SAVE'));
		JToolBarHelper::apply('apply', JText::_('E_APPLY'));
		JToolBarHelper::cancel();
		
    }
    /**
	* The function for all page which allow adding new items
	* usually when page= *.*_list
	*/
    function LISTS_MENU_NEW() {
        global $page,  $limitstart;
        
        TOOLBAR_virtuemart::TITLE($page);
		//$bar = & vmToolBar::getInstance('virtuemart');
        //$my_page = str_replace('list','form',$page);		        
        //$bar->addNew( "new", $my_page, JText::_('CMN_NEW') );
        JToolBarHelper::custom('redirectToEditPage', 'new', 'new', JText::_('CMN_NEW'), false, false);
		
//        if ($page == 'admin.country_state_list' && vmGet( $_SESSION, 'vmLayout', 'extended' ) == 'standard') {
		//TODO We dont have a extended view anylonger so this can be simplified
        if ($page == 'admin.country_state_list') {
            // bass28 - 6/2/09 Move to Joomla toolbar
			// Back to the country
			//$bar->divider();
			//$href = $_SERVER['PHP_SELF']. '?option=com_virtuemart&page=admin.country_list';
			//$bar->customHref( $href, 'back', '&nbsp;'.JText::_('VM_BACK_TO_COUNTRY') );
			JToolBarHelper::divider();
			JToolBarHelper::custom('redirectToCountryList', 'back', 'back', JText::_('VM_BACK_TO_COUNTRY'), false, false);
        }
        elseif ($page == 'product.file_list') {
            // bass28 - 6/2/09 Move to Joomla toolbar
			// Close the window
			//$bar->divider();
			//$bar->cancel();
			JToolBarHelper::divider();
			JToolBarHelper::cancel();
        }
   
        
    }
	/**
	* Draws a list publish button
	*/
    function LISTS_MENU_PUBLISH( $funcName ) {
		//$bar = & vmToolBar::getInstance('virtuemart');
		//$bar->publishList( $funcName );
		
		//$bar->unpublishList( $funcName );
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		
	}
	/**
	* Draws a list delete button
	*/
    function LISTS_MENU_DELETE( $funcName ) {
		//$bar = & vmToolBar::getInstance('virtuemart');
		//$bar->deleteList( $funcName );		
		JToolBarHelper::deleteList();
	}
	
	/** 
	* Handles special task selectors for pages
	* like the product list
	*/
	function LISTS_SPECIAL_TASKS( $page ) {
		global $product_id;
		
		//$bar = & vmToolBar::getInstance('virtuemart');
		TOOLBAR_virtuemart::TITLE($page);
		switch( $page ) {
		
			case "product.product_list":
			
				if( empty($_REQUEST['product_parent_id']) ) { 
					// add new attribute
					//$alt =  JText::_('VM_ATTRIBUTE_FORM_MNU');
					//$bar->custom( 'new', "product.product_attribute_form", 'new', $alt );
					JToolBarHelper::custom('redirectToAddProductAttributeForm', 'new', 'new', JText::_('VM_ATTRIBUTE_FORM_MNU'), false, false);	
					
				}
				// Go to Price list
				//$alt =  JText::_('VM_PRICE_LIST_MNU');
				//$bar->custom( 'new', "product.product_price_list", 'new', $alt );	
				JToolBarHelper::custom('redirectToProductPriceList', 'preview', 'preview', JText::_('VM_PRICE_LIST_MNU'), false, false);			
		
				// add product type
				//$alt =  JText::_('VM_PRODUCT_PRODUCT_TYPE_FORM_MNU');
				//$bar->custom( 'new', "product.product_product_type_form", 'new', $alt );	
				JToolBarHelper::custom('redirectToAddProductTypeForm', 'new', 'new', JText::_('VM_PRODUCT_PRODUCT_TYPE_FORM_MNU'), false, false);		
		
				/*** Adding an item is only pssible, if the product has attributes ***/
				if (ps_product::product_has_attributes( $product_id ) ) { 
					// Add Item
					//$alt =  JText::_('VM_PRODUCT_FORM_NEW_ITEM_LBL');
					//$bar->custom( 'new', "product.product_child_form", 'new', $alt );
					JToolBarHelper::custom('redirectToAddChildProductForm', 'new', 'new', JText::_('VM_PRODUCT_FORM_NEW_ITEM_LBL'), false, false);
				}
				//$bar->divider();
				JToolBarHelper::divider();
				
				if( !empty( $_REQUEST['category_id'])) {
					$alt = JText::_('VM_PRODUCTS_MOVE_TOOLBAR');
//					$bar->custom( 'move', 'product.product_move', 'move', $alt );
					
					//$bar->divider();
					JToolBarHelper::divider();
				}
				break;
			
			case "admin.country_list":
					//$alt = JText::_('VM_ADD_STATE');
					//$bar->custom( 'new', "admin.country_state_form", 'new', $alt );	
					//JToolBarHelper::custom('redirectToCountryStateForm', 'new', 'new', JText::_('VM_ADD_STATE'), false, false);				
					
					//$alt = JText::_('VM_LIST_STATES');
					//$bar->custom( 'new', "admin.country_state_list", 'new', $alt );
					//JToolBarHelper::customX('redirectToCountryStateList', 'new', 'new', JText::_('VM_LIST_STATES'), false, false);
					
					//$bar->divider();
					JToolBarHelper::divider();
				break;			
		} 
		
	}
	
	
	/**
	* Draws the menu for a New users
	*/
	function _NEW_USERS() {
	    // bass28 6/15/09 - Move to Joomla toolbar
		//$bar = & vmToolBar::getInstance('virtuemart');
		//$bar->save();
		//$bar->cancel();
		JToolBarHelper::title(JText::_('VM_COUNTRY_LIST_ADD'), 'countries_48');
		JToolBarHelper::save();
	    JToolBarHelper::cancel();
	}
	
	function _EDIT_USERS() {
	    // bass28 6/15/09 - Move to Joomla toolbar
		//$bar = & vmToolBar::getInstance('virtuemart');
		//$bar->save();
		//$bar->cancel();
		JToolBarHelper::save();
	    JToolBarHelper::cancel();
	}
	
	function _DEFAULT_USERS() {
		$bar = & vmToolBar::getInstance('virtuemart');
		
		//$bar->addNew();
		//$bar->editList();
		//$bar->deleteList();
		
		$bar->custom( 'remove_as_customer', 'admin.user_list', 'remove', 'Remove as Customer' );
		//JToolBarHelper::custom('remove_as_customer', 'back', 'remove', JText::_('Remove as Customer'), false, false);	
		
		JToolBarHelper::addNew();
		JToolBarHelper::editList();
		JToolBarHelper::deleteList();	
	}
}
