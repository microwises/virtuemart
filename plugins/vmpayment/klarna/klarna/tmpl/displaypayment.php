
<?php
	defined('_JEXEC') or die();
	?>
  <fieldset>
        <table cellspacing="0" cellpadding="2" border="0" width="100%">
            <tbody>
                <tr>
                    <td colspan="2">
                        <input id="<?php echo $this->vars->klarna_pm['id']?>"
                                    type="radio" name="virtuemart_paymentmethod_id"
                                    value="<?php echo  $this->vars->virtuemart_paymentmethod_id ?>" />
                        <label for="<?php echo $this->vars->klarna_pm['id']?>">
                                 <?php echo $this->vars->klarna_pm['module'] ?></label>
                       <br />
                    </td>
                </tr>
                <tr>
                    <td>
                       <?php echo  $this->vars->klarna_pm['fields']['0']['field'] ?>
                    <td>
                </tr>
            </tbody>
        </table>
     </fieldset>


