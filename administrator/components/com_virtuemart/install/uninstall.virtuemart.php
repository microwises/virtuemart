<?php
/**
 * VirtueMart uninstall file.
 *
 * @author Max Milbers, RickG
 * @package VirtueMart
 */
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.model');
require_once(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'models'.DS.'updatesMigration.php');

function com_uninstall() {
	include(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'install'.DS.'uninstall.virtuemart.html.php');
	
	return true;
}

?>