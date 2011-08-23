<?php


defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');

/**
 * Supports a modal product picker.
 *
 *
 */
class JFormFieldManufacturer extends JFormField
{
	/**
	 * The form field type.
	 *
         * @author      Valerie Cartan Isaksen
	 * @var		string
	 *
	 */
	protected $type = 'manufacturer';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
 

            function getInput() {

        $key = ($this->element['key_field'] ? $this->element['key_field'] : 'value');
        $val = ($this->element['value_field'] ? $this->element['value_field'] : $this->name);
       
        return JHTML::_('select.genericlist', $this->_getManufacturers(), $this->name, 'class="inputbox"  size="1"', 'value', 'text', $this->value, $this->id);
    }
 private function _getManufacturers() {

        $db = JFactory::getDBO();
        $query = "SELECT `virtuemart_manufacturer_id`  AS value, `mf_name`  AS text FROM `#__virtuemart_manufacturers` WHERE `published` = 1";
        $db->setQuery($query);
        $db->query();
        return $db->loadObjectList();
    }

}