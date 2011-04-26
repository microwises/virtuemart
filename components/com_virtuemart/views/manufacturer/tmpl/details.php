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
?>

<div class="manufacturer-details-view">
	<div class="spacer">
	
		<?php // Manufacturer Image
		echo $this->manufacturer->images[0]->displayMediaThumb('','',0,0);?>
		
		<h1><?php echo $this->manufacturer->mf_name; ?></h1>
		
		<?php if(!empty($this->manufacturer->mf_email) || !empty($this->manufacturer->mf_url)) { ?>
		<div class="email-weblink">
		<?php 
		// TO DO Make The Email Visible Within The Lightbox
		// echo JHtml::_('email.cloak', $this->manufacturer->mf_email,true,JText::_('COM_VIRTUEMART_EMAIL'),false) ?>
		<a href="<?php echo $this->manufacturer->mf_url ?>"><?php echo JText::_('COM_VIRTUEMART_MANUFACTURER_PAGE') ?></a>
		</div>
		<?php } ?>
		
		<?php if(!empty($this->manufacturer->mf_desc)) { ?>
		<div class="description">
		<?php echo $this->manufacturer->mf_desc; ?>
		</div>
		<?php } ?>

	</div>	
</div>