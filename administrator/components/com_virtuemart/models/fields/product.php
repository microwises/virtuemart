<?php


defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');
if (!class_exists('VmConfig'))
    require(JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'config.php');
if (!class_exists('ShopFunctions'))
    require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'shopfunctions.php');
if (!class_exists('TableCategories'))
    require(JPATH_VM_ADMINISTRATOR . DS . 'tables' . DS . 'categories.php');

if (!class_exists('VmElements'))
    require(JPATH_VM_ADMINISTRATOR . DS . 'elements' . DS . 'vmelements.php');
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

        return JHTML::_('select.genericlist',  $this->_getProducts(), $this->name, 'class="inputbox"   ', 'value', 'text', $this->value, $this->id);
    }
  private function _getProducts() {

	if (!class_exists('VirtueMartModelProduct'))
	    require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'product.php');
	$productModel = new VirtueMartModelProduct();
	$products = $productModel->getProductListing(false, false, false, false, true);
	$i = 0;
	foreach ($products as $product) {
	    $list[$i]['value'] = $product->virtuemart_product_id;
	    $list[$i]['text'] = $product->product_name. " (". $product->product_sku.")";
	    $i++;
	}
	return $list;
    }

}