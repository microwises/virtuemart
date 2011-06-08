<?php
/**
 *
 * Description
 *
 * @package	VirtueMart
 * @subpackage ShippingCarrier
 * @author RickG
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
defined('_JEXEC') or die('Restricted access');
AdminMenuHelper::startAdminArea();
JHTML::_('behavior.tooltip');
?>
<form action="index.php" method="post" name="adminForm">
<?php
$pane = JPane::getInstance('tabs', array('startOffset' => 0));
echo $pane->startPane('pane');

echo $pane->startPanel(JText::_('COM_VIRTUEMART_ADMIN_SHIPPER_FORM'), 'shipper_edit');
echo $this->loadTemplate('edit');
echo $pane->endPanel();
echo $pane->startPanel(JText::_('COM_VIRTUEMART_ADMIN_SHIPPER_CONFIGURATION'), 'shipper_config');
echo $this->loadTemplate('config');
echo $pane->endPanel();

echo $pane->endPane();
?>

    <!-- Hidden Fields -->

<input type="hidden" name="option" value="com_virtuemart" />
<input type="hidden" name="virtuemart_shippingcarrier_id" value="<?php echo $this->carrier->virtuemart_shippingcarrier_id; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="xxcontroller" value="shippingcarrier" />
<input type="hidden" name="view" value="shippingcarrier" />

<?php echo JHTML::_('form.token'); ?>
</form>
    <?php AdminMenuHelper::endAdminArea(); ?>
 