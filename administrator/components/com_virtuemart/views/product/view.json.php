<?php
/**
* @package		VirtueMart
*/

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the VirtueMart Component
 *
 * @package		VirtueMart
 */
class VirtuemartViewProduct extends JView {
	
	function display($tpl = null) {
		/* Get the task */
		$type = JRequest::getVar('type');
		
		switch ($type) {
			case 'relatedproducts':
				$related_products = $this->get('ProductListJson');
				echo json_encode($related_products);
				break;
			
		}
	}
}
?>
