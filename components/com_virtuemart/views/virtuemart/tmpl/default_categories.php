<?php defined('_JEXEC') or die('Restricted access'); ?>
	<div class="category-view">
	<?php
	echo "<h4>".JText::_('VM_CATEGORIES')."</h4>";

	$iCol = 1;

	// calculation of the categories per row
	$categories_per_row = VmConfig::get('categories_per_row',3);
	$cellwidth = floor( 100 / $categories_per_row);


foreach ($this->categories as $category) {

		if ($iCol == 1) { // this is an indicator wether a row needs to be opened or not ?>
		<div class="category-row">
		<?php }
			?>

		<!-- Category Listing Output -->
		<div class="width<?php echo $cellwidth ?> floatleft center">
			<?php $caturl = JRoute::_('index.php?option=com_virtuemart&view=category&category_id='.$category->category_id); ?>
			<h2>
				<a href="<?php echo $caturl ?>" title="<?php echo $category->category_name ?>">
				<?php echo $category->category_name ?><span><?php // echo ' ()'?></span><br />
				<?php if ($category->category_thumb_image) {
					echo VmImage::getImageByCat($category)->displayImage();
				} ?>
				</a>
			</h2>
		</div>

			<?php
		// Do we need to close the current row now?
		if ($iCol == $categories_per_row) { // If the number of products per row has been reached
			echo "<div class='clear'></div></div>";
			$iCol = 1;
		}
		else {
			$iCol++;
		}
	}
	// Do we need a final closing row tag?
	if ($iCol != 1) {
		echo "<div class='clear'></div></div>";
		}
		?>
	<div class="clear"></div>
	</div>
