<?php
/**
*
* Base controller
*
* @package	VirtueMart
* @subpackage Core
* @author Max Milbers
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2011 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: calc.php 2641 2010-11-09 19:25:13Z milbo $
*/

jimport('joomla.application.component.controller');

/**
 * VirtueMart default administrator controller
 *
 * @package		VirtueMart
 */
class VirtuemartControllerPlugin extends JController
{
	/**
	 * Method to render the plugin datas
	 * this is an entry point to plugin to easy renders json or html
	 *  
	 *
	 * @access	public
	 */
	function Plugin()
	{ 
		$type = JRequest::getWord('type', 'vmcustom');
		$typeWhiteList = array('vmcustom','vmcalculation','vmpayment','vmshipper');
		if(!in_array($type,$typeWhiteList)) return false;

		$name = JRequest::getCmd('name', null);


		JPluginHelper::importPlugin($type, $name);
		$dispatcher = JDispatcher::getInstance();
		// if you want only one render simple in the plugin use jExit(); 
		// or $render is an array of code to echo as html or json Objects!
		$render = null ;
		$dispatcher->trigger('plgVmOnSelfCallFE',array($type, $name, &$render));
		if ($render !=null) {
			$format = JRequest::getCmd('format', 'json');
			if ($format == 'json') echo json_encode($render);
			else echo $render;
		}
		jexit();
	}
}
