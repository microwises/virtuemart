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

<!-- List of recent products -->
<h3><?php echo JText::_('VM_RECENT_PRODUCTS') ?></h3>
<ul class="vmRecentDetail">
<?php 
foreach ($this->recentProducts as $recent ) { // Loop through all recent products
	/**
	 * Available indexes:
	 * 
	 * $recent["product_name"] => The user ID of the comment author
	 * $recent["category_name"] => The username of the comment author
	 * $recent["product_thumb_image"] => The name of the comment author
	 * $recent["product_url"] => The UNIX timestamp of the comment ("when" it was posted)
	 * $recent["category_url"] => The rating; an integer from 1 - 5
	 * $recent["product_s_desc"] => The comment text
	 * 
	 */
	?>
	<li>
	<a href="<?php echo $recent["product_url"]; ?>" >
	<?php echo $recent["product_name"]; ?></a>&nbsp;(<?php echo JText::_('VM_CATEGORY') ?>:&nbsp;
	<a href="<?php echo $recent["category_url"]; ?>" ><?php echo $recent["category_name"]; ?></a>)
	</li>
	<?php
}
?>
</ul>