<?php
/*
 * @package		Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @component Phoca Component
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 2 or later;
 */
defined('_JEXEC') or die;

if(!class_exists('VmView'))require(JPATH_VM_SITE.DS.'helpers'.DS.'vmview.php');

class VirtueMartViewPdf extends VmView
{

	function display($tpl = 'pdf')
	{

		$viewName = jRequest::getWord('view','productdetails');
		$class= 'VirtueMartView'.ucfirst($viewName);
		if(!class_exists($class)) require(JPATH_VM_SITE.DS.'views'.DS.$viewName.DS.'view.html.php');
		$view = new $class ;

		$view->display($tpl);
	}


}
