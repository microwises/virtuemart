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

//Console::logSpeed('virtuemart start');

if (!class_exists( 'VmConfig' )) require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart'.DS.'helpers'.DS.'config.php');
VmConfig::loadConfig();
if(VmConfig::get('shop_is_offline',0)){
	$_controller = 'virtuemart';
	require (JPATH_VM_SITE.DS.'controllers'.DS.'virtuemart.php');
	JRequest::setVar('view', 'virtuemart');

} else {
	/* Front-end helpers */
	require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'image.php'); //dont remove that file it is actually in every view except the state view
	require(JPATH_VM_SITE.DS.'helpers'.DS.'shopfunctionsf.php'); //dont remove that file it is actually in every view

	/* Loading jQuery and VM scripts. */
	VmConfig::jQuery();
	VmConfig::jSite();
	VmConfig::cssSite();

	/* Require specific controller if requested */
	if($_controller = JRequest::getWord('controller', JRequest::getWord('view', 'virtuemart'))) {
		if (file_exists(JPATH_VM_SITE.DS.'controllers'.DS.$_controller.'.php')) {
			// Only if the file exists, since it might be a Joomla view we're requesting...
			require (JPATH_VM_SITE.DS.'controllers'.DS.$_controller.'.php');
		}
		else {
			// try plugins
			JPluginHelper::importPlugin('vmextended');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onVmSiteController', $_controller);
		}
	}

}

/* Create the controller */
$_class = 'VirtuemartController'.ucfirst($_controller);
$controller = new $_class();

/* Perform the Request task */
$controller->execute(JRequest::getWord('task', JRequest::getWord('view', $_controller)));
//Console::logSpeed('virtuemart start');

vmdump('My Controller',$controller,'There is nothing to see');

/* Redirect if set by the controller */
$controller->redirect();

