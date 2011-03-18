<?php
/**
*
* Show the products in a category
*
* @package	VirtueMart
* @subpackage
* @author RolandD
* @author Max Milbers
* @todo add pagination
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: default.php 2710 2011-02-13 00:51:06Z Electrocity $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/* Show child categories */
if ($this->category->haschildren) {
	?>
	<div class="category-view">
	<?php
	$iTopTenCol = 1;

	// calculation of the categories per row
	$categories_per_row = VmConfig::get('categories_per_row',3);
	$TopTen_cellwidth = floor( 100 / $categories_per_row);


	foreach ($this->category->children as $category ) {

		if ($iTopTenCol == 1) { // this is an indicator wether a row needs to be opened or not ?>
			<div class="category-row">
		<?php }
				?>

		<!-- Category Listing Output -->
		<div class="width<?php echo $TopTen_cellwidth ?> floatleft center">
			<?php $caturl = JRoute::_('index.php?option=com_virtuemart&view=category&category_id='.$category->category_id); ?>
			<h3>
				<a href="<?php echo $caturl ?>" title="<?php echo $category->category_name ?>">
				<?php echo $category->category_name ?><span><?php echo ' ('.$category->number_of_products.')'?></span><br />
				<?php if ($category->category_thumb_image) {
					echo VmImage::getImageByCat($category)->displayImage();
				} ?>
				</a>
			</h3>
		</div>

		<?php
		// Do we need to close the current row now?
		if ($iTopTenCol == $categories_per_row) { // If the number of products per row has been reached
			echo "<div class='clear'></div></div>";
			$iTopTenCol = 1;
		}
		else {
			$iTopTenCol++;
		}
	}
	// Do we need a final closing row tag?
	if ($iTopTenCol != 1) {
		echo "<div class='clear'></div></div>";
	}
	?>
	<div class="clear"></div>
	</div>

<div class="horizontal-separator margintop20 marginbottom20"></div>
<?php } ?>

