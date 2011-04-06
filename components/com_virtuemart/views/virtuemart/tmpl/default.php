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
JHTML::_( 'behavior.modal' );
?>

<?php // Vendor Store Description
if (!empty($this->vendor->vendor_store_desc)) { ?>
<div class="vendor-store-desc">
	<?php /** @todo Add vendor description */
	echo $this->vendor->vendor_store_desc; ?>
</div>
<?php } ?>

<?php

// load categories from front_categories if exist
if ($this->categories) echo $this->loadTemplate('categories');

//Console::logSpeed('Load featured');
/* Show Featured Products */
if (VmConfig::get('show_featured', 1) && $this->featuredProducts) echo $this->loadTemplate('featuredproducts');

//Console::logSpeed('Load recent');
/* Recent products */
if (VmConfig::get('show_recent', 1) && $this->recentProducts) echo $this->loadTemplate('recentproducts');
/* Topten products */
if (VmConfig::get('show_topTen', 1) && $this->toptenProducts) echo $this->loadTemplate('toptenproducts');
// load categories from front_categories if exist
if (VmConfig::get('show_latest', 1) && $this->latestProducts) echo $this->loadTemplate('latestproducts');

?>