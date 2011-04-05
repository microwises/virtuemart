<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>

<ul class="menu<?php echo $class_sfx; ?>">
<?php foreach ($categories as $category) {
		$active_menu='';
		$caturl = JRoute::_('index.php?option=com_virtuemart&view=category&category_id='.$category->category_id);
		$cattext = $category->category_name;
		if ($active_category_id == $category->category_id) $active_menu = 'class="active"';
		?>
			
<li <?php echo $active_menu ?>>
	<div ><?php echo JHTML::link($caturl, $cattext); ?></div>
<?php if ($category->childs) { ?>
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
