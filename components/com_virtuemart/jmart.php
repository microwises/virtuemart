<?php
/**
* @version		$Id: registration.php 6290 2007-01-16 04:06:06Z Jinx $
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// Require the base controller
require_once (JPATH_COMPONENT.DS.'controller.php');

//Include the VirtueMart configuration file
if (!file_exists(JPATH_COMPONENT_ADMINISTRATOR.DS.'virtuemart.cfg.php')) {
    $errorMsg = '<h3>The configuration file for VirtueMart is missing!</h3>It should be here: <strong>'.JPATH_COMPONENT_ADMINISTRATOR.DS.'virtuemart.cfg.php</strong>';
    die( $errorMsg);
}
//else {
//   require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'virtuemart.cfg.php');
//}


// Require specific controller if requested
if($controller = JRequest::getVar('controller')) {
	$path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
	if (file_exists($path)) {
		require_once $path;
	}
	else {
		$controller = '';
	}
}

// Create the controller
$classname	= 'VirtueMartController'.ucfirst($controller);
$controller = new $classname( );

// Perform the Request task
$controller->execute( JRequest::getVar('task'));

// Redirect if set by the controller
$controller->redirect();

?>