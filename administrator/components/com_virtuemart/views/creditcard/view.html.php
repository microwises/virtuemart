<?php
/**
*
* Credit Card View
*
* @package	VirtueMart
* @subpackage CreditCard
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

// Load the view framework
jimport( 'joomla.application.component.view');

/**
 * HTML View class for maintaining the list of Credit Cards
 *
 * @package	VirtueMart
 * @subpackage CreditCard
 * @author RickG
 */
class VirtuemartViewCreditcard extends JView {

	function display($tpl = null) {

		// Load the helper(s)
		$this->loadHelper('adminMenu');

		$model = $this->getModel();

        $creditcard =& $model->getCreditCard();

        $layoutName = JRequest::getVar('layout', 'default');
        $isNew = ($creditcard->creditcard_id < 1);

		if ($layoutName == 'edit') {
			if ($isNew) {
				JToolBarHelper::title(  JText::_('VM_CREDITCARD_LIST_ADD' ).': <small><small>[ New ]</small></small>', 'vm_credit_48');
				JToolBarHelper::divider();
				JToolBarHelper::save();
				JToolBarHelper::cancel();
			}
			else {
				JToolBarHelper::title( JText::_('VM_CREDITCARD_LIST_ADD' ).': <small><small>[ Edit ]</small></small>', 'vm_credit_48');
				JToolBarHelper::divider();
				JToolBarHelper::save();
				JToolBarHelper::cancel('cancel', 'Close');
			}
			$this->assignRef('creditcard',	$creditcard);
        }
        else {
			JToolBarHelper::title( JText::_( 'VM_CREDITCARD_LIST_LBL' ), 'vm_credit_48' );
			JToolBarHelper::deleteList('', 'remove', 'Delete');
			JToolBarHelper::editListX();
			JToolBarHelper::addNewX();

			$pagination = $model->getPagination();
			$this->assignRef('pagination',	$pagination);

			$creditcards = $model->getCreditCards();
			$this->assignRef('creditcards',	$creditcards);
		}

		parent::display($tpl);
	}

}
?>
