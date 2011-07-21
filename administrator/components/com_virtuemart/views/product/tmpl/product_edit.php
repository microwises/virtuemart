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
AdminUIHelper::startAdminArea();

/* Load some behaviour */
jimport('joomla.html.pane');
$pane = JPane::getInstance();
//JHTML::_('behavior.tooltip');
JHTML::_('behavior.calendar');

$document = JFactory::getDocument();
$document->addScript(JURI::base().'components/com_virtuemart/assets/js/jquery.autocomplete.pack.js');
$document->addStyleSheet(JURI::base().'components/com_virtuemart/assets/css/jquery.autocomplete.css');
VmConfig::JvalideForm();
$this->editor = JFactory::getEditor();

?>
<form method="post" name="adminForm" action="index.php" enctype="multipart/form-data" ID="adminform">

<?php // Loading Templates in Tabs
$tabarray = array();
$tabarray['information'] = 'COM_VIRTUEMART_PRODUCT_FORM_PRODUCT_INFO_LBL';
$tabarray['description'] = 'COM_VIRTUEMART_PRODUCT_FORM_DESCRIPTION';
$tabarray['status'] = 'COM_VIRTUEMART_PRODUCT_FORM_PRODUCT_STATUS_LBL';
$tabarray['dimensions'] = 'COM_VIRTUEMART_PRODUCT_FORM_PRODUCT_DIM_WEIGHT_LBL';
$tabarray['images'] = 'COM_VIRTUEMART_PRODUCT_FORM_PRODUCT_IMAGES_LBL';
$tabarray['custom'] = 'COM_VIRTUEMART_PRODUCT_FORM_PRODUCT_CUSTOM_TAB';


if (isset($this->waitinglist) && count($this->waitinglist) > 0) { 
	$tabarray['waitinglist'] = 'COM_VIRTUEMART_PRODUCT_WAITING_LIST_TAB';
}

AdminUIHelper::buildTabs ( $tabarray, $this->_models['product']->_id );
// Loading Templates in Tabs END ?>


<!-- Hidden Fields -->
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="option" value="com_virtuemart" />
<input type="hidden" name="view" value="product" />
<input type="hidden" name="controller" value="product" />
<input type="hidden" name="virtuemart_product_id" value="<?php echo $this->product->virtuemart_product_id; ?>" />
<input type="hidden" name="product_parent_id" value="<?php echo JRequest::getInt('product_parent_id', $this->product->product_parent_id); ?>" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>
<?php AdminUIHelper::endAdminArea(); ?>