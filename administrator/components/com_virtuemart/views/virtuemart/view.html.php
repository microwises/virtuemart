<?php
/**
*
* Description
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

// Load the view framework
jimport( 'joomla.application.component.view');
jimport('joomla.html.pane');

/**
 * HTML View class for the VirtueMart Component
 *
 * @package		VirtueMart
 * @author
 */
class VirtuemartViewVirtuemart extends JView {

	function display($tpl = null) {

		// Load the helper(s)
		$this->loadHelper('adminMenu');
		$this->loadHelper('image');

		$model =& $this->getModel();

		$nbrCustomers = $model->getTotalCustomers();
		$this->assignRef('nbrCustomers', $nbrCustomers);

		$nbrActiveProducts = $model->getTotalActiveProducts();
		$this->assignRef('nbrActiveProducts', $nbrActiveProducts);
		$nbrInActiveProducts = $model->getTotalInActiveProducts();
		$this->assignRef('nbrInActiveProducts', $nbrInActiveProducts);
		$nbrFeaturedProducts = $model->getTotalFeaturedProducts();
		$this->assignRef('nbrFeaturedProducts', $nbrFeaturedProducts);

		$ordersByStatus = $model->getTotalOrdersByStatus();
		$this->assignRef('ordersByStatus', $ordersByStatus);

		$recentOrders = $model->getRecentOrders();
		$this->assignRef('recentOrders', $recentOrders);
//		$recentCustomers = $model->getRecentCustomers();
//		$this->assignRef('recentCustomers', $recentCustomers);

		parent::display($tpl);
	}
}

//pure php no tag