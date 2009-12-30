<?php
defined('_JEXEC') or die('Restricted access');  
?>
<br />
<fieldset class="adminform">
    <legend><?php echo JText::_('VM_ADMIN_CFG_CHECKOUT_SETTINGS') ?></legend>
    <table class="admintable">
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_ENABLE_CHECKOUTBAR_EXPLAIN'); ?>"/>
		<?php echo JText::_('VM_ADMIN_CFG_ENABLE_CHECKOUTBAR') ?>
	    </td>
	    <td>
		<?php
		$checked = '';
		if ($this->config->get('show_checkout_bar')) $checked = 'checked="checked"'; ?>
		<input type="checkbox" name="show_checkout_bar" value="1" <?php echo $checked; ?> />
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_MAX_VENDOR_PRO_CART_EXPLAIN'); ?>"/>
		<?php echo JText::_('VM_ADMIN_CFG_MAX_VENDOR_PRO_CART') ?>
	    </td>
	    <td>
		<input type="text" name="max_vendor_pro_cart" class="inputbox" value=<?php echo MAX_VENDOR_PRO_CART; ?> />
	    </td>
	</tr>
	<tr>
	    <td class="key" valign="top">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_CFG_CHECKOUT_SHOWSTEP_TIP'); ?>"/>
		<?php echo JText::_('VM_ADMIN_CFG_CHECKOUT_PROCESS') ?>
	    </td>
	    <td valign="top">
		<?php
		$checkout_modules = array('CHECK_OUT_GET_SHIPPING_ADDR'=>array('order'=>1,'enabled'=>1),
			'CHECK_OUT_GET_SHIPPING_METHOD'=>array('order'=>2,'enabled'=>1),
			'CHECK_OUT_GET_PAYMENT_METHOD'=>array('order'=>3,'enabled'=>1),
			'CHECK_OUT_GET_FINAL_CONFIRMATION'=>array('order'=>4,'enabled'=>1));
		$checkout_names = array_keys($checkout_modules);
		foreach ($checkout_modules as $step) {
		    $stepname = current($checkout_names);
		    $label = "VM_CHECKOUT_MSG_".$step['order'];
		    $label = constant($stepname);
		    echo $label;
		    echo $stepname;
		    $readonly = $checked = '';
		    if ($step['enabled'] > 0) {
			$checked = ' checked="checked"';
		    }
		    if ($stepname == 'CHECK_OUT_GET_PAYMENT_METHOD' || $stepname == 'CHECK_OUT_GET_FINAL_CONFIRMATION') {
			$readonly = 'disabled="disabled"';
			$checked = ' checked="checked"';
		    }
		    echo '<input type="checkbox" name="VM_CHECKOUT_MODULES['.$stepname.'][enabled]" id="VM_CHECKOUT_MODULES_'.$stepname.'" value="1" '.$readonly.$checked.'/>
            			<label for="VM_CHECKOUT_MODULES_'.$stepname.'"><strong>&quot;'.JText::_($label).'&quot;</strong></label><br />
            			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            			'.sprintf( JText::_('VM_CFG_CHECKOUT_SHOWSTEPINCHECKOUT'), '<input type="text" name="VM_CHECKOUT_MODULES['.$stepname.'][order]" value="'.$step['order'].'" class="inputbox" size="2" />' )
			    .'<input type="hidden" name="VM_CHECKOUT_MODULES['.$stepname.'][name]" value="'.$stepname.'" />
            			<br /><br />';
		    next($checkout_names);
		}
		?>
	    </td>
	</tr>
    </table>
</fieldset>