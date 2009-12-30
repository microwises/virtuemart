<?php if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
mm_showMyFileName(__FILE__);
$tooltipArray = array('className'=>'stockImageTip', 'showDelay'=>'200', 
   'hideDelay'=>'300',
   'onShow'=>"function(tip) {tip.effect('opacity', 
      {duration: 200, wait: false}).start(0,1)}", 
   'onHide'=>"function(tip) {tip.effect('opacity', 
      {duration: 300, wait: false}).start(1,0)}");
JHTML::_('behavior.tooltip','.hasTip3',$tooltipArray);?>
<span class="hasTip3" title="<?php echo JText::_('VM_STOCK_LEVEL_DISPLAY_TITLE_TIP') ?>::<?php echo $stock_tip ?>">
<div class="<?php echo $stock_level ?>"><?php echo $stock_level_label ?></div></span>
