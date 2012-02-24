<?php
/*
#####################################################################################################
#
#					Module pour la plateforme de paiement PayZen
#						Version : 1.2 (révision 33398)
#									########################
#					Développé pour VirtueMart
#						Version : 2.0.0
#						Compatibilité plateforme : V2
#									########################
#					Développé par Lyra Network
#						http://www.lyra-network.com/
#						20/02/2012
#						Contact : support@payzen.eu
#
#####################################################################################################
*/
// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Renders a url element
 */

class JElementPayzenUrl extends JElement
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'PayzenUrl';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$class = ( $node->attributes('class') ? 'class="'.$node->attributes('class').'"' : 'class="text_area"' );
		if ($node->attributes( 'default' ) == $value)
        {
        	$value = JURI::root(). $value;
        }

		if ($node->attributes( 'editable' ) == 'true')
        {
        	$size = ( $node->attributes('size') ? 'size="'.$node->attributes('size').'"' : '' );

			return '<input type="text" name="'.$control_name.'['.$name.']" id="'.$control_name.$name.'" value="'.$value.'" '.$class.' '.$size.' />';
        }
        else
        {
        	return '<label for="'.$name.'"'.$class.'>'.$value.'</label>';
        }
	}
}