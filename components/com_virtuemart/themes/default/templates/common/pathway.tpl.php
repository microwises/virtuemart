<?php if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); ?>

<?php 
$catcount = 1;
$count = count( $pathway );

// Remove the link on the last pathway item
$pathway[ $count - 1 ]->link = '';

foreach( $pathway as $item ) { ?>
	<?php if( !empty( $item->link ) ) : ?>
	<a class="pathway" href="<?php echo $item->link ?>"><?php echo $item->name ?></a>
	<?php else: ?>
	<?php echo $item->name ?>
	<?php endif; ?>

<?php

	if( $catcount < $count || $item->link != '') {
		// This prints the separator image (uses the one from the template if available!)
		// Cat1 * Cat2 * ...
		echo vmCommonHTML::pathway_separator();
		
	}
	$catcount++;
}
if( isset( $return_link ) && !empty( $return_link ) ) {
    echo $return_link;
}
 ?>