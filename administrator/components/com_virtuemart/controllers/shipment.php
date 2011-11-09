<?php
/**
*
* Shipment Carrier controller
*
* @package	VirtueMart
* @subpackage Shipment
* @author RickG
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id$
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the controller framework
jimport('joomla.application.component.controller');

if(!class_exists('VmController'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmcontroller.php');


/**
 * Shipment Carrier Controller
 *
 * @package    VirtueMart
 * @subpackage Shipment
 * @author RickG, Max Milbers
 */
class VirtuemartControllerShipment extends VmController {

	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function __construct() {
		parent::__construct();

		$document = JFactory::getDocument();
		$viewType	= $document->getType();
		$view = $this->getView('shipment', $viewType);

		// Push a model into the view
		$model = $this->getModel('shipment');
		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}
	}

	/**
	 * We want to allow html in the descriptions.
	 *
	 * @author Max Milbers
	 */
	function save(){
		$data = JRequest::get('post');
		// TODO disallow shipment_name as HTML
		$data['shipment_name'] = JRequest::getVar('shipment_name','','post','STRING',JREQUEST_ALLOWHTML);
		$data['shipment_desc'] = JRequest::getVar('shipment_desc','','post','STRING',JREQUEST_ALLOWHTML);

		parent::save($data);
	}
	
}
// pure php no closing tag
