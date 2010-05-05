<?php
defined('_JEXEC') or die('Restricted access');
?>

<link rel="stylesheet" href="components/com_virtuemart/assets/css/install.css" type="text/css" /> 

<div align="center">
	<table width="100%" border="0">
	<tr>
		<td valign="top" align="center">
			<a href="http://virtuemart.net" target="_blank">
				<img border="0" align="center" src="components/com_virtuemart/assets/images/cart.gif" alt="Cart" />
			</a>
			<br /><br />
			<h1>Welcome to<br />Virtuemart!</h1>
		</td>
		<td>	
			<h1>The first step of the Installation was <font color="green">SUCCESSFUL</font></h1>
			<br /><br />
			
			<table width="50%">				
			<tr>
				<td align="center" colspan="2" >Basic Installation has been finished.</td>
			</tr>
			<tr>
				<td width="50%" align="center">
					<a onclick="alert('Please don\'t interrupt the next Step! \n It is essential for running VirtueMart.');" href="<?php echo JROUTE::_('index.php?option=com_virtuemart&view=updatesmigration&task=installSampleData'); ?>">
						<img src="components/com_virtuemart/assets/images/icon_48/vm_install_48.png">
					</a>
					<br />
					Install Sample Data
				</td>				
				<td width="50%" align="center">
					<a href="<?php echo JROUTE::_('index.php?option=com_virtuemart'); ?>">
						<img src="components/com_virtuemart/assets/images/icon_48/vm_frontpage_48.png">
					</a>
					<br />
					Go to the Shop
				</td>
			</tr>
			<tr>
				<td align="center" colspan="2"><br /><br /><hr /><br /></td>
			</tr>
			<tr>
				<td align="center">
					Go to <a href="http://virtuemart.net" target="_blank">VirtueMart</a> for further Help
				</td>
				<td align="center">
					Please consider a small donation to help us keep up the work on this component.<br /><br />
					<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
						<input type="hidden" name="cmd" value="_xclick" />
						<input type="hidden" name="business" value="" />
						<input type="hidden" name="item_name" value="VirtueMart Donation" />
						<input type="hidden" name="item_number" value="" />
						<input type="hidden" name="currency_code" value="EUR" />
						<input type="hidden" name="tax" value="0" />
						<input type="hidden" name="no_note" value="0" />
						<input type="hidden" name="amount" value="" />
						<input type="image" src="components/com_virtuemart/assets/images/donate.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!" />
					</form>
				</td>
			</tr>
			</table>
		</td>
	</tr>
	</table>
</div>