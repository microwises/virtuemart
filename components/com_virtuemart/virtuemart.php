<?php 
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: virtuemart.php 1760 2009-05-03 22:58:57Z Aravot $
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
/* Going for a new look :) */

/* Require the base controller */
require_once(JPATH_COMPONENT.DS.'controller.php');
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'config.php');
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'permissions.php');
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'shoppergroup.php');
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'shopfunctions.php');
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'calculationh.php');

/* Load the permissions */
Permissions::doAuthentication();

/* Require specific controller if requested */
if($controller = JRequest::getVar('view', 'virtuemart')) {
   require_once (JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php');
}

/* Create the controller */
$classname   = 'VirtuemartController'.$controller;
$controller = new $classname();

/* Perform the Request task */
$controller->execute(JRequest::getVar('task', JRequest::getVar('view')));

/* Redirect if set by the controller */
$controller->redirect();


if (0) {
global $mosConfig_absolute_path, $product_id, $vmInputFilter, $vmLogger;
        
/* Load the virtuemart main parse code */
require_once( dirname(__FILE__) . '/virtuemart_parser.php' );

$my_page= explode ( '.', $page );
$modulename = $my_page[0];
$pagename = $my_page[1];

$is_popup = vmRequest::getBool( 'pop' );

// Page Navigation Parameters
//$limit = intval( $vm_mainframe->getUserStateFromRequest( "viewlistlimit{$page}", 'limit', $mosConfig_list_limit ) );
//$limitstart = intval( $vm_mainframe->getUserStateFromRequest( "view{$keyword}{$category_id}{$pagename}limitstart", 'limitstart', 0 )) ;
$limit = intval( $mainframe->getUserStateFromRequest( "viewlistlimit{$page}", 'limit', $mosConfig_list_limit ) );
$limitstart = intval( $mainframe->getUserStateFromRequest( "view{$keyword}{$category_id}{$pagename}limitstart", 'limitstart', 0 )) ;

/* Get all the other paramters */
$search_category= vmRequest::getVar( 'search_category' );
// Display just the naked page without toolbar, menu and footer?
$only_page = vmRequest::getInt('only_page', 0 );

if( PSHOP_IS_OFFLINE == '1' && !$perm->hasHigherPerms('storeadmin') ) {
    echo PSHOP_OFFLINE_MESSAGE;
}
else {
	if( PSHOP_IS_OFFLINE == '1' ) {
		echo '<h2>'.JText::_('OFFLINE_MODE').'</h2>';
	}
	if( $is_popup ) {
		echo "<style type='text/css' media='print'>.vmNoPrint { display: none }</style>";
		echo vmCommonHTML::PrintIcon('', true, ' '.JText::_('CMN_PRINT') );
	}
	
	// The Vendor ID is important senseless to set the vendor this way.
	// The vendor must be set by visited page or product by Max Milbers
//	$hVendor_id = $_SESSION['ps_vendor_id'];

	// The authentication array
	$auth = $_SESSION['auth'];
	$no_menu = vmRequest::getInt('no_menu', 0 );

	// Timer Start
	if ( vmShouldDebug() ) { /*@MWM1: Log/Debug enhancements */
		$start = utime();
		$GLOBALS["mosConfig_debug"] = 1;
	}

	// update the cart because something could have
	// changed while running a function
	$cart = $_SESSION["cart"];


	if (( !$pagePermissionsOK || !$funcParams ) && $_REQUEST['page'] != 'checkout.index') {

		if( !$pagePermissionsOK && defined('_VM_PAGE_NOT_AUTH') ) {
			$page = 'checkout.login_form';
			echo '<br/><br/>'.JText::_('DO_LOGIN').'<br/><br/>';
		}
		elseif( !$pagePermissionsOK && defined('_VM_PAGE_NOT_FOUND') ) {
			$page = HOMEPAGE;
		}
		else {
			$page = $_SESSION['last_page'];
		}
	}

	$my_page= explode ( '.', $page );
	$modulename = $my_page[0];
	$pagename = $my_page[1];

	// For there's no errorpage to display the error,
	// we must echo it before the page is loaded
	if (!empty($error) && $page != ERRORPAGE) {
		echo '<span class="shop_error">'.$error.'</span>';
	}

	/*****************************
	** FRONTEND ADMIN - MOD
	**/
	if ( vmIsAdminMode()
		&& $perm->check("admin,storeadmin")
		&& ((!stristr($my->usertype, "admin") ^ PSHOP_ALLOW_FRONTENDADMIN_FOR_NOBACKENDERS == '' )
			|| stristr($my->usertype, "admin")
			)
		&& !stristr($page, "shop.")
	) {
		
		$task = JRequest::getVar('task', null);
		switch( $task ) {
			case 'extlayout':
				include( $mosConfig_absolute_path.'/components/'.$option.'/js/extlayout.js.php');
				exit;
		}
		$only_page_default = strstr( $_SERVER['PHP_SELF'], 'index3.php') ? 1 : 0;
		$only_page = $_REQUEST['only_page'] = JRequest::getVar( 'only_page', $only_page_default );

		define( '_FRONTEND_ADMIN_LOADED', '1' );
		
		$editor =& JFactory::getEditor();
		echo $editor->initialise();
		$editor1_array = Array('product.product_form' => 'product_desc',
		'product.product_category_form' => 'category_description',
		'store.store_form' => 'vendor_store_desc',
		'vendor.vendor_form' => 'vendor_store_desc');
		$editor2_array = Array('store.store_form' => 'vendor_terms_of_service',
		'vendor.vendor_form' => 'vendor_terms_of_service');
		editorScript(isset($editor1_array[$page]) ? $editor1_array[$page] : '', isset($editor2_array[$page]) ? $editor2_array[$page] : '');
		
		$vm_mainframe->addStyleSheet( VM_THEMEURL .'admin.css' );
		$vm_mainframe->addStyleSheet( VM_THEMEURL .'admin.styles.css' );
		$vm_mainframe->addScript( "$mosConfig_live_site/components/$option/js/functions.js" );

		if( $only_page != 1 ) {
		
			vmCommonHTML::loadExtjs();
			
			$vm_mainframe->addScript( $_SERVER['SCRIPT_NAME'].'?option='.$option.'&pshop_mode=admin&task=extlayout&frontend=1' );
			$phpscript_url = str_replace( 'index.php', 'index2.php', $_SERVER['SCRIPT_NAME']);
		
			echo '<iframe id="vmPage" src="'.$phpscript_url.'?option=com_virtuemart&amp;page='.$_SESSION['last_page'].'&amp;only_page=1&amp;no_menu=1&amp;pshop_mode=admin" style="width:100%; height: 100%; overflow:auto; border: none;padding-left:4px;" name="vmPage"></iframe>';
		
		} else {
		
			
			echo '<div id="vm-toolbar"></div>';
		
			include( ADMINPATH.'toolbar.virtuemart.php');
			
			echo '<div id="vmPage">';
			
			// Load PAGE
			if( !$pagePermissionsOK ) {
				$error = JText::_('VM_MOD_NO_AUTH');
				include( PAGEPATH. ERRORPAGE .'.php');
				return;
			}
			
			if(file_exists(PAGEPATH.$modulename.".".$pagename.".php")) {
				
				if( $only_page ) {
					if( @$_REQUEST['format'] == 'raw' ) while( @ob_end_clean());
					if( $func ) echo vmCommonHTML::getSuccessIndicator( $ok, $vmDisplayLogger );
		
					include( PAGEPATH.$modulename.".".$pagename.".php" );
					if( @$_REQUEST['format'] == 'raw' ) {
						$vm_mainframe->close(true);
					}
				} else {
					include( PAGEPATH.$modulename.".".$pagename.".php" );
				}
			}
			else {
				include( PAGEPATH.'store.index.php' );
			}
			
			if( DEBUG == '1' && $no_menu != 1 ) {
			        // Load PAGE
				include( PAGEPATH."shop.debug.php" );
			}
			
			echo '</div>';
			if( stristr($page, '_list') && $page != 'product.file_list' ) {
				echo vmCommonHTML::scriptTag('', 'function listItemClicked(e){
		       // find the <a> element that was clicked
		       var a = e.getTarget("a");
		      try {
		        if(a && !a.onclick && a.href.indexOf("javascript:") == -1 && a.href.indexOf("func=") == -1 ) {
		            e.preventDefault();
		            parent.addSimplePanel( a.title != "" ? a.title : a.innerHTML, a.href + "&tmpl=component&pshop_mode=admin&only_page=1&no_menu=1" );
		   		}  
		    } catch(e) {}
		}
		Ext.get("vmPage").mon("click", listItemClicked );');
			}
		}
		// Render the script and style resources into the document head
		$vm_mainframe->close();
		return;
	}
	/**
	** END: FRONTEND ADMIN - MOD
	*****************************/

	// Here is the most important part of the whole Shop:
	// LOADING the requested page for displaying it to the customer.
        // I have wrapped it with a function, because it becomes
        // cacheable that way.
        // It's just an "include" statement which loads the page
        $vmDoCaching = ($page=="shop.browse" || $page=="shop.product_details") 
                        && (empty($keyword) && empty($keyword1) && empty($keyword2));
		
        // IE6 PNG transparency fix
        $vm_mainframe->addScript( "$mosConfig_live_site/components/$option/js/sleight.js" );

		echo '<div id="vmMainPage">'."\n";
		
		// Load requested PAGE
		if( file_exists( PAGEPATH.$modulename.".".$pagename.".php" )) {
			if( $only_page) {
				require_once( CLASSPATH . 'connectionTools.class.php' );
				vmConnector::sendHeaderAndContent( 200 );
				if( $func ) echo vmCommonHTML::getSuccessIndicator( $ok, $vmDisplayLogger ); /*@MWM1: Log/Debug enhancements*/
				include( PAGEPATH.$modulename.".".$pagename.".php" );
				// Exit gracefully
				$vm_mainframe->close(true);
			}
			include( PAGEPATH.$modulename.".".$pagename.".php" );
		}
		elseif( file_exists( PAGEPATH . HOMEPAGE.'.php' )) {
			include( PAGEPATH . HOMEPAGE.'.php' );
		}
	    else {
	        include( PAGEPATH.'shop.index.php');
	    }
	    if ( !empty($mosConfig_caching) && $vmDoCaching) {
	        echo '<span class="small">'.JText::_('LAST_UPDATED').': '.strftime( $vendor_date_format ).'</span>';
	    }
	    
	    echo "\n<div id=\"statusBox\" style=\"text-align:center;display:none;visibility:hidden;\"></div></div>\n";
	    
	    if(SHOWVERSION && !$is_popup) {
			include(PAGEPATH ."footer.php");
	    }

		// Set debug option on/off
		if (vmShouldDebug()) {  /*@MWM1: Log/Debug enhancements */
			$end = utime();
			$runtime = $end - $start;
			
			include( PAGEPATH . "shop.debug.php" );
		}

}
$vm_mainframe->close();
}
?>
