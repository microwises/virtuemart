<?php if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); ?>

<!-- List of product reviews -->
<h4><?php echo JText::_('VM_REVIEWS') ?>:</h4>

<?php
/** @todo Handle review submission */
$alreadycommented = false;
foreach($this->product_reviews as $review ) { // Loop through all reviews
	/* Check if user already commented */
	if ($review->userid == $this->user->id) $alreadycommented = true;
	/**
	 * Available indexes:
	 * 
	 * $review->userid => The user ID of the comment author
	 * $review->username => The username of the comment author
	 * $review->name => The name of the comment author
	 * $review->time => The UNIX timestamp of the comment ("when" it was posted)
	 * $review->user_rating => The rating; an integer from 1 - 5
	 * $review->comment => The comment text
	 * 
	 */
	?>
	<strong><?php echo $review->username.'&nbsp;&nbsp;('.JHTML::date($review->time, JText::_('DATE_FORMAT_LC')).')'; ?></strong>
	<br />
	<?php 
		echo JText::_('VM_RATE_NOM');
		$url = JURI::root().'components/com_virtuemart/shop_image/reviews/'.$review->user_rating.'.gif';
		echo JHTML::image($url, $review->user_rating, array('border' => 0));
	?>
	<br />
	<blockquote><div><?php echo wordwrap($review->comment, 150, "<br/>\n", true ); ?></div></blockquote>
	<br /><br />
	<?php
}
if (count($this->product_reviews) < 1) echo JText::_('VM_NO_REVIEWS')." <br />"; // "There are no reviews for this product"
else {
	/* Show all reviews */
	if (!JRequest::getBool('showall', false) && count($this->product_reviews) >=5 ) {
		echo JHTML::link($this->more_reviews, JText::_('MORE_REVIEWS').'<br />');
	}
}

if (!empty($this->user->id)) {
	if (!$alreadycommented) {
		echo JText::_('VM_WRITE_FIRST_REVIEW'); // "Be the first to write a review!"
		?>
		<script language="javascript" type="text/javascript">
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
			else if (form.comment.value.length < <?php echo VmConfig::get('vm_reviews_minimum_comment_length', 100); ?>) {
				alert('<?php echo sprintf( JText::_('VM_REVIEW_ERR_COMMENT1',false), VmConfig::get('vm_reviews_minimum_comment_length', 100)); ?>');
			  return false;
			}
			else if (form.comment.value.length > <?php echo VmConfig::get('vm_reviews_maximum_comment_length', 2000); ?>) {
				alert('<?php echo sprintf( JText::_('VM_REVIEW_ERR_COMMENT2',false), VmConfig::get('vm_reviews_maximum_comment_length', 2000)); ?>');
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
		</script>
	
		<h4><?php echo JText::_('VM_WRITE_REVIEW')  ?></h4>
		<br /><?php echo JText::_('VM_REVIEW_RATE')  ?>
		<form method="post" action="<?php echo JRoute::_('index.php');  ?>" name="reviewForm" id="reviewform">
		<table cellpadding="5" summary="<?php echo JText::_('VM_REVIEW_RATE') ?>">
		  <tr>
		  	<?php $url = JURI::root().'components/com_virtuemart/shop_image/reviews/'; ?>
			<th id="five_stars">
			<label for="user_rating5"><?php echo JHTML::image($url.'5.gif', JText::_('5_STARS')); ?></label>
			</th>
			<th id="four_stars">
				<label for="user_rating4"><?php echo JHTML::image($url.'4.gif', JText::_('4_STARS')); ?></label>
			</th>
			<th id="three_stars">
				<label for="user_rating3"><?php echo JHTML::image($url.'3.gif', JText::_('3_STARS')); ?></label>
			</th>
			<th id="two_stars">
				<label for="user_rating2"><?php echo JHTML::image($url.'2.gif', JText::_('2_STARS')); ?></label>
			</th>
			<th id="one_star">
				<label for="user_rating1"><?php echo JHTML::image($url.'1.gif', JText::_('1_STARS')); ?></label>
			</th>
			<th id="null_stars">
				<label for="user_rating0"><?php echo JHTML::image($url.'0.gif', JText::_('0_STARS')); ?></label>
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
		<br /><br />
			<?php
				$review_comment = sprintf( JText::_('VM_REVIEW_COMMENT'), VmConfig::get('vm_reviews_minimum_comment_length', 100), VmConfig::get('vm_reviews_maximum_comment_length', 2000));
				echo $review_comment;  
			?><br />
		<textarea title="<?php echo $review_comment ?>" class="inputbox" id="comment" onblur="refresh_counter();" onfocus="refresh_counter();" onkeypress="refresh_counter();" name="comment" rows="10" cols="55"></textarea>
		<br />
		<input class="button" type="submit" onclick="return( check_reviewform());" name="submit_review" title="<?php echo JText::_('VM_REVIEW_SUBMIT')  ?>" value="<?php echo JText::_('VM_REVIEW_SUBMIT')  ?>" />
		
		<div align="right"><?php echo JText::_('VM_REVIEW_COUNT')  ?>
		<input type="text" value="0" size="4" class="inputbox" name="counter" maxlength="4" readonly="readonly" />
		</div>
		
		<input type="hidden" name="product_id" value="<?php echo JRequest::getInt('product_id'); ?>" />
		<input type="hidden" name="option" value="<?php echo JRequest::getVar('option'); ?>" />
		<input type="hidden" name="category_id" value="<?php echo JRequest::getInt('category_id'); ?>" />
		<input type="hidden" name="func" value="addReview" />
	</form>
	<?php
	}
	else {
		echo JText::_('VM_REVIEW_ALREADYDONE');
	}
}
else echo JText::_('VM_REVIEW_LOGIN'); // Login to write a review!
?>