<?php
/**
 * Abstract class for order plugins
 *
 * @package	VirtueMart
 * @subpackage Plugins
 * @author Oscar van Eijk
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2011 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id$
 */
 
// Get the plugin library
jimport('joomla.plugin.plugin');

/**
* Abstract class for order plugins.
* This class defines the plugin used in the backend in the order detail view. The
* plugins defined here can be used to create special prints (shipping lists, Dymo labels etc.) and
* whatever else you like ;)
* 
* @package	VirtueMart
* @subpackage Plugins
* @author Oscar van Eijk
*/
abstract class vmOrderPlugin extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @param object $subject The object to observe
	 * @param array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}

	/**
	 * This plugin is fired from within the form in the backend order detail view
	 * @param integer $_orderID The order ID
	 * @return string HTML code (might contain form data_
	 * @author Oscar van Eijk
	 */
	abstract public function plgVmOnUpdateOrderBE($_orderID);

}
