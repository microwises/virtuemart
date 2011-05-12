<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>

<!-- Currency Selector Module -->
<?php echo $text_before ?>
<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
	<br />
	<?php echo JHTML::_('select.genericlist', $currencies, 'currency_id', 'class="inputbox"', 'currency_id', 'currency_txt', $currency_id) ; ?>
    <input class="button" type="submit" name="submit" value="<?php echo JText::_('Change Currency') ?>" />
</form>
