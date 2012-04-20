<?php  defined('_JEXEC') or die(); ?>
<center><p> -- OR --</p></center>
         <!-- KLARNA BOX -->
         <script type="text/javascript">
         <!--
             var placeHolderText = new Array;
             placeHolderText['mobile_no'] = '<?php echo $viewData['input']['placeholder'] ; ?>';
             placeHolderText['mobile_code'] = '<?php echo JText::_('VMPAYMENT_KLARNA_MOBILE_MOBILE_CODE'); ?>';

             var pId = '<?php echo $viewData['input']['pId'] ; ?>';

             $('head').append('<link type="text/css" rel="stylesheet" href="klarna/mobile/default/style.css" />');

         //-->
         </script>
         <script src="<?php echo $viewData['setup']['path_js'] ; ?>klarna_mobile.js" type="text/javascript"></script>
         <div class="klarnaMobile_box">
             <div class="klarnaMobile_boxLogo">
             </div>
             <div class="klarnaMobile_info">
                 <?php echo JText::_('VMPAYMENT_KLARNA_MOBILE_TOPINFO'); ?> <a href="#top"><?php echo JText::_('VMPAYMENT_KLARNA_MOBILE_AGREEMENT'); ?></a>
             </div>
             <div class="klarnaMobile_boxContent">
                 <h1>1. <?php echo JText::_('VMPAYMENT_KLARNA_MOBILE_MOBILEPHONENO'); ?></h1>
                 <div class="klarnaMobile_boxInputField">
                     <div class="klarnaMobile_boxInputField_left_inputPlaceholder">
                         <input type="text" value="<?php echo $viewData['input']['placeholder'] ; ?>" name="mobile_no" />
                     </div>
                     <div class="klarnaMobile_boxInputField_right" id="getCode">
                         <input type="button" value="Få kod" name="submit_mobile_no" />
                         <div class="klarnaMobile_boxInputField_right_send"></div>
                     </div>
                 </div>
                 <h1>2. <?php echo JText::_('VMPAYMENT_KLARNA_MOBILE_PINCODE'); ?></h1>
                 <div class="klarnaMobile_boxInputField">
                     <div class="klarnaMobile_boxInputField_left_inputPlaceholder">
                         <input type="text" value="XXXX" name="mobile_code" />
                     </div>
                     <div class="klarnaMobile_boxInputField_right" id="makePurchase">
                         <input type="button" value="Köp nu!" name="submit_mobile_code" />
                         <div class="klarnaMobile_boxInputField_right_buy"></div>
                     </div>
                 </div>
             </div>
             <div class="klarnaMobile_error" id="klarnaMobile_error">
             </div>
             <div class="klarnaMobile_error_Inner">
                 <h3><?php echo JText::_('VMPAYMENT_KLARNA_MOBILE_WHOOPS'); ?></h3>
                 <div class="klarnaMobile_errorText"  id="klarnaMobile_errorText">

                 </div>
                 <div class="klarnaMobile_errorClose">
                     <?php echo JText::_('VMPAYMENT_KLARNA_MOBILE_CLOSE'); ?>
                 </div>
             </div>
         </div>