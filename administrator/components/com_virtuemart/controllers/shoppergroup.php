<?php
/**
*
* Shopper Group controller
*
* @package	VirtueMart
* @subpackage ShopperGroup
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

/**
 * Shopper Group Controller
 *
 * @package    VirtueMart
 * @subpackage ShopperGroup
 * @author Markus Öhler
 */
class VirtuemartControllerShopperGroup extends JController
{
	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function __construct() {
		parent::__construct();

		// Register Extra tasks
		$this->registerTask( 'add', 'edit' );

		$document =& JFactory::getDocument();
		$viewType	= $document->getType();
		$view =& $this->getView('shoppergroup', $viewType);

		// Push a model into the view
		$model =& $this->getModel('shoppergroup');
		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}
		//$model1 =& $this->getModel('vendor');
		//if (!JError::isError($model1)) {
		//	$view->setModel($model1, false);
		//}
	}

	/**
	 * Display the shopper group view
	 *
	 * @author Markus Öhler
	 */
	function display() {
		parent::display();
	}


	/**
	 * Handle the edit task
	 *
     * @author Markus Öhler
	 */
	function edit()
	{
		JRequest::setVar('controller', 'shoppergroup');
		JRequest::setVar('view', 'shoppergroup');
		JRequest::setVar('layout', 'edit');
		JRequest::setVar('hidemenu', 1);

		parent::display();
	}


	/**
	 * Handle the cnacel task
	 *
	 * @author Markus Öhler
	 */
	function cancel()
	{
		$this->setRedirect('index.php?option=com_virtuemart&view=shoppergroup');
	}


	/**
	 * Handle the save task
	 *
	 * @author Markus Öhler
	 */
	function save()
	{
		$model =& $this->getModel('shoppergroup');

		if ($model->store()) {
			$msg = JText::_('Shopper group saved!');
		}
		else {
			$msg = JText::_($model->getError());
		}

		$this->setRedirect('index.php?option=com_virtuemart&view=shoppergroup', $msg);
	}


	/**
	 * Handle the remove task
	 *
	 * @author Markus Öhler
	 */
	function remove()
	{
		$model = $this->getModel('shoppergroup');
		if (!$model->delete()) {
			$msg = JText::_('Error: One or more shopper groups could not be deleted!');
		}
		else {
			$msg = JText::_( 'Shopper groups deleted!');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=shoppergroup', $msg);
	}

}
?>
