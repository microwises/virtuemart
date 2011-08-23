<?php


defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');

/**
 * Supports a modal product picker.
 *
 *
 */
class JFormFieldProduct extends JFormField
{
	/**
	 * The form field type.
	 *
         * @author      Valerie Cartan Isaksen
	 * @var		string
	 *
	 */
	protected $type = 'product';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
 

            function getInput() {

        $key = ($this->element['key_field'] ? $this->element['key_field'] : 'value');
        $val = ($this->element['value_field'] ? $this->element['value_field'] : $this->name);
        $products=$this->_getProducts();
        return JHTML::_('select.genericlist', $products, $this->name, 'class="inputbox"   ', 'value', 'text', $this->value, $this->id);
    }
 private function _getProducts() {

        $db = JFactory::getDBO();
        $query = "SELECT `virtuemart_product_id`  AS value, `product_name`  AS text FROM `#__virtuemart_products` WHERE `published` = 1";
        $db->setQuery($query);
        $db->query();
        return $db->loadObjectList();
    }

}