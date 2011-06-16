<?php
/**
*
* Controller for the front end Manufacturerviews
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
* @version $Id: manufacturer.php 2420 2010-06-01 21:12:57Z oscar $
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
class VirtueMartControllerManufacturer extends JController
{

	function Manufacturer() {
		$view = $this->getView(JRequest::getWord('view', 'manufacturer'), 'html');
		
		/* Load the backend models */
		/* Push a model into the view */		
		$this->addModelPath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart' . DS . 'models');
		/*Manufacturer functions */
		$view->setModel( $this->getModel( 'manufacturer', 'VirtuemartModel',true ));
		
		/* Vendor functions */
		$view->setModel( $this->getModel( 'vendor', 'VirtuemartModel' ));
		
		/* Product functions */
		$view->setModel( $this->getModel( 'product', 'VirtuemartModel' ));
		
		/* Set the layout */
		$view->setLayout(JRequest::getWord('layout','default'));
		if (JRequest::getInt('virtuemart_manufacturer_id')) {
			/* link in product details to display a specific manufacturer */
			$view->setLayout('details');
		} else {
			/* view all manufacturer */
			$view->setLayout(JRequest::getWord('layout','default'));
		}
			
		/* Display it all */
		$view->display();
	}
}

// No closing tag
