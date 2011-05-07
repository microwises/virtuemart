<?php
echo JText::sprintf('COM_VIRTUEMART_RECOMMEND_MAIL_MSG', $this->product->product_name, $this->comment);

$uri    = JURI::getInstance();
$prefix = $uri->toString(array('scheme', 'host', 'port'));
$link = JRoute::_ ( 'index.php?option=com_virtuemart&view=productdetails&product_id='.$this->product->product_id );

echo '<br /><b>'.JHTML::_('link',$prefix.$link, $this->product->product_name).'</b>';

// TODO add a footer and SHOP HEADER ?
