<?php if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); ?>

<?php
if (!$alreadycommented) {
	?>
	<script language="JavaScript" type="text/javascript">//<![CDATA[
	function check_reviewform() {
	    var form = document.getElementById('reviewform');
	
	    var ausgewaehlt = false;
	    for (var i=0; i<form.user_rating.length; i++)
	       if (form.user_rating[i].checked)
	          ausgewaehlt = true;
	    if (!ausgewaehlt)  {
	      alert('<?php echo JText::_('VM_REVIEW_ERR_RATE',false)  ?>');
	      return false;
	    }
	    else if (form.comment.value.length < <?php echo VM_REVIEWS_MINIMUM_COMMENT_LENGTH ?>) {
	      alert('<?php echo sprintf( JText::_('VM_REVIEW_ERR_COMMENT1',false),VM_REVIEWS_MINIMUM_COMMENT_LENGTH )  ?>');
	      return false;
	    }
	    else if (form.comment.value.length > <?php echo VM_REVIEWS_MAXIMUM_COMMENT_LENGTH ?>) {
	      alert('<?php echo sprintf( JText::_('VM_REVIEW_ERR_COMMENT2',false), VM_REVIEWS_MAXIMUM_COMMENT_LENGTH )  ?>');
	      return false;
	    }
	    else {
	      return true;
	    }
	}
	function refresh_counter() {
	  var form = document.getElementById('reviewform');
	  form.counter.value= form.comment.value.length;
	}
	//]]>
	</script>

    <h4><?php echo JText::_('VM_WRITE_REVIEW')  ?></h4>
    <br /><?php echo JText::_('VM_REVIEW_RATE')  ?>
    <form method="post" action="<?php echo URL  ?>index.php" name="reviewForm" id="reviewform">
    <table cellpadding="5" summary="<?php echo JText::_('VM_REVIEW_RATE') ?>">
      <tr>
        <th id="five_stars">
        	<label for="user_rating5"><img alt="5 stars" src="<?php echo VM_THEMEURL ?>images/stars/5.gif" border="0" /></label>
        </th>
        <th id="four_stars">
        	<label for="user_rating4"><img alt="4 stars" src="<?php echo VM_THEMEURL ?>images/stars/4.gif" border="0" /></label>
        </th>
        <th id="three_stars">
        	<label for="user_rating3"><img alt="3 stars" src="<?php echo VM_THEMEURL ?>images/stars/3.gif" border="0" /></label>
        </th>
        <th id="two_stars">
        	<label for="user_rating2"><img alt="2 stars" src="<?php echo VM_THEMEURL ?>images/stars/2.gif" border="0" /></label>
        </th>
        <th id="one_star">
        	<label for="user_rating1"><img alt="1 star" src="<?php echo VM_THEMEURL ?>images/stars/1.gif" border="0" /></label>
        </th>
        <th id="null_stars">
        	<label for="user_rating0"><img alt="0 stars" src="<?php echo VM_THEMEURL ?>images/stars/0.gif" border="0" /></label>
        </th>
      </tr>
      <tr>
        <td headers="five_stars" style="text-align:center;">
          <input type="radio" id="user_rating5" name="user_rating" value="5" />
        </td>
        <td headers="four_stars" style="text-align:center;">
        	<input type="radio" id="user_rating4" name="user_rating" value="4" />
        </td>
        <td headers="three_stars" style="text-align:center;">
        	<input type="radio" id="user_rating3" name="user_rating" value="3" />
        </td>
        <td headers="two_stars" style="text-align:center;">
        	<input type="radio" id="user_rating2" name="user_rating" value="2" />
        </td>
        <td headers="one_star" style="text-align:center;">
        	<input type="radio" id="user_rating1" name="user_rating" value="1" />
        </td>
        <td headers="null_stars" style="text-align:center;">
        	<input type="radio" id="user_rating0" name="user_rating" value="0" />
        </td>
      </tr>
    </table>
    <br /><br /><?php echo $review_comment  ?><br />
    <textarea title="<?php echo $review_comment ?>" class="inputbox" id="comment" onblur="refresh_counter();" onfocus="refresh_counter();" onkeypress="refresh_counter();" name="comment" rows="10" cols="55"></textarea>
    <br />
    <input class="button" type="submit" onclick="return( check_reviewform());" name="submit_review" title="<?php echo JText::_('VM_REVIEW_SUBMIT')  ?>" value="<?php echo JText::_('VM_REVIEW_SUBMIT')  ?>" />
    
    <div align="right"><?php echo JText::_('VM_REVIEW_COUNT')  ?>
    <input type="text" value="0" size="4" class="inputbox" name="counter" maxlength="4" readonly="readonly" />
    </div>
    
    <input type="hidden" name="product_id" value="<?php echo $product_id ?>" />
    <input type="hidden" name="option" value="<?php echo $option ?>" />
    <input type="hidden" name="page" value="<?php echo $page ?>" />
    <input type="hidden" name="category_id" value="<?php echo @intval($_REQUEST['category_id'])  ?>" />
    <input type="hidden" name="Itemid" value="<?php echo @$_REQUEST['Itemid']  ?>" />
    <input type="hidden" name="func" value="addReview" />
</form>
<?php
}
else {
	echo JText::_('VM_REVIEW_ALREADYDONE');
}

?>