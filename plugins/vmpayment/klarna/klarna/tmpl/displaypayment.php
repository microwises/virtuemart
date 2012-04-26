<?php  defined('_JEXEC') or die(); ?>
  <fieldset>
        <table cellspacing="0" cellpadding="2" border="0" width="100%">
            <tbody>
                <tr>
                    <td colspan="2">
                        <input class="klarnaPayment" data-stype="<?php echo $viewData['stype'] ?>" id="<?php echo $viewData['klarna_pm']['id'] ?>" type="radio" name="virtuemart_paymentmethod_id" value="<?php echo  $viewData['virtuemart_paymentmethod_id'] ?>" />
			<input  value="<?php echo $viewData['klarna_pm']['id'] ?>" type="hidden" name="klarna_paymentmethod" />
<label for="<?php echo $viewData['klarna_pm']['id']?>">
                                 <?php echo $viewData['klarna_pm']['module'] ?></label>
                       <br />
                    </td>
                </tr>
                <tr>
                    <td>
                       <?php echo  $viewData['klarna_pm']['fields']['0']['field'] ?>
                    <td>
                </tr>
            </tbody>
        </table>
     </fieldset>
<?php

