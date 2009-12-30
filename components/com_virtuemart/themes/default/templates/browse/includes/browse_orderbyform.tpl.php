<?php if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
mm_showMyFileName(__FILE__); ?>

<!-- ORDER BY .... FORM -->
<form action="<?php echo $mm_action_url."index.php" ?>" method="get" name="order">

<?php
if( !empty( $VM_BROWSE_ORDERBY_FIELDS )) {
	echo $this->fetch( 'browse/includes/browse_orderbyfields.tpl.php');

// This is the toggle button for Descending / Ascending Order
// It is wrapped into a JS function with a noscript area to keep it accessible
echo mm_writeWithJS('&nbsp;<input type="hidden" name="DescOrderBy" value="'.$asc_desc[0].'" /><a href="javascript: document.order.DescOrderBy.value=\''.$asc_desc[1].'\'; document.order.submit()"><img src="'. $mosConfig_live_site."/images/M_images/$icon"  .'" border="0" alt="'. JText::_('VM_PARAMETER_SEARCH_'.$asc_desc[0].'ENDING_ORDER') .'" title="'.JText::_('VM_PARAMETER_SEARCH_'.$asc_desc[0].'ENDING_ORDER') .'" width="12" height="12" /></a>',
      '<select class="inputbox" name="DescOrderBy">
            <option '.$selected[0].' value="DESC">'.JText::_('VM_PARAMETER_SEARCH_DESCENDING_ORDER').'</option>
            <option '.$selected[1].' value="ASC">'.JText::_('VM_PARAMETER_SEARCH_ASCENDING_ORDER').'</option>
        </select>
        <input class="button" type="submit" value="'.JText::_('VM_SUBMIT').'" />');
}
?>
    <input type="hidden" name="Itemid" value="<?php echo $Itemid ?>" />
    <input type="hidden" name="option" value="com_virtuemart" />
    <input type="hidden" name="page" value="shop.browse" />
    <input type="hidden" name="category_id" value="<?php echo $category_id ?>" />
    <input type="hidden" name="manufacturer_id" value="<?php echo $manufacturer_id ?>" />
    <input type="hidden" name="keyword" value="<?php echo $keyword ?>" />
    <input type="hidden" name="keyword1" value="<?php echo $keyword1 ?>" />
    <input type="hidden" name="keyword2" value="<?php echo $keyword2 ?>" />
    
<?php
if( !empty( $product_type_id )) {
	echo '<input type="hidden" name="product_type_id" value="'.$product_type_id.'" />'; 
	echo $ps_product_type->get_parameter_form($product_type_id);
}
?></div><?php
if( $show_top_navigation ) {
	?>
	&nbsp;&nbsp;&nbsp;&nbsp;<?php echo JText::_('PN_DISPLAY_NR') ?>&nbsp;&nbsp;
	<?php $pagenav->writeLimitBox('',$category_id); ?>
	<noscript><input type="submit" value="<?php echo JText::_('VM_SUBMIT') ?>" /></noscript>
	
    <!-- PAGE NAVIGATION AT THE TOP -->
    <br/>
    <div style="text-align:center;"><?php 
		if($prodlimit) {
			$pagenav->writePagesLinks( $search_string );
			echo '<br />';
    		$pagenav->writePagesCounter();
		}
      ?>
    </div>
	<?php
}  	
?>
</form>