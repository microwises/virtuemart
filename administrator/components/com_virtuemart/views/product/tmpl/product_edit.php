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
AdminMenuHelper::startAdminArea();
/* Load some behaviour */
$document = JFactory::getDocument();
$document->addScript(JURI::base().'components/com_virtuemart/assets/js/jquery.autocomplete.pack.js');
$document->addStyleSheet(JURI::base().'components/com_virtuemart/assets/css/jquery.autocomplete.css');
?>
<form method="post" name="adminForm" action="index.php" enctype="multipart/form-data">
<?php
	echo $this->pane->startPane("product-pane");
	echo $this->pane->startPanel( JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRODUCT_INFO_LBL'), 'product_information' );
		echo $this->loadTemplate('information');
	echo $this->pane->endPanel();
	echo $this->pane->startPanel( JText::_('COM_VIRTUEMART_PRODUCT_FORM_DESCRIPTION'), 'product_description' );
		echo $this->loadTemplate('description');
	echo $this->pane->endPanel();
	echo $this->pane->startPanel( JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRODUCT_STATUS_LBL'), 'product_status' );
		echo $this->loadTemplate('status');
	echo $this->pane->endPanel();
	echo $this->pane->startPanel( JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRODUCT_VARIANTS_LBL'), 'product_variants' );
		echo $this->loadTemplate('variants');
	echo $this->pane->endPanel();
	echo $this->pane->startPanel( JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRODUCT_DIM_WEIGHT_LBL'), 'product_dimensions' );
		echo $this->loadTemplate('dimensions');
	echo $this->pane->endPanel();
	echo $this->pane->startPanel( JText::_('COM_VIRTUEMART_PRODUCT_FORM_PRODUCT_IMAGES_LBL'), 'product_images' );
		echo $this->loadTemplate('images');
	echo $this->pane->endPanel();
	if (isset($this->waitinglist) && count($this->waitinglist) > 0) {
		echo $this->pane->startPanel( JText::_('COM_VIRTUEMART_PRODUCT_WAITING_LIST_TAB'), 'product_waitinglist' );
			echo $this->loadTemplate('waitinglist');
		echo $this->pane->endPanel();
	}
	echo $this->pane->startPanel( JText::_('COM_VIRTUEMART_META_INFORMATION'), 'product_metadata' );
		echo $this->loadTemplate('metadata');
	echo $this->pane->endPanel();
	echo $this->pane->endPane();

?>
<!-- Hidden Fields -->
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="option" value="com_virtuemart" />
<input type="hidden" name="view" value="product" />
<input type="hidden" name="controller" value="product" />
<input type="hidden" name="product_id" value="<?php echo $this->product->product_id; ?>" />
<input type="hidden" name="product_parent_id" value="<?php echo JRequest::getInt('product_parent_id', $this->product->product_parent_id); ?>" />
<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
</form>
<?php AdminMenuHelper::endAdminArea(); ?>