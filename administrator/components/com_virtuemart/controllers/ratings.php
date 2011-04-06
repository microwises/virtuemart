<?php
/**
*
* Review controller
*
* @package	VirtueMart
* @subpackage
* @author RolandD
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
 * Review Controller
 *
 * @package    VirtueMart
 * @author RolandD
 */
class VirtuemartControllerRatings extends JController {

	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function __construct() {
		parent::__construct();

		/* Redirects */
		$this->registerTask('unpublish','publish');
		$this->registerTask('add','edit');
		$this->registerTask('cancel','ratings');
	}

	/**
	 * Shows the product list screen
	 */
	public function Ratings() {
		/* Create the view object */
		$view = $this->getView('ratings', 'html');

		/* Default model */
		$view->setModel( $this->getModel( 'ratings', 'VirtueMartModel' ), true );

		/* Set the layout */

		/* Now display the view. */
		$view->display();
	}

	/**
	* Publish/Unpublish a rating
	* @author RolandD
	*/
	public function publish() {
		$mainframe = Jfactory::getApplication();

		/* Load the view object */
		$view = $this->getView('ratings', 'html');

		$model = $this->getModel('ratings');
		$msgtype = '';
		if ($model->setPublish()) {
			$msg = JText::sprintf('COM_VIRTUEMART_RATING_TASK_SUCCESSFULLY', strtoupper($this->getTask()));
		}
		else {
			$msg = JText::strintf('RATING_NOT_TASK_SUCCESSFULLY', strtoupper($this->getTask()));
			$msgtype = 'error';
		}

		$mainframe->redirect('index.php?option=com_virtuemart&view=ratings&task=ratings', $msg, $msgtype);
	}

	/**
	 * Handle the edit task
	 *
     * @author RolandD
	 */
	function edit() {
		JRequest::setVar('controller', 'ratings');
		JRequest::setVar('view', 'ratings');
		JRequest::setVar('layout', 'edit');
		JRequest::setVar('hidemenu', 1);

		parent::display();
	}

	/**
	* Save a rating
	*
	* @author RolandD
	*/
	public function Save() {
		$mainframe = Jfactory::getApplication();

		/* Load the view object */
		$view = $this->getView('ratings', 'html');

		$model = $this->getModel('ratings');
		$msgtype = '';
		if ($model->saveRating()) $msg = JText::_('COM_VIRTUEMART_RATING_SAVED_SUCCESSFULLY');
		else {
			$msg = JText::_('COM_VIRTUEMART_RATING_NOT_SAVED_SUCCESSFULLY');
			$msgtype = 'error';
		}
		$mainframe->redirect('index.php?option=com_virtuemart&view=ratings&task=ratings', $msg, $msgtype);
	}

	/**
	* Delete a user rating
	* @author RolandD
	*/
	public function remove() {
		$mainframe = Jfactory::getApplication();

		/* Load the view object */
		$view = $this->getView('ratings', 'html');

		$model = $this->getModel('ratings');
		$msgtype = '';
		if ($model->removeRating()) $msg = JText::_('COM_VIRTUEMART_RATING_REMOVED_SUCCESSFULLY');
		else {
			$msg = JText::_('COM_VIRTUEMART_RATING_NOT_REMOVED_SUCCESSFULLY');
			$msgtype = 'error';
		}

		$mainframe->redirect('index.php?option=com_virtuemart&view=ratings&task=ratings', $msg, $msgtype);
	}
}
// pure php no closing tag
