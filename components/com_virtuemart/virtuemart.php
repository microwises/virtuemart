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
/* Going for a new look :) */

/* Require the base controller */
require_once(JPATH_COMPONENT.DS.'controller.php');
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'config.php');
VmConfig::loadConfig();

require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'store.php');
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'permissions.php');
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'shoppergroup.php');
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'shopfunctions.php');
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'calculationh.php');
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'currencydisplay.php');
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'vendorhelper.php');
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'image.php');
require_once(JPATH_BASE.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'vmpaymentplugin.php');
require_once(JPATH_BASE.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'cart.php');

/* Front-end helpers */
require_once(JPATH_SITE.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'shopfunctionsf.php');

/* Loading jQuery and VM scripts. */
$document = JFactory::getDocument();
$document->addScript(JURI::base().'components/com_virtuemart/assets/js/jquery.js');
$document->addScript(JURI::base().'components/com_virtuemart/assets/js/vm.js');
$document->addScript(JURI::base().'components/com_virtuemart/assets/js/vmsite.js');

/* Loading stylesheets */
$document->addStyleSheet(JURI::base().'components/com_virtuemart/assets/css/vmsite.css');

/* Require specific controller if requested */
if($controller = JRequest::getVar('view', 'virtuemart')) {
	if (file_exists(JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php')) {
		// Only if the file exists, since it might be a Joomla view we're requesting...
		require_once (JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php');
	}
}

//This should be done in the config, only when there are no entries, get them from the file
//require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'virtuemart.cfg.php');


/* Create the controller */
$classname   = 'VirtuemartController'.$controller;

$controller = new $classname();
/* Perform the Request task */
$controller->execute(JRequest::getVar('task', JRequest::getVar('view', 'virtuemart')));

/* Redirect if set by the controller */
$controller->redirect();

