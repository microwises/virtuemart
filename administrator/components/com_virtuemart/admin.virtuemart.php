<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
/**
*
* @version $Id$
* @package VirtueMart
* @subpackage core
* @copyright Copyright (C) VirtueMart Team - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/
require(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'config.php');
VmConfig::loadConfig();

// Require specific controller if requested
if ($controllername = JRequest::getVar('controller')) {
	$path = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'controllers'.DS.$controllername.'.php';
	if (file_exists($path)) {
		require $path;
	}
	else {

		$controllername = '';
	}
}

// Try to find a controller with the same name as the view
else if ($controllername = JRequest::getVar('view')) {
	$path = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'controllers'.DS.$controllername.'.php';
	if (file_exists($path)) {
		require $path;
	}
	else {
		$controllername = '';
	}
}

//Fallback
if(empty($controllername)){
	// Require the base controller
	require(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'controllers'.DS.'virtuemart.php');
}

// Create the controller
$classname	= 'VirtueMartController'.ucfirst($controllername);
$controller = new $classname();

// Perform the Request task
$controller->execute(JRequest::getVar('task', $controllername));
$controller->redirect();

// pure php no closing tag