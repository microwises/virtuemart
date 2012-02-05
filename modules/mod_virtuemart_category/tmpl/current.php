<?php // no direct access
defined('_JEXEC') or die('Restricted access');
//JHTML::stylesheet ( 'menucss.css', 'modules/mod_virtuemart_category/css/', false );
$ID = str_replace('.', '_', substr(microtime(true), -8, 8));
?>

<ul class="VMmenu<?php echo $class_sfx ?>" ID="<?php echo "VMmenu".$ID ?>" >
<?php foreach ($categories as $category) {
		 $active_menu = 'class="VmClose"';
		$caturl = JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id='.$category->virtuemart_category_id);
		$cattext = $category->category_name;
		//if ($active_category_id == $category->virtuemart_category_id) $active_menu = 'class="active"';
		if (in_array( $category->virtuemart_category_id, $parentCategories)) $active_menu = 'class="VmOpen"';

		?>

<li <?php echo $active_menu ?>>
	<div >
		<?php echo JHTML::link($caturl, $cattext);
		if ($category->childs) {
			?>
			<span class="VmArrowdown"> </span>
			<?php
		}
		?>
	</div>
<?php if ($active_menu=='class="VmOpen"') {


	?>
	<ul class="menu<?php echo $class_sfx; ?>">
	<?php
		foreach ($category->childs as $child) {

			$caturl = JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id='.$child->virtuemart_category_id);
			$cattext = $child->category_name;
			?>

			<li>
				<div ><?php echo JHTML::link($caturl, $cattext); ?></div>
			</li>
			<?php
		}
		?>
	</ul>
	<?php
}
?>
</li>
<?php
	} ?>
</ul>
