<?php 
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: admin.ajax_tools.php 1755 2009-05-01 22:45:17Z rolandd $
* @package VirtueMart
* @subpackage classes
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

$task = strtolower( JRequest::getVar( 'task' ));
$option = strtolower( JRequest::getVar( 'option' ));
require_once( CLASSPATH.'connectionTools.class.php');

switch( $task ) {
	case 'get_class_methods':
		$class = JRequest::getVar( 'class', 'ps_product' );
		$classfile = basename( $class ).'.php';
		$function = JRequest::getVar( 'function' );
		$method_array = array();
		if( file_exists(CLASSPATH. $classfile )) {
			require_once( CLASSPATH.$classfile);
			$class = str_replace( '.class', '', $class );
			$methods = get_class_methods( $class );
			
			if( empty( $methods )) {
				$methods = get_class_methods( 'vm'.$class );	
			}
			foreach( $methods as $method ) {
				if( $method == $class ) continue;
				$method_array[$method] = $method;
			}
			
		}
		vmConnector::sendHeaderAndContent( 200, ps_html::selectList( 'function_method', $function, $method_array ) );
		break;
	case 'checkforupdate':
		require_once( CLASSPATH.'update.class.php');
		$result = vmUpdate::checkLatestVersion();
		
		if( !empty($result) ) {
			// Convert a String like "1.1.1" => "1.11", so we can use it as float in Javascript
			$version_as_float = substr($result, 0, 3 ) . substr( $result, 4 );
			$version_as_json = '{version_string:"'.$result.'",version:"'.$version_as_float.'"}';
			vmConnector::sendHeaderAndContent('200', $version_as_json );
		} else {
			vmConnector::sendHeaderAndContent('200', 'Connection Failed' );
		}
		
	default: die;
	
}
exit;
?>