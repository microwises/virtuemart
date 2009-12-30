<?php if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); ?>

<!-- The "Vote for a product" Form -->
<strong><?php echo JText::_('VM_CAST_VOTE') ?>:</strong>&nbsp;&nbsp;

<form method="post" action="<?php echo $mm_action_url ?>index.php">
    <select name="user_rating" class="inputbox">
        <option value="5">5</option>
        <option value="4">4</option>
        <option selected="selected" value="3">3</option>
        <option value="2">2</option>
        <option value="1">1</option>
        <option value="0">0</option>
    </select>
    <input class="button" type="submit" name="submit_vote" value="<?php echo JText::_('VM_RATE_BUTTON') ?>" />
    <input type="hidden" name="product_id" value="<?php echo $product_id ?>" />
    <input type="hidden" name="option" value="<?php echo $option ?>" />
    <input type="hidden" name="page" value="<?php echo $page ?>" />
    <input type="hidden" name="category_id" value="<?php echo @intval($_REQUEST['category_id']) ?>" />
    <input type="hidden" name="Itemid" value="<?php echo @intval($_REQUEST['Itemid']) ?>" />
    <input type="hidden" name="func" value="addVote" />
</form>