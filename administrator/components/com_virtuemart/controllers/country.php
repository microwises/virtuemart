<?php
/**
*
* Country controller
*
* @package	VirtueMart
* @subpackage Country
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
 * Country Controller
 *
 * @package    VirtueMart
 * @subpackage Country
 * @author RickG
 */
class VirtuemartControllerCountry extends VmController {

	function __construct() {
		parent::__construct();

	}

	public function Country() {

		$document =& JFactory::getDocument();
		$viewType	= $document->getType();
		$view = $this->getView($this->_cname, $viewType);

		// Pushing default model
		$model = $this->getModel();
		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}

		$model1 = $this->getModel('WorldZones');
		if (!JError::isError($model1)) {
			$view->setModel($model1, false);
		}

		parent::display();
	}

	function edit(){

		$document = JFactory::getDocument();
		$viewType = $document->getType();
		$view = $this->getView($this->_cname, $viewType);

		$view->setModel( $this->getModel( 'WorldZones', 'VirtueMartModel' ));

		parent::edit();
	}
}

//pure php no tag
