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

//if(!class_exists('VmController'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmcontroller.php');


/**
 * VirtueMart default administrator controller
 *
 * @package		VirtueMart
 */
class VirtuemartControllerVirtuemart extends JController {


	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function display() {

		$document = JFactory::getDocument();
		$viewName = JRequest::getVar('view', '');
		$viewType = $document->getType();
		$view =& $this->getView($viewName, $viewType);

		// Push a model into the view
		$model =& $this->getModel( 'virtuemart' );
		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}

		parent::display();
	}
}
