<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage vendor
* @author Kohl Patrick, Eugen Stranz
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
	<h1><?php echo $this->vendor->vendor_store_name;
	if (!empty($this->vendor->images[0])) { ?>
		<div class="vendor-image">
		<?php echo $this->vendor->images[0]->displayMediaThumb('',false); ?>
		</div>
	<?php
	}
?>	</h1></div>

<div class="vendor-description">
<?php echo $this->vendor->vendor_store_desc.'<br>';

	foreach($this->userFields as $userfields){

		foreach($userfields['fields'] as $item){
			if(!empty($item['value'])){
				if($item['name']==='agreed'){
					$item['value'] =  ($item['value']===0) ? JText::_('COM_VIRTUEMART_USER_FORM_BILLTO_TOS_NO'):JText::_('COM_VIRTUEMART_USER_FORM_BILLTO_TOS_YES');
				}
			?><!-- span class="titles"><?php echo $item['title'] ?></span -->
						<span class="values vm2<?php echo '-'.$item['name'] ?>" ><?php echo $this->escape($item['value']) ?></span>
					<?php if ($item['name'] != 'title' and $item['name'] != 'first_name' and $item['name'] != 'middle_name' and $item['name'] != 'zip') { ?>
						<br class="clear" />
					<?php
				}
			}
		}
	}

	?></div>

<?php	echo $this->vendor->vendor_legal_info; ?>

	<br class="clear" />
	<?php echo $this->linktos ?>

	<br class="clear" />

	<?php echo $this->linkcontact ?>

	<br class="clear" />
