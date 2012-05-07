<?php defined('_JEXEC') or die('Restricted access');
JHTML::stylesheet('style.css', VMKLARNAPLUGINWEBROOT . '/klarna/assets/css/', false);
JHTML::script('klarna_pp.js', VMKLARNAPLUGINWEBASSETS.'/js/', false);
JHTML::script('klarnapart.js', 'https://static.klarna.com:444/external/js/', false);
$document = JFactory::getDocument();
$document->addScriptDeclaration("

jQuery(function(){
	jQuery('.klarna_PPBox_bottomMid_readMore a').click( function(){
		InitKlarnaPartPaymentElements('klarna_partpayment', '". $viewData['eid'] ."', '". $viewData['country'] ."');
		ShowKlarnaPartPaymentPopup();
		return false;
	});
});
");
?>
<?php
if ($viewData['country']== "nl") {
	$country_width="klarna_PPBox_topMid_nl";
} else {
	$country_width="";
}
?>
</script>
<div class="klarna_PPBox">
    <div id="klarna_partpayment" style="display: none"></div>
    <div class="klarna_PPBox_inner">
        <div class="klarna_PPBox_top">
            <span class="klarna_PPBox_topRight"></span>
            <span class="klarna_PPBox_topMid  <?php echo $country_width ?>">
                <p><?php echo JText::_('VMPAYMENT_KLARNA_PPBOX_FROMTEXT'); ?><label> <?php echo $viewData['defaultMonth'] ?> </label><?php echo JText::_('VMPAYMENT_KLARNA_PPBOX_MONTHTEXT'); ?><?php echo $this->settings['asterisk'] ?></p>
            </span>
            <span class="klarna_PPBox_topLeft"></span>
        </div>
        <div class="klarna_PPBox_bottom">
            <div class="klarna_PPBox_bottomMid">
                <table cellpadding="0" cellspacing="0" width="100%" border="0">
                    <thead>
                        <tr>
                            <th style="text-align: left"><?php echo JText::_('VMPAYMENT_KLARNA_PPBOX_TH_MONTH'); ?></th>
                            <th style="text-align: right"><?php echo JText::_('VMPAYMENT_KLARNA_PPBOX_TH_SUM'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php echo $viewData['monthTable'] ?>
                    </tbody>
                </table>
                <div class="klarna_PPBox_bottomMid_readMore">
                    <a href="#"><?php echo JText::_('VMPAYMENT_KLARNA_PPBOX_READMORE'); ?></a>
                </div>
                <div class="klarna_PPBox_pull" id="klarna_PPBox_pullUp">
                    <img src="<?php echo VMKLARNAPLUGINWEBASSETS ?>/images/productPrice/default/pullUp.png" alt="More info" />
                </div>
            </div>
        </div>
        <div class="klarna_PPBox_pull" id="klarna_PPBox_pullDown">
            <img src="<?php echo VMKLARNAPLUGINWEBASSETS ?>/images/productPrice/default/pullDown.png" alt="More info" />
        </div>
        <?php
	$notice = (($viewData['country']  == 'nl') ? '<div class="nlBanner"><img src="' . VMKLARNAPLUGINWEBASSETS . '/images/account/notice_nl.jpg" /></div>' : "");
	echo $notice;
	 ?>
    </div>
</div>
<div style="clear: both; height: 80px;"></div>
