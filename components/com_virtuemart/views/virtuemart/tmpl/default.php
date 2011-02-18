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
?>

<?php // Vendor Store Description
if (!empty($this->vendor->vendor_store_desc)) { ?>
<div class="vendor-store-desc">
	<h1><?php echo JText::_('VM_STORE_FORM_DESCRIPTION') ?></h1>
	<?php /** @todo Add vendor description */
	echo $this->vendor->vendor_store_desc; ?>
</div>
<?php } ?>

<?php

// load categories from front_categories if exist
if ($this->categories) echo $this->loadTemplate('categories');

//Console::logSpeed('Load featured');
/* Show Featured Products */
if (VmConfig::get('showFeatured', 1) && $this->featuredProducts) echo $this->loadTemplate('featuredproducts');

//Console::logSpeed('Load recent');
/* Recent products */
if ($this->recentProducts) echo $this->loadTemplate('recentproducts');
/* Topten products */
if ($this->toptenProducts) echo $this->loadTemplate('toptenproducts');
// load categories from front_categories if exist
if ($this->latestProducts) echo $this->loadTemplate('latestproducts');

?>