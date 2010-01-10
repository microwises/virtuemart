<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
* This file prepares the VirtueMart framework
* It should be included whenever a VirtueMart function is needed
*
* @version $Id: virtuemart_parser.php 1755 2009-05-01 22:45:17Z rolandd $
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
global $my, $db, $perm, $ps_function, $ps_module, $ps_html, $hVendor_id, $vendor_image,$vendor_image_url, $keyword,
	$ps_payment_method,$ps_zone,$sess, $page, $func, $pagename, $modulename, $vars, $default, $cmd, $ok, $mosConfig_lang,
	$auth, $ps_checkout,$error, $error_type, $func_perms, $func_list, $func_class, $func_method, $func_list, $dir_list, $pagePermissionsOK,
	$vendor_currency_display_style, $vendor_freeshipping, $mm_action_url, $limit, $limitstart, $mainframe, $ps_product, $database;
global $sess, $perm, $ps_module, $pagePermissionsOK;

include_once( dirname(__FILE__).'/../../administrator/components/com_virtuemart/compat.joomla1.5.php' );

if( !defined( '_VM_PARSER_LOADED' )) {
	global $my;
	
	// Clean the var PHP_SELF from chars like " or ' 
	$_SERVER['PHP_SELF'] = htmlspecialchars( $_SERVER['PHP_SELF'], ENT_QUOTES );
	
	if( !empty($_SERVER['QUERY_STRING'])) {
		// Make sure, that the Query String only contains urlencoded values
		$vars = explode( '&', $_SERVER['QUERY_STRING']);
		$new_query_string = array();
		foreach( $vars as $val) {
			$keyvarpair = explode('=', $val);
			if( sizeof( $keyvarpair ) == 1 ) {
				$keyvarpair[1] = 0;
			}
			$new_query_string[] = $keyvarpair[0].'='.urlencode(urldecode($keyvarpair[1]));
			
		}
		$_SERVER['QUERY_STRING'] = implode('&', $new_query_string );
	}
	
	if( !empty($my->id) || !empty($user->id) ) {
		// This is necessary to get the real GID
		if( class_exists( 'jconfig' ) ) {
			$tmpuser = & JFactory::getUser();
			if( !defined( '_JLEGACY' ) ) {
				$GLOBALS['my']->load( $tmpuser->get('id'));
				$GLOBALS['my']->set('gid', $tmpuser->get('gid'));
			} else {
				$GLOBALS['my']->gid = $tmpuser->get('gid');
			}
		} else {
			$my->load( $my->id );
		}
		$vmuser = $my;
	}

	// the configuration file for the Shop
	require_once( JPATH_ROOT. "/administrator/components/com_virtuemart/virtuemart.cfg.php" );
	
	$GLOBALS['mosConfig_live_site'] = $mosConfig_live_site = substr( URL, 0, strlen(URL)-1);
	
	// the global file for VirtueMart
	require_once( ADMINPATH . 'global.php' );

	if( !vmIsAdminMode() && !is_a($mainframe, 'JAdministrator') && !isset( $_REQUEST['page']) ) {

		// Get the menu parameters, if any
//		if( vmIsJoomla( '1.5' ) ) {
			$menuparams = $mainframe->getParams();
//		} else {
//			$Itemid = (int) vmRequest::getInt( 'Itemid', '' );
//			$query = "SELECT params FROM #__menu WHERE id='".$Itemid."'";
//			$database->setQuery( $query );
//			$itemparams = $database->loadResult();
//			$menuparams = new mosParameters( $itemparams );
//		}
		
		$tmp_product_id = $menuparams->get( 'product_id' );
		$tmp_category_id = $menuparams->get( 'category_id' );
		$tmp_flypage = $menuparams->get( 'flypage' );
		$tmp_page = $menuparams->get( 'page' );

		if( !empty( $tmp_product_id ) ) {
			vmRequest::setVar( 'product_id', $tmp_product_id );
			vmRequest::setVar( 'page', 'shop.product_details' );
		} elseif( !empty( $tmp_category_id ) ) {
			vmRequest::setVar( 'category_id', $tmp_category_id );
			vmRequest::setVar( 'page', 'shop.browse' );
		}
		
		if( ( !empty( $tmp_product_id ) || !empty( $tmp_category_id ) ) && !empty( $tmp_flypage ) ) {
			vmRequest::setVar( 'flypage', $tmp_flypage );
		}
		
		if( !empty( $tmp_page ) ) {
			vmRequest::setVar( 'page', $tmp_page );
		}

		// Set the default page
		$defaultpage = HOMEPAGE;
	} else {
		$defaultpage = JRequest::getVar('last_page');
	}
	$page = JRequest::getVar('page', $defaultpage );
	$func = JRequest::getVar('func');
	$ajax_request = strtolower(JRequest::getVar('HTTP_X_REQUESTED_WITH')) == 'xmlhttprequest' || JRequest::getVar('ajax_request') == '1';
	$option = JRequest::getVar('option');

	// This makes it possible to use Shared SSL
	$sess->prepare_SSL_Session();
	
	if( $option == "com_virtuemart" ) {

		// Get sure that we have float values with a decimal point!
		@setlocale( LC_NUMERIC, 'en_US', 'en' );
		@setlocale( LC_TIME, $mosConfig_locale );

		$mosConfig_list_limit = isset( $mosConfig_list_limit ) ? $mosConfig_list_limit : SEARCH_ROWS;

		unset( $_REQUEST["error"] );
		
		// Cast all the following fields to INT
		$parseToIntFields = array('user_id','product_id','category_id','manufacturer_id','id','cid','vendor_id','country_id','currency_id', 'limitstart', 'tax_rate_id',
								'order_id','module_id','function_id','payment_method_id','coupon_id','product_type_id', 'product_price_id', 'shopper_group_id') ;
		foreach( $parseToIntFields as $intField ) {
			if( !empty($_REQUEST[$intField]) && is_array($_REQUEST[$intField]) ) {
				vmArrayToInts( $_REQUEST[$intField] );
			} elseif ( isset($_REQUEST[$intField]) ) {
				$_REQUEST[$intField] = $$intField = vmRequest::getInt($intField);
			}
		}
		$product_id = vmRequest::getInt('product_id');
		$vm_mainframe->setUserState('product_id', $product_id );
		if( vmIsAdminMode() ) {
//			$category_id = (int)$vm_mainframe->getUserStateFromRequest( 'category_id', 'category_id' );
			$category_id = (int)$mainframe->getUserStateFromRequest( 'category_id', 'category_id' );
		} else {
			$category_id = vmRequest::getInt('category_id');
		}
		$manufacturer_id = vmRequest::getInt('manufacturer_id');
		
		$user_info_id = vmRequest::getVar('user_info_id');

		$myInsecureArray = array(
									'user_info_id' => $user_info_id,
									'page' => $page,
									'func' => $func
									);
		/**
		 * This InputFiler Object will help us filter malicious variable contents
		 * @global vmInputFiler vmInputFiler
		 */
		$GLOBALS['vmInputFilter'] = $vmInputFilter = vmInputFilter::getInstance();
		// prevent SQL injection
		if( $perm->check('admin,storeadmin') ) {
			$myInsecureArray = $vmInputFilter->safeSQL( $myInsecureArray );
			$myInsecureArray = $vmInputFilter->process( $myInsecureArray );
			// Re-insert the escaped strings into $_REQUEST
			foreach( $myInsecureArray as $requestvar => $requestval) {
					$_REQUEST[$requestvar] = $requestval;
			}
		} else {
			// Strip all tags from all input values
			$_REQUEST = $vmInputFilter->process( $_REQUEST );
			$_REQUEST = $vmInputFilter->safeSQL( $_REQUEST );
		}
		
		// Limit the keyword (=search string) length to 50
		$keyword = substr( urldecode( JRequest::getVar( 'keyword' )), 0, 50 );
		
		$vars = vmRequest::get('', VMREQUEST_ALLOWRAW );
	}

    // The Page will change with every different parameter / argument, so provide this for identification
    // "call" will call the function load_that_shop_page when it is not yet cached with exactly THESE parameters
    // or the caching time range has expired
	$GLOBALS['cache_id'] = vmTemplate::getCacheId();
		
	if( $option == "com_virtuemart" ) {
		// Check if we have to run a Shop Function
		// and if the user is allowed to execute it
		$funcParams = $ps_function->getFuncPermissions( $func );
//		$vmLogger->info( 'virtuemart_parser.php $funcParams '.$funcParams);

		/**********************************************
		** Get Page/Directory Permissions
		** Displays error if directory is not registered,
		** user has no permission to view it , or file doesn't exist
		************************************************/
		if (empty($page)) {// default page
			if (defined('_VM_IS_BACKEND')) {
				$page = "store.index";
			} 
			else {
				$page = HOMEPAGE;
			}
		}
		// Let's check if the user is allowed to view the page
		// if not, $page is set to ERROR_PAGE
		$pagePermissionsOK = $ps_module->checkModulePermissions( $page );

		$ok = true;

		if ( !empty( $funcParams["method"] ) && JRequest::getVar('task') != 'cancel' ) {
			// Protection against Cross-Site Request Forgery
			if( vmIsAdminMode() && !vmSpoofCheck(null, $sess->getSessionId() ) ) {
				return;
			}
			// Get the function parameters: function name and class name
			$q = "SELECT #__{vm}_module.module_name,#__{vm}_function.function_class";
			$q .= " FROM #__{vm}_module,#__{vm}_function WHERE ";
			$q .= "#__{vm}_module.module_id=#__{vm}_function.module_id AND ";
			$q .= "#__{vm}_function.function_method='".$funcParams["method"]."' AND ";
			$q .= "#__{vm}_function.function_class='".$funcParams["class"]."'";
			
			$db->query($q);
			$db->next_record();
			$class = $db->f('function_class');
			if( file_exists( CLASSPATH."$class.php" ) ) {
				if( $ajax_request ) {
					require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'connection.php');
					VmConnector::sendHeaderAndContent( 200 );
				}
				// Load class definition file
				require_once( CLASSPATH."$class.php" );
				$classname = str_replace( '.class', '', $funcParams["class"]);
				if( !class_exists(strtolower($classname))) {
					$classname = 'vm'.$classname;
				}
				if( class_exists( $classname )) {
					// create an object
					$$classname = new $classname();
				
					// RUN THE FUNCTION
					// $ok  = $class->function( $vars );
					$ok = $$classname->$funcParams["method"]($vars);
				}
				if ($ok == false) {
					$no_last = 1;
					$last_page = JRequest::getVar( 'last_page' );
					if( $last_page != HOMEPAGE && !empty( $last_page ) && empty($_REQUEST['ignore_last_page']) ) {
						$page = $last_page;
					}
					$my_page= explode ( '.', $page );
					$modulename = $my_page[0];
					$pagename = $my_page[1];
					$_REQUEST['keyword']= vmGet($_SESSION['session_userstate'], 'keyword' );
					$_REQUEST['category_id']= vmGet( $_SESSION['session_userstate'], 'category_id' );
					$_REQUEST['product_id']=$product_id = $_SESSION['session_userstate']['product_id'];
				}
			}
			else {
				$vmLogger->debug( "Could not include the class file $class" );
			}
			

			if (!empty($vars["error"])) {
				$error = vmGet( $vars, 'error' );
			}
			if (!empty($error)) {
				echo vmCommonHTML::getErrorField($error);
			}
			
		}
		else {
			$no_last = 0;
			//$error="";
		}
		
		// If this is an asynchronous page load,
		// we clear the output buffer and just send the log messages.
		// the variable named 'ajax_request' has to be set to 1.
		if( $func && $ajax_request) {
			// Send an indicator wether the function call return true or false
			vmCommonHTML::getSuccessIndicator( $ok, $vmDisplayLogger );		
			$vm_mainframe->close(true);//die
		}
		
		if ($ok == true && empty($error) && !defined('_DONT_VIEW_PAGE') && !strstr($page, 'ajax')) {
			$_SESSION['last_page'] = $page;
		}
	}

	// I don't get it, why Joomla uses masked gid values!
	if( !defined( '_VM_IS_BACKEND' )&& !class_exists('jfactory')) {
		$my = $mainframe->getUser();
	}
	
	if( empty($_REQUEST['only_page']) ) {
		// the Log object holds all error messages
		// here we flush the buffer and print out all messages
		$vmLogger->flush();
		
		// Now we can switch to implicit flushing
		$vmDisplayLogger->_buffering = false;
	}
	
	define( '_VM_PARSER_LOADED', 1 );
}
?>