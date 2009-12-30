<?php if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); ?>


<form method="post" action="<?php echo $mm_action_url ?>index.php" name="reviewForm" id="reviewform"> 
    <input class="addtocart_button" type="submit"  name="submit_review" title="<?php echo JText::_('VM_REVIEW_SUBMIT')  ?>" value="Add to Favourites" />
    <input type="hidden" name="product_id" value="<?php echo $product_id ?>" />
    <input type="hidden" name="option" value="<?php echo $option ?>" />
    <input type="hidden" name="page" value="<?php echo $page ?>" />
    <input type="hidden" name="category_id" value="<?php echo @intval($_REQUEST['category_id'])  ?>" />
    <input type="hidden" name="flypage" value="<?php echo $flypage  ?>" />
    <input type="hidden" name="Itemid" value="<?php echo @$_REQUEST['Itemid']  ?>" />
    <input type="hidden" name="func" value="addFavourite" />
</form>
