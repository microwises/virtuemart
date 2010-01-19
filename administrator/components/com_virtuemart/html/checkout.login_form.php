<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id$
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2004-2007 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/
mm_showMyFileName( __FILE__ );

// Determine settings based on CMS version
//if( vmIsJoomla( '1.5' ) ) {
	// Post action
	$action =  'index.php?option=com_user&amp;task=login';

	// Return URL
	$uri = JFactory::getURI();
	$url = $uri->toString(array('path', 'query', 'fragment'));
	$return_url = base64_encode( $url );

	// Set the validation value
	$validate = JUtility::getToken();
	
//} else {
//	// Post action
//	$action = 'index.php?option=login';
//
//	// Return URL
//	$return_url = vmGet( $_SERVER, 'REQUEST_URI', null );
//
//	// Convert & to &amp; for xhtml compliance
//	$return_url = str_replace( '&', '&amp;', $return_url );
//	$return_url = str_replace( 'option', '&amp;option', $return_url );
//
//	// Set the validation value
//	if( function_exists( 'josspoofvalue' ) ) {
//		$validate = josSpoofValue(1);
//	} else {
//		$validate = vmSpoofValue(1);
//	}
//}

$theme = vmTemplate::getInstance();

$theme->set_vars( array(
						'action' => $action,
						'return_url' => $return_url,
						'validate' => $validate, 
						'mosConfig_lang' => $mosConfig_lang
					));

echo $theme->fetch('common/login_form.tpl.php');
