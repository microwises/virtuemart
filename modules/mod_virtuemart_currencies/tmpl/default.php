<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>

<!-- Currency Selector Module -->
<?php 
echo $text_before ;
$url =& JFactory::getURI();
?>
<form action="<?php echo $url->toString(); ?>" method="post">
	<br />
	<?php
	 echo JHTML::_('select.genericlist', $currencies, 'currency_id', 'class="inputbox" onchange="this.form.submit();" ', 'currency_id', 'currency_txt', $currency_id) ;
	 ?>
    <input class="button" type="submit" name="formSubmit" value="<?php echo JText::_('Change Currency') ?>" />
</form>
