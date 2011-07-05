<?php
/**
*
* UpdatesMigration View
*
* @package	VirtueMart
* @subpackage UpdatesMigration
* @author Max Milbers
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

/**
 * HTML View class for maintaining the Installation. Updating of the files and imports of the database should be done here
 *
 * @package	VirtueMart
 * @subpackage UpdatesMigration
 * @author Max Milbers
 */
class VirtuemartViewUpdatesMigration extends JView {

    function display($tpl = null) {

	$this->loadHelper('adminui');
	$latestVersion = JRequest::getVar('latestverison', '');

	JToolBarHelper::title(JTEXT::_('COM_VIRTUEMART_UPDATE_MIGRATION'), 'vm_config_48');

	$this->loadHelper('connection');
	$this->loadHelper('image');
	$model = $this->getModel();

	$this->assignRef('checkbutton_style', $checkbutton_style);
	$this->assignRef('downloadbutton_style', $downloadbutton_style);
	$this->assignRef('latestVersion', $latestVersion);

	parent::display($tpl);
    }

}
// pure php no closing tag
