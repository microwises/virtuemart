<?php
defined('_JEXEC') or die();
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

<?php
$logo = '<img src="' . JURI::base() . VMKLARNAPLUGINWEBROOT . '/klarna/assets/images/logo' . $viewData['logo'] . '"/>';
?>


<div class="klarna_info">
    <span style="">
	<a href="http://www.klarna.com/"><?php echo $logo ?></a><br /><?php echo $viewData['description'] ?>
    </span>
</div>

<div class="clear"></div>


