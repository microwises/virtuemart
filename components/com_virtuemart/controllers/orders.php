<?php
/**
*
* Controller for the front end Orderviews
*
* @package	VirtueMart
* @subpackage User
* @author Oscar van Eijk
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
 * VirtueMart Component Controller
 *
 * @package		VirtueMart
 */
class VirtueMartControllerOrders extends JController
{
	public function __construct()
	{
		parent::__construct();
		$this->registerTask('browse','orders');
	}

	/**
	* Display the order listing 
	*/
	public function orders()
	{
		$view = $this->getView('orders', 'html');
		
		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR .DS.'models' );
		$view->setModel( $this->getModel( 'orders', 'VirtuemartModel' ), true );
		$view->setLayout('list');

		/* Display it all */
		$view->display();
	}

	/**
	* Display the order details 
	*/
	public function details()
	{
		$view = $this->getView('orders', 'html');
		
		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR .DS.'models' );
		$view->setModel( $this->getModel( 'orders', 'VirtuemartModel' ), true );
		$view->setLayout('details');

		/* Display it all */
		$view->display();
	}
}
// No closing tag
