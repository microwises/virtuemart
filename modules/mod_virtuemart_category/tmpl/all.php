<?php // no direct access
defined('_JEXEC') or die('Restricted access');
//JHTML::stylesheet ( 'menucss.css', 'modules/mod_virtuemart_category/css/', false );
?>

<ul class="menu<?php echo $class_sfx ?>" >
<?php foreach ($categories as $category) {
		 $active_menu = '';
		$caturl = JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id='.$category->virtuemart_category_id);
		$cattext = $category->category_name;
		//if ($active_category_id == $category->virtuemart_category_id) $active_menu = 'class="active"';
		if (in_array( $category->virtuemart_category_id, $parentCategories)) $active_menu = 'class="active"';

		?>

<li <?php echo $active_menu ?>>
	<div>
		<?php echo JHTML::link($caturl, $cattext); ?>
	</div>
<?php if ($category->childs ) {


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
<?php } ?>
</ul>
<?php 	} ?>
</li>
<?php
	} ?>
</ul>
