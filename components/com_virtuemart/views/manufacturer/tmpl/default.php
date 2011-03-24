<?php
/**
*
* Description
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
* @version $Id: default.php 2701 2011-02-11 15:16:49Z impleri $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
$i = 1 ;
	?>
<div class="category-row">
	<?php
	foreach ($this->manufacturers as $manufacturer) {
		$link = JROUTE::_('index.php?option=com_virtuemart&view=manufacturer&&manufacturer_id=' . $manufacturer->manufacturer_id);
		$manufacturerImage = VmImage::getImageByMf($manufacturer);
		$productlink = JROUTE::_('index.php?option=com_virtuemart&view=category&manufacturer_id=' . $manufacturer->manufacturer_id);
		?>
		
		<!-- Category Listing Output -->
		<div class="width33 floatleft center">
			<h3>
				<a href="<?php echo $link; ?>"><?php echo $manufacturer->mf_name; ?></a>
			
			<br />
				<a href="<?php echo $link; ?>"><?php echo $manufacturerImage->displayImage('','',1,1);?></a>
			</h3>
		</div>

		<?php
		if ($i==3){
		}
		$i++;
	}
	?>
</div>