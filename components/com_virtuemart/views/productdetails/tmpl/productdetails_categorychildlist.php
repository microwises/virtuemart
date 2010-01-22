<?php if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'image.php');
$iCol = 1;
if (empty($this->category->categories_per_row)) {
	$this->category->categories_per_row = 4;
}
$cellwidth = intval( 100 / $this->category->categories_per_row );
?>
<br/>
<table width="100%" cellspacing="0" cellpadding="0">
	<?php
	foreach($this->category->children as $category ) {
		if ($iCol == 1) { // this is an indicator wether a row needs to be opened or not
			echo "<tr>\n";
		}
		?>
		<td align="center" width="<?php echo $cellwidth ?>%" >
			<br />
			<?php
			$url = JRoute::_('index.php?option=com_virtuemart&view=category&task=browse&category_id='.$category->category_id);
			echo JHTML::link($url, ImageHelper::getShopImageHtml($category->category_thumb_image, 'category', 'alt="'.$category->category_name.'"', false).'<br /><br />'.$category->category_name.' ('.$category->number_of_products.')');
			?>
			<br/>
		</td>
		
		<?php
		// Do we need to close the current row now?
		if ($iCol == $this->category->categories_per_row) { // If the number of products per row has been reached
			echo "</tr>\n";
			$iCol = 1;
		}
		else {
			$iCol++;
		}
	}
	// Do we need a final closing row tag?
	if ($iCol != 1) {
		echo "</tr>\n";
	}
	?>
</table>