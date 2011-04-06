<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 
JHTML::stylesheet ( 'menucss.css', 'modules/mod_virtuemart_category/css/', false );
?>

<ul class="VMmenu<?php echo $class_sfx ?>" ID="<?php echo "VMmenu".$ID ?>" > 
<?php foreach ($categories as $category) { 
		 $active_menu = 'class="inactive"';
		$caturl = JRoute::_('index.php?option=com_virtuemart&view=category&category_id='.$category->category_id);
		$cattext = $category->category_name;
		//if ($active_category_id == $category->category_id) $active_menu = 'class="active"';
		if (in_array( $category->category_id, $parentCategories)) $active_menu = 'class="active"';

		?>
			
<li <?php echo $active_menu ?>>
	<div >
		<?php echo JHTML::link($caturl, $cattext); 
		if ($category->childs) {
			?>
			<span class="VmArrowdown"> <span>
			<?php
		}
		?>
	</div>
<?php if (($category->childs and $Accordeon) or ($active_menu=='class="active"' and !$Accordeon)) { ?>
<ul class="menu<?php echo $class_sfx; ?>">
<?php
		foreach ($category->childs as $child) {
	
		$caturl = JRoute::_('index.php?option=com_virtuemart&view=category&category_id='.$child->category_id);
		$cattext = $child->category_name;
		?>
			
<li>
	<div ><?php echo JHTML::link($caturl, $cattext); ?></div>
</li>
<?php		} ?>
</ul>
<?php 	} ?>
</li>
<?php
	} ?>
</ul>
