<?php
/**
*
* Manufacturer View
*
* @package	VirtueMart
* @subpackage Manufacturer
* @author Kohl Patrick
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
 * @author Kohl Patrick
 */
class VirtuemartViewManufacturer extends JView {

	function display($tpl = null) {

		$document = JFactory::getDocument();
		$mainframe = JFactory::getApplication();
		$pathway = $mainframe->getPathway();

		/* Set the helper */
		$this->addHelperPath(JPATH_VM_ADMINISTRATOR.DS.'helpers');
		$this->loadHelper('image');

		$manufacturer_id = JRequest::getInt('manufacturer_id', 0);
		$mf_categorie_id = JRequest::getInt('mf_categorie_id', 0);
		// get necessary models
		$model = & $this->getModel('manufacturer');
		if ($manufacturer_id ) {
			$manufacturer = $model->getManufacturer();
			$model->addImagesToManufacturer($manufacturer);

			$document->setTitle(JText::_('COM_VIRTUEMART_MANUFACTURER_DETAILS').' '.$manufacturer->mf_name);

			$this->assignRef('manufacturerImage', $manufacturerImage);
			$this->assignRef('manufacturer',	$manufacturer);
			$pathway->addItem($manufacturer->mf_name);

		} else {
		$document->setTitle(JText::_('COM_VIRTUEMART_MANUFACTURER_PAGE')) ;
		$manufacturers = $model->getManufacturers(true, true);
		$model->addImagesToManufacturer($manufacturers);
		$this->assignRef('manufacturers',	$manufacturers);
		}


		parent::display($tpl);
	}

}
// pure php no closing tag
