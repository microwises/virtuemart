<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage vendor
* @author Patrick Kohl, Max Milbers
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: default.php 2701 2011-02-11 15:16:49Z impleri $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
?>

<div class="vendor-details-view">
	<h1><?php echo $this->vendor->vendor_store_name; ?></h1>
	<h3><?php echo $this->vendor->vendor_name; ?></h3>

	<div class="spacer">

	<?php // vendor Description
	if(!empty($this->vendor->vendor_terms_of_service  )) { ?>
		<div class="vendor-description">
			<?php echo $this->vendor->vendor_terms_of_service   ?>
		</div>
	<?php } ?>

	<div class="clear"></div>
	</div>
</div>

	<?php
		$link = JROUTE::_('index.php?option=com_virtuemart&view=vendor&virtuemart_vendor_id=' . $this->vendor->virtuemart_vendor_id);

		?>
		<a href="<?php echo $link;  ?>">
		<?php echo JText::_('MOD_VIRTUEMART_VENDOR_DETAIL');

				echo $this->vendor->images[0]->displayMediaThumb('',false);
		?>
			</a>

	<br style='clear:both;' />

	<?php
		$link = JROUTE::_('index.php?option=com_virtuemart&view=vendor&task=contact&virtuemart_vendor_id=' . $this->vendor->virtuemart_vendor_id);
		?>
			<a href="<?php echo $link; ?>"><?php echo JText::_('MOD_VIRTUEMART_VENDOR_CONTACT'); ?>	</a>

		<?php
	?>

	<br style='clear:both;' />
