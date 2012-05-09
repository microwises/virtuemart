<?php  defined('_JEXEC') or die();
/**
*
* @version $Id: virtuemart.php 5967 2012-04-29 23:17:14Z electrocity $
* @package VirtueMart
* @subpackage Klarna
* @author Valérie Isaksen
* @copyright Copyright (C) 2009-11 by the authors of the VirtueMart Team listed at /administrator/com_virtuemart/copyright.php - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.net
*/
?>
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
        <span class="sectiontableheader klarna_info">
	   <?php echo $viewData['payment_name']; ?>
	</span>
        <span id="klarna_invno_wrapper">
            <span id="klarna_invno_text"><?php echo JText::sprintf('VMPAYMENT_KLARNA_INVOICE_NUMBER_TEXT'); ?></span>
            <span id="klarna_invno"><?php echo  $viewData['klarna_invoiceno']; ?></span>
        </span>

    </div>

    <div class="clear"></div>


