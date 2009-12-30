<?php if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );

if($empty_cart) { ?>
    
    <div style="margin: 0 auto;">
    <?php if(!$vmMinicart) { ?>
        <a href="http://virtuemart.org/" target="_blank">
        <img src="<?php echo $mm_action_url ?>components/com_virtuemart/shop_image/ps_image/menu_logo.gif" alt="VirtueMart" width="80" border="0" /></a>
        <br />
    <?php }
    echo JText::_('VM_EMPTY_CART') ?>
    </div>
<?php } 
else {
    // Loop through each row and build the table
    foreach( $minicart as $cart ) { 		

		foreach( $cart as $attr => $val ) {
			// Using this we make all the variables available in the template
			// translated example: $this->set( 'product_name', $product_name );
			$this->set( $attr, $val );
		}
        if(!$vmMinicart) { // Build Minicart
            ?>
            <div style="float: left;">
            <?php echo $cart['quantity'] ?>&nbsp;x&nbsp;<a href="<?php echo $cart['url'] ?>"><?php echo $cart['product_name'] ?></a>
            </div>
            <div style="float: right;">
            <?php echo $cart['price'] ?>
            </div>
            <br style="clear: both;" />
            <?php echo $cart['attributes'];
        }
    }
}
if(!$vmMinicart) { ?>
    <hr style="clear: both;" />
<?php } ?>
<div style="float: left;" >
<?php echo $total_products ?>
</div>
<div style="float: right;">
<?php echo $total_price ?>
</div>
<?php if (!$empty_cart && !$vmMinicart) { ?>
    <br/><br style="clear:both" /><div align="center">
    <?php echo $show_cart ?>
    </div><br/>

<?php } 
echo $saved_cart;
?>