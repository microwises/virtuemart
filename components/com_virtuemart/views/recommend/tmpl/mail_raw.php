<?php
defined('_JEXEC') or die('');
echo JText::sprintf('COM_VIRTUEMART_RECOMMEND_MAIL_MSG', $this->product->product_name, $this->comment);

$uri    = JURI::getInstance();
$prefix = $uri->toString(array('scheme', 'host', 'port'));
$link = JRoute::_ ( 'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$this->product->virtuemart_product_id );

echo '<br /><b>'.JHTML::_('link',$prefix.$link, $this->product->product_name).'</b>';
include(JPATH_VM_SITE.DS.'views'.DS.'cart'.DS.'tmpl'.DS.'mail_html_footer.php');
