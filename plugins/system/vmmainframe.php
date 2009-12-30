<?php
/**
* @version		$Id: debug.php 10457 2008-06-27 05:52:12Z eddieajau $
* @package		VirtueMart
* @copyright	Copyright (C) 2008 soeren - All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

/**
 * VirtueMart CSS&JS Render plugin
 *
 * @author		soeren
 * @package		Joomla
 * @subpackage	System
 */
class  plgSystemVmMainframe extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @access	protected
	 * @param	object $subject The object to observe
	 * @param 	array  $config  An array that holds the plugin configuration
	 * @since	1.0
	 */
	function plgSystemVmMainframe(& $subject, $config)
	{
		parent::__construct($subject, $config);

	}

	/**
	* Renders the VirtueMart Mainframe if it's a non-virtuemart html request
	*
	*/
	function onAfterRender()
	{
		global $vm_mainframe;


		$document	=& JFactory::getDocument();
		$doctype	= $document->getType();
		$option = JRequest::getCmd('option');

		// Only render for HTML output
		if ( $doctype !== 'html' ) { return; }
		if ( $option == 'com_virtuemart' ) { return; }
		
		if( is_object($vm_mainframe)) {
			ob_start();
			$vm_mainframe->render(true);
			$head_content = ob_get_clean();
			$body = JResponse::getBody();
			$body = str_replace('</head>', $head_content.'</head>', $body);
			JResponse::setBody($body);
		}
	}
}
?>