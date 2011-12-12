<?php // no direct access
defined('_JEXEC') or die('Restricted access');
$col= 1 ;
?>
<div class="vmgroup<?php echo $params->get( 'moduleclass_sfx' ) ?>">

<?php if ($headerText) : ?>
	<div class="vmheader"><?php echo $headerText ?></div>
<?php endif;
if ($display_style =="div") { ?>
	<div class="vmmanufacturer<?php echo $params->get('moduleclass_sfx'); ?>">
	<?php foreach ($manufacturers as $manufacturer) {
		$link = JROUTE::_('index.php?option=com_virtuemart&view=manufacturer&virtuemart_manufacturer_id=' . $manufacturer->virtuemart_manufacturer_id);

		?>
		<div style="float:left;">
			<a href="<?php echo $link; ?>">
		<?php
		if ($manufacturer->images && ($show == 'image' or $show == 'all' )) { ?>
			<?php echo $manufacturer->images[0]->displayMediaThumb('',false);?>
		<?php
		}
		if ($show == 'text' or $show == 'all' ) { ?>
		 <div><?php echo $manufacturer->mf_name; ?></div>
		<?php
		} ?>
			</a>
		</div>
		<?php
		if ($col == $manufacturers_per_row) {
			echo "</div><div style='clear:both;'>";
			$col= 1 ;
		} else {
			$col++;
		}
	} ?>
	</div>
	<br style='clear:both;' />

<?php
} else {
	$last = count($manufacturers)-1;
?>

<ul class="vmmanufacturer<?php echo $params->get('moduleclass_sfx'); ?>">
<?php
foreach ($manufacturers as $manufacturer) {
	$link = JROUTE::_('index.php?option=com_virtuemart&view=manufacturer&virtuemart_manufacturer_id=' . $manufacturer->virtuemart_manufacturer_id);
	?>
	<li><a href="<?php echo $link; ?>">
		<?php
		if ($manufacturer->images && ($show == 'image' or $show == 'all' )) { ?>
			<?php echo $manufacturer->images[0]->displayMediaThumb('',false);?>
		<?php
		}
		if ($show == 'text' or $show == 'all' ) { ?>
		 <div><?php echo $manufacturer->mf_name; ?></div>
		<?php
		}
		?>
		</a>
	</li>
	<?php
	if ($col == $manufacturers_per_row && $manufacturers_per_row && $last) {
		echo '</ul><ul class="vmmanufacturer'.$params->get('moduleclass_sfx').'">';
		$col= 1 ;
	} else {
		$col++;
	}
	$last--;
} ?>
</ul>

<?php }
	if ($footerText) : ?>
	<div class="vmfooter<?php echo $params->get( 'moduleclass_sfx' ) ?>">
		 <?php echo $footerText ?>
	</div>
<?php endif; ?>
</div>
