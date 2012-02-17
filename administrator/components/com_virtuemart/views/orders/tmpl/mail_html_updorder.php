<?php
/**
 * Renders the email when the order status has changed
 * @package	VirtueMart
 * @subpackage Order
 * @author Valérie Isaksen
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: view.html.php 2459 2010-07-02 17:30:23Z milbo $
 */
$li = '<br />';
?>


<html>
    <head>
	<style type="text/css">
            body, td, span, p, th { font-size: 11px; }
	    table.html-email {margin:10px auto;background:#fff;border:solid #dad8d8 1px;}
	    .html-email tr{border-bottom : 1px solid #eee;}
	    span.grey {color:#666;}
	    span.date {color:#666;font-size: 10px;}
	    a.default:link, a.default:hover, a.default:visited {color:#666;line-height:25px;background: #f2f2f2;margin: 10px ;padding: 3px 8px 1px 8px;border: solid #CAC9C9 1px;border-radius: 4px;-webkit-border-radius: 4px;-moz-border-radius: 4px;text-shadow: 1px 1px 1px #f2f2f2;font-size: 12px;background-position: 0px 0px;display: inline-block;text-decoration: none;}
	    a.default:hover {color:#888;background: #f8f8f8;}
	    .cart-summary{ }
	    .html-email th { background: #ccc;margin: 0px;padding: 10px;}
	    .sectiontableentry2, .html-email th, .cart-summary th{ background: #ccc;margin: 0px;padding: 10px;}
	    .sectiontableentry1, .html-email td, .cart-summary td {background: #fff;margin: 0px;padding: 10px;}
	</style>

    </head>

    <body style="background: #F2F2F2;word-wrap: break-word;">
	<div style="background-color: #e6e6e6;" width="100%">
	    <table style="margin: auto;" cellpadding="0" cellspacing="0" width="600" >
		<tr>
		    <td>
			<table width="100%" border="0" cellpadding="0" cellspacing="0" class="html-email">
			    <tr>
				<td >

				    <?php echo JText::_('COM_VIRTUEMART_HI') . ' ' . $this->user->full_name; ?>
				    <br />
				</td>
			    </tr>
			</table>

			<table class="html-email" cellspacing="0" cellpadding="0" border="0" width="100%">  <tr >
				<th width="100%">
				    <?php echo JText::sprintf('COM_VIRTUEMART_ORDER_STATUS_CHANGE_SEND_MSG_1', $this->orderdata['details']['BT']->order_number); ?>
				</th>
			    </tr>
			    <tr>
				<td valign="top" width="100%">
				    <?php
				    if ($this->order->_customer_notified) {
					echo JText::_('COM_VIRTUEMART_ORDER_HISTORY_COMMENT_EMAIL')  . '<br/>';
					echo  $this->order->_comments . '<br/>';
					echo '<br/>';
				    }

				    echo JText::_('COM_VIRTUEMART_ORDER_STATUS_CHANGE_SEND_MSG_2')
				    ?>
				    <?php echo $this->user->order_status_name ?><br/><br/>
				    <?php if (VmConfig::get('vm_registration_type') != 'NO_REGISTRATION') { ?>
    				    <br/>

    				    <a class="default" title="<?php echo $this->vendor->vendor_store_name ?>" href="<?php echo JURI::root() . 'index.php?option=com_virtuemart&view=orders&layout=details&order_number=' . $this->orderdata['details']['BT']->order_number . '&order_pass=' . $this->orderdata['details']['BT']->order_pass; ?>">
					    <?php echo JText::_('COM_VIRTUEMART_ORDER_MAIL_SHOPPER_YOUR_ORDER_LINK'); ?></a>
    				 <br/><br/>
				    <?php } ?>

				</td>
			    </tr>
			</table>
		    </td>
		</tr>
	    </table>
	</div>
    </body>
</html>
</head>

