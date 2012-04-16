<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id$
* @package VirtueMart
* @subpackage core
* @author Max Milbers
* @copyright Copyright (C) 2009-11 by the authors of the VirtueMart Team listed at /administrator/com_virtuemart/copyright.php - All rights reserved.
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

vmRam('Start');
// vmSetStartTime();
vmSetStartTime('Start');

if(VmConfig::get('enableEnglish', 1)){
    $jlang =JFactory::getLanguage();
    $jlang->load('com_virtuemart', JPATH_SITE, 'en-GB', true);
    $jlang->load('com_virtuemart', JPATH_SITE, $jlang->getDefault(), true);
    $jlang->load('com_virtuemart', JPATH_SITE, null, true);
}
if(VmConfig::get('shop_is_offline',0)){
	$_controller = 'virtuemart';
	require (JPATH_VM_SITE.DS.'controllers'.DS.'virtuemart.php');
	JRequest::setVar('view', 'virtuemart');
	$task='';
} else {

	//Lets load first englisch, then joomla default standard, then user language.
	 $jlang =JFactory::getLanguage();
	 $jlang->load('com_virtuemart', JPATH_SITE, 'en-GB', true);
	 $jlang->load('com_virtuemart', JPATH_SITE, $jlang->getDefault(), true);
	 $jlang->load('com_virtuemart', JPATH_SITE, null, true);

	/* Front-end helpers */
	if(!class_exists('VmImage')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'image.php'); //dont remove that file it is actually in every view except the state view
	if(!class_exists('shopFunctionsF'))require(JPATH_VM_SITE.DS.'helpers'.DS.'shopfunctionsf.php'); //dont remove that file it is actually in every view
	if (!class_exists( 'VmModel' )) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmmodel.php');


	/* Loading jQuery and VM scripts. */
	vmJsApi::jQuery();
	vmJsApi::jSite();
	vmJsApi::cssSite();
	$_controller = JRequest::getWord('view', JRequest::getWord('controller', 'virtuemart')) ;
// 	$task = JRequest::getWord('task',JRequest::getWord('layout',$_controller) );		$this makes trouble!
	$task = JRequest::getWord('task') ;

	if (($_controller == 'product' || $_controller == 'category') && ($task == 'save' || $task == 'edit') ) {
		$app = JFactory::getApplication();

		if ($task == 'save') $app->redirect('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.JRequest::getInt('virtuemart_product_id') );
		else {
			if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
			if	(Permissions::getInstance()->check("admin,storeadmin")) {
				 $jlang->load('com_virtuemart', JPATH_ADMINISTRATOR, null, true);
				require (JPATH_VM_ADMINISTRATOR.DS.'controllers'.DS.$_controller.'.php');
				//require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'shopfunctions.php');

			} else {
				$app->redirect('index.php?option=com_virtuemart', jText::_('COM_VIRTUEMART_RESTRICTED_ACCESS') );
			}
		}


	/* Require specific controller if requested */
	} elseif($_controller) {
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
if (class_exists($_class)) {
    $controller = new $_class();

    /* Perform the Request task */
    $controller->execute($task);

    //Console::logSpeed('virtuemart start');
    vmTime($_class.' Finished task '.$task,'Start');
    vmRam('End');
    vmRamPeak('Peak');
    /* Redirect if set by the controller */
    $controller->redirect();
} else {
    vmDebug('VirtueMart controller not found: '. $_class);
    $mainframe = Jfactory::getApplication();
    $mainframe->redirect('index.php?option=com_virtuemart');
}
