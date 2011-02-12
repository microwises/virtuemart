<?php
/**
*
* Manufacturer View
*
* @package	VirtueMart
* @subpackage Manufacturer
* @author vhv_alex
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: view.html.php 2641 2010-11-09 19:25:13Z milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the view framework
jimport( 'joomla.application.component.view');

/**
 * HTML View class for maintaining the list of manufacturers
 *
 * @package	VirtueMart
 * @subpackage Manufacturer
 * @author vhv_alex
 */
class VirtuemartViewManufacturer extends JView {

	function display($tpl = null) {
		$manufacturer_id = JRequest::getInt('manufacturer_id', 0);
		$mf_categorie_id = JRequest::getInt('mf_categorie_id', 0);
		// get necessary models
		$model = & $this->getModel('manufacturer');
		if ($manufacturer_id ) {
			$manufacturer = $model->getManufacturer();
			$this->assignRef('manufacturer',	$manufacturer);
			
		} else {
		$manufacturers = $model->getManufacturers(true, true);
		$this->assignRef('manufacturers',	$manufacturers);
		}


		parent::display($tpl);
	}

}
// pure php no closing tag
