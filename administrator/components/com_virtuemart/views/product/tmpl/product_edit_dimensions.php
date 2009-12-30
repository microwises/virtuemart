<?php defined('_JEXEC') or die('Restricted access');?>
   <table class="adminform">
   <tbody>
    <tr class="row1">
      <td width="21%" valign="top" >
        <div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_PRODUCT_FORM_LENGTH') ?>:</div>
      </td>
      <td width="79%" >
        <input type="text" class="inputbox"  name="product_length" value="<?php echo $this->product->product_length; ?>" size="15" maxlength="15" />
      </td>
    </tr>
    <tr class="row0">
      <td width="21%" valign="top" >
        <div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_PRODUCT_FORM_WIDTH') ?>:</div>
      </td>
      <td width="79%" >
        <input type="text" class="inputbox"  name="product_width" value="<?php echo $this->product->product_width; ?>" size="15" maxlength="15" />
      </td>
    </tr>
    <tr class="row1">
      <td width="21%" valign="top" >
        <div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_PRODUCT_FORM_HEIGHT') ?>:</div>
      </td>
      <td width="79%" >
        <input type="text" class="inputbox"  name="product_height" value="<?php echo $this->product->product_height; ?>" size="15" maxlength="15" />
      </td>
    </tr>
    <tr class="row0">
      <td width="21%" valign="top" >
        <div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_PRODUCT_FORM_DIMENSION_UOM') ?>:</div>
      </td>
      <td width="79%" >
        <input type="text" class="inputbox"  name="product_lwh_uom" value="<?php echo $this->product->product_lwh_uom; ?>" size="8" maxlength="32" />
      </td>
    </tr>
    <tr class="row1">
      <td width="21%" valign="top" >&nbsp;</td>
      <td width="79%" >&nbsp;</td>
    </tr>
    <tr class="row0">
      <td width="21%" valign="top" >
        <div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_PRODUCT_FORM_WEIGHT') ?>:</div>
      </td>
      <td width="79%" >
        <input type="text" class="inputbox"  name="product_weight" size="15" maxlength="15" value="<?php echo $this->product->product_weight; ?>" />
      </td>
    </tr>
    <tr class="row1">
      <td width="21%" valign="top" >
        <div style="text-align:right;font-weight:bold;"><?php echo JText::_('VM_PRODUCT_FORM_WEIGHT_UOM') ?>:</div>
      </td>
      <td width="79%" >
        <input type="text" class="inputbox"  name="product_weight_uom" value="<?php echo $this->product->product_weight_uom; ?>" size="8" maxlength="32" />
      </td>
    </tr>
    <!-- Changed Packaging - Begin -->
    <tr class="row0">
      <td width="21%" valign="top" >&nbsp;</td>
      <td width="21%" >&nbsp;</td>
    </tr>
    <tr class="row1">
      <td width="21%" valign="top" >
        <div align="right"><strong><?php echo JText::_('VM_PRODUCT_FORM_UNIT') ?>:</strong></div>
      </td>
      <td width="21%" >
        <input type="text" class="inputbox"  name="product_unit" size="15" maxlength="15" value="<?php echo $this->product->product_unit; ?>" />
      </td>
    </tr>
    <tr class="row0">
      <td width="21%" valign="top" >
        <div align="right"><strong><?php echo JText::_('VM_PRODUCT_FORM_PACKAGING') ?>:</strong></div>
      </td>
      <td width="21%" >
        <input type="text" class="inputbox"  name="product_packaging" value="<?php echo $this->product->product_packaging & 0xFFFF; ?>" size="8" maxlength="32" />&nbsp;<?php
        //echo vmToolTip(JText::_('VM_PRODUCT_FORM_PACKAGING_DESCRIPTION')); 
        ?>
      </td>
    </tr>
    <tr class="row1">
      <td width="21%" valign="top" >
        <div align="right"><strong><?php echo JText::_('VM_PRODUCT_FORM_BOX') ?>:</strong></div>
      </td>
      <td width="21%" >
        <input type="text" class="inputbox"  name="product_box" value="<?php echo ($this->product->product_packaging>>16)&0xFFFF; ?>" size="8" maxlength="32" />&nbsp;<?php
        // echo vmToolTip(JText::_('VM_PRODUCT_FORM_BOX_DESCRIPTION')); 
        ?>
      </td>
    </tr>
    <!-- Changed Packaging - End -->
</tbody>
</table>
