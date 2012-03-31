<?php  defined('_JEXEC') or die(); ?>
<style type="text/css">
#klarna_invno {
    float:left; font-weight: bold; font-size: 13px;
}
#klarna_invno_text {
    width: 50%; float: left;
}
.clear {
    clear: both;
}
.klarna_info {
    float: left; left: -2px; position: relative; text-align: left; width: 99.8%;
}
.klarna_tulip {
    float: left; padding-right: 10px;
}
</style>
    <div class="klarna_info">
        <span class="sectiontableheader klarna_info"><img class="klarna_tulip" src="<?php $params['klarna_logo'] ?>" /> <?php echo JText::_('VMPAYMENT_KLARNA_PAYMENT_INFO') ?></span>
        <span id="klarna_invno_wrapper">
            <span id="klarna_invno_text"><?php echo $params['invoice_number_text']; ?></span>
            <span id="klarna_invno"><?php echo $params['invoice_number']; ?></span>
        </span>
        <span style="float: right;">
            <a href="http://www.klarna.com/"><?php echo $params['klarna_payment_logo'];?></a><br>
            <?php echo $params['method'];?>
        </span>
    </div>

    <div class="clear"></div>


