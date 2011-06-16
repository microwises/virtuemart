<?php
/**
*
* Module controller
*
* @package	VirtueMart
* @subpackage Module
* @author Markus Öhler
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
 * Module Controller
 *
 * @package    VirtueMart
 * @subpackage Module
 * @author Markus Öhler
 */
class VirtuemartControllerModule extends JController
{
	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function __construct() {
		parent::__construct();

//		$this->setMainLangKey('MODULE');
		// Register Extra tasks
		$this->registerTask( 'add', 'edit' );

		$document = JFactory::getDocument();
		$viewType	= $document->getType();
		$view = $this->getView('module', $viewType);

		// Push a model into the view
		$model = $this->getModel('module');
		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}
	}

	/**
	 * Display the shopper group view
	 *
	 * @author Markus Öhler
	 */
	function display() {
		parent::display();
	}

}
// pure php no closing tag
