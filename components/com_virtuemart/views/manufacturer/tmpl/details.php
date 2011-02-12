<?php
/**
*
* Description
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
* @version $Id: default.php 2701 2011-02-11 15:16:49Z impleri $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
?>

	    <?php
		?>
		<div>
		    <a href="<?php echo $this->manufacturer->mf_url ?>"><?php echo $this->manufacturer->mf_name; ?></a>

		</div>
		<div>
			<?php echo JText::_($this->manufacturer->mf_email); ?>
		</div>
		<div>
			<?php echo JText::_($this->manufacturer->mf_desc); ?>
		</div>
		<div>
			<?php echo JText::_($this->manufacturer->mf_url); ?>
		</div>

