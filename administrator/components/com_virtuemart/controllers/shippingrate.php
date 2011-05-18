<?php
/**
*
* Shipping Rate controller
*
* @package	VirtueMart
* @subpackage ShippingRate
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
 * Shipping Carrier Controller
 *
 * @package    VirtueMart
 * @subpackage ShippingRate
 * @author RickG
 */
class VirtuemartControllerShippingRate extends VmController {

	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function __construct() {
		parent::__construct();

//		$this->setMainLangKey('SHIPPING_RATE');
		// Register Extra tasks
		$this->registerTask( 'add',  'edit' );
		$this->registerTask('apply','save');

		$document =& JFactory::getDocument();
		$viewType	= $document->getType();
		$view =& $this->getView('shippingrate', $viewType);

		// Push a model into the view
		$model =& $this->getModel('shippingrate');
		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}
		$model1 =& $this->getModel('country');
		if (!JError::isError($model1)) {
			$view->setModel($model1, false);
		}
		$model2 =& $this->getModel('shippingcarrier');
		if (!JError::isError($model2)) {
			$view->setModel($model2, false);
		}
		$model3 =& $this->getModel('currency');
		if (!JError::isError($model3)) {
			$view->setModel($model3, false);
		}
//		$model =& $this->getModel('taxrate');
//		if (!JError::isError($model)) {
//			$view->setModel($model, false);
//		}
	}

}
// pure php no closing tag
