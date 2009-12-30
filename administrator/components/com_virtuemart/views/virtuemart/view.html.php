<?php
/**
* @package		VirtueMart
*/

jimport( 'joomla.application.component.view');
jimport('joomla.html.pane');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'adminMenu.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'image.php');

/**
 * HTML View class for the VirtueMart Component
 *
 * @package		VirtueMart
 */
class VirtuemartViewVirtuemart extends JView
{
	
	function display($tpl = null)
	{		
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
		$recentCustomers = $model->getRecentCustomers();			
		$this->assignRef('recentCustomers', $recentCustomers);
	
		parent::display($tpl);
	}
}
?>
