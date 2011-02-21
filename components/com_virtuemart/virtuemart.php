<?php 
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id$
* @package VirtueMart
* @subpackage core
* @author Max Milbers
* @copyright Copyright (C) 2009-11 by the author - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.net
*/

/* Require the config */
require(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'config.php');
$config= new VmConfig();
$config->loadConfig();

/* Front-end helpers */
require(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'image.php'); //dont remove that file it is actually in every view except the state view
require(JPATH_COMPONENT_SITE.DS.'helpers'.DS.'shopfunctionsf.php'); //dont remove that file it is actually in every view

/* Loading jQuery and VM scripts. */
/*$document = JFactory::getDocument();
$document->addScript(JURI::base().'components/com_virtuemart/assets/js/jquery.js');
$document->addScript(JURI::base().'components/com_virtuemart/assets/js/vm.js');
$document->addScript(JURI::base().'components/com_virtuemart/assets/js/vmsite.js');*/
$config->jQuery();
$config->jPrice();
$config->jVm();
$config->cssSite();

/* Loading stylesheets */
//$document->addStyleSheet(JURI::base().'components/com_virtuemart/assets/css/vmsite.css');

/* Require specific controller if requested */
if($controller = JRequest::getVar('view', 'virtuemart')) {
	if (file_exists(JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php')) {
		// Only if the file exists, since it might be a Joomla view we're requesting...
		require (JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php');
	}
}

//This should be done in the config, only when there are no entries, get them from the file
//require(JPATH_COMPONENT_ADMINISTRATOR.DS.'virtuemart.cfg.php');


/* Create the controller */
$classname   = 'VirtuemartController'.$controller;

$controller = new $classname();
/* Perform the Request task */
$controller->execute(JRequest::getVar('task', JRequest::getVar('view', 'virtuemart')));

//shopFunctionsF::displayDumps();

/* Redirect if set by the controller */
$controller->redirect();

