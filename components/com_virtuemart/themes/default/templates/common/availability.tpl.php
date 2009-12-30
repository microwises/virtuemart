<?php if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); ?>


	<?php
	ob_start();
	
	if (($product_in_stock < 1 && CHECK_STOCK) || $product_available_date > time() ) { 
		// Product is not in stock or not available yet (Availability date in future) ?>
		<?php echo JText::_('VM_CURRENTLY_NOT_AVAILABLE') ?>
		<br />
				
		<?php 	
	}
	// Product will be available soon!!
	if($product_available_date > time()) { ?>
			<?php 
			echo JText::_('VM_PRODUCT_AVAILABLE_AGAIN')
				. date("d.m.Y", $product_available_date ); 
			?>
			<br /><br />
			<?php 
	}
	// Yes, we have XX products in stock!
	elseif( ($product_in_stock >= 1 && CHECK_STOCK) ) {
		?><span style="font-weight:bold;">
			<?php echo JText::_('VM_PRODUCT_FORM_IN_STOCK') ?>: 
		  </span><?php echo $product_in_stock ?>
		  <br /><br />
		  <?php
	}

	// Delivery time!
	// Ships in 24hrs, 48hrs, ....
	if( $product_availability ) { ?>
		<span style="font-weight:bold;">
			<?php echo JText::_('VM_DELIVERY_TIME') ?>: 
		</span>
		<br /><br />
		<?php
		if( CHECK_STOCK == '1' && !$product_in_stock ) {
			$product_availability = 'not_available.gif';
		}
		if( is_file( VM_THEMEPATH."images/availability/".$product_availability)) {
			echo vmCommonHTML::imageTag( VM_THEMEURL."images/availability/".$product_availability, $product_availability );
		}
		else {
			echo $product_availability;
		}
	}
	$avail = ob_get_contents();
	ob_end_clean();
	if( !empty( $avail ) ) { 
		?>
		<div class="availabilityHeader"><?php echo JText::_('VM_AVAILABILITY') ?></div>
		<br />
		<?php
		echo $avail;
	}
?>