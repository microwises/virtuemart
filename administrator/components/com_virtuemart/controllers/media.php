<?php
/**
*
* Media controller
*
* @package	VirtueMart
* @subpackage
* @author
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
 * Product Controller
 *
 * @package    VirtueMart
 * @author
 */
class VirtuemartControllerMedia extends JController {

	/**
	 * Method to display the view
	 *
	 * @access	public
	 * @author
	 */
	function __construct() {
		parent::__construct();

		$this->registerTask('add','media');
		$this->registerTask('edit','media');
		$this->registerTask('remove','media');
		$this->registerTask('cancel','media');
		$this->registerTask('save','media');
		$this->registerTask('publish','media');
		$this->registerTask('unpublish','media');
	}

	/**
	 * Shows the product files list screen
	 */
	function Media() {
		/* Create the view object */
		$view = $this->getView('media', 'html');

		/* Default model */
		$view->setModel( $this->getModel( 'media', 'VirtueMartModel' ), true );

		/* Set the layout */
		switch (JRequest::getCmd('task')) {
			case 'add':
			case 'edit':
				$view->setLayout('media_edit');
				break;
			default:
				$view->setLayout('media');
				break;
		}

		/* Now display the view. */
		$view->display();
	}


}
?>
