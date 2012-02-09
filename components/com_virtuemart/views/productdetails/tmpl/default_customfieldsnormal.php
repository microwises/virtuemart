<?php
/**
 *
 * Show the product details page
 *
 * @package	VirtueMart
 * @subpackage
 * @author Max Milbers, Valerie Isaksen

 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id$
 */

// Check to ensure this file is included in Joomla!
defined ( '_JEXEC' ) or die ( 'Restricted access' );
?>
<div class="product-fields">
	    <?php
	    $custom_title = null;
	    foreach ($this->product->customfieldsSorted['normal'] as $field) {
		if ($field->display) {
		    ?><div class="product-field product-field-type-<?php echo $field->field_type ?>">
		    <?php if ($field->custom_title != $custom_title) { ?>
			    <span class="product-fields-title" ><?php echo JText::_($field->custom_title); ?></span>
			    <?php
			    if ($field->custom_tip)
				echo JHTML::tooltip($field->custom_tip, JText::_($field->custom_title), 'tooltip.png');
			}
			?>
	    	    <span class="product-field-display"><?php echo $field->display ?></span>
	    	    <span class="product-field-desc"><?php echo jText::_($field->custom_field_desc) ?></span>
	    	</div>
		    <?php
		    $custom_title = $field->custom_title;
		}
	    }
	    ?>
        </div>
