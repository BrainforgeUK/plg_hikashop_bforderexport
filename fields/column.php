<?php
/**
 * @package   Handles customer order delivery slot selection.
 * @version   0.0.1
 * @author    https://www.brainforge.co.uk
 * @copyright Copyright (C) 2020 Jonathan Brain. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;

FormHelper::loadFieldClass('groupedlist');

class BrainforgeFormFieldColumn extends JFormFieldGroupedList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.9.0
	 */
	protected $type = 'Column';

	/**
	 * Method to get the field option groups.
	 *
	 * @return  array  The field option objects as a nested array in groups.
	 *
	 * @since   1.7.0
	 * @throws  UnexpectedValueException
	 */
	protected function getGroups()
	{
		$groups = array();

		$groups[''] = array();
		$groups[''][] = JHtml::_('select.option', $this->value, $this->value, 'value', 'text');

		$this->addGroup($groups, 'User', 		$this->getUserFields());
		$this->addGroup($groups, 'Order', 		$this->getOrderFields());
		$this->addGroup($groups, 'Shipping', 	$this->getAddressFields('shipping'));
		$this->addGroup($groups, 'Billing',	$this->getAddressFields('billing'));
		$this->addGroup($groups, 'Item',		$this->getItemFields());

		reset($groups);

		return $groups;
	}

	/**
	 * Method to get the field input markup fora grouped list.
	 * Multiselect is enabled by using the multiple attribute.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.7.0
	 */
	protected function getInput()
	{
		$html = array();

		// Initialize some field attributes.
		$attr = 'class="chzn-custom-value' . (empty($this->class) ? '' : ' ' . $this->class) . '" '
				. 'data-custom_group_text="' . 'Test1' . '" '
				. 'data-no_results_text="' . Text::_('PLG_HIKASHOP_BFORDEREXPORT_COLUMN_CUSTOM') . '" '
				. 'data-placeholder="' . Text::_('PLG_HIKASHOP_BFORDEREXPORT_COLUMN_TYPEORSELECT') . '" ';
		$attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$attr .= $this->multiple ? ' multiple' : '';
		$attr .= $this->required ? ' required aria-required="true"' : '';
		$attr .= $this->autofocus ? ' autofocus' : '';

		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ($this->readonly || $this->disabled)
		{
			$attr .= ' disabled="disabled"';
		}

		// Initialize JavaScript field attributes.
		$attr .= !empty($this->onchange) ? ' onchange="' . $this->onchange . '"' : '';

		// Get the field groups.
		$groups = (array) $this->getGroups();

		// Create a read-only list (no name) with a hidden input to store the value.
		if ($this->readonly)
		{
			$html[] = JHtml::_(
				'select.groupedlist', $groups, null,
				array(
					'list.attr' => $attr, 'id' => $this->id, 'list.select' => $this->value, 'group.items' => null, 'option.key.toHtml' => false,
					'option.text.toHtml' => false,
				)
			);

			// E.g. form field type tag sends $this->value as array
			if ($this->multiple && is_array($this->value))
			{
				if (!count($this->value))
				{
					$this->value[] = '';
				}

				foreach ($this->value as $value)
				{
					$html[] = '<input type="hidden" name="' . $this->name . '" value="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"/>';
				}
			}
			else
			{
				$html[] = '<input type="hidden" name="' . $this->name . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"/>';
			}
		}

		// Create a regular list.
		else
		{
			$html[] = JHtml::_(
				'select.groupedlist', $groups, $this->name,
				array(
					'list.attr' => $attr, 'id' => $this->id, 'list.select' => $this->value, 'group.items' => null, 'option.key.toHtml' => false,
					'option.text.toHtml' => false,
				)
			);
		}

		return implode($html);
	}

	protected function addGroup(&$groups, $label, $columns)
	{
		$groups[$label] = array();

		foreach($columns as $column)
		{
			$groups[$label][] = JHtml::_('select.option', $column, $column, 'value', 'text');
		}
	}

	protected function getCustomFields($table, $address_type=null)
	{
		$query = "SELECT a.field_namekey FROM #__hikashop_field AS a " .
			"WHERE a.field_table = '" . $table . "' " .
			"AND a.field_type NOT IN ( 'customtext', 'ajaxfile', 'ajaximage' ) " .
			(empty($address_type) ? '' :
				"AND ( " .
					"    field_address_type = '" . $address_type . "'" .
					" OR field_address_type = ''" .
					" OR field_address_type IS NULL" .
				   " ) ") .
			"ORDER BY a.field_ordering ASC, a.field_namekey ASC";

		$database = Factory::getDBO();
		$database->setQuery($query);
		return $database->loadColumn();
	}

	protected function getUserFields() {
		$fields = array(
			'user_id',
			'user_cms_id',
			'user_email',
			'user_partner_email',
			'user_params',
			'user_partner_id',
			'user_partner_price',
			'user_partner_paid',
			'user_created_ip',
			'user_unpaid_amount',
			'user_partner_currency_id',
			'user_created',
			'user_currency_id',
			'user_partner_activated',
		);

		return $fields;
	}

	protected function getOrderFields()
	{
		$fields = array(
			'order_id',
			'order_billing_address_id',
			'order_shipping_address_id',
			'order_user_id',
			'order_parent_id',
			'order_status',
			'order_type',
			'order_number',
			'order_created',
			'order_modified',
			'order_invoice_id',
			'order_invoice_number',
			'order_invoice_created',
			'order_currency_id',
			'order_currency_info',
			'order_currency_code',
			'order_currency_rate',
			'order_currency_percent_fee',
			'order_currency_modified',
			'order_full_tax',
			'order_full_price',
			'order_tax_info',
			'order_discount_code',
			'order_discount_price',
			'order_discount_tax',
			'order_payment_id',
			'order_payment_name',
			'order_payment_method',
			'order_payment_price',
			'order_payment_tax',
			'order_payment_params',
			'order_shipping_id',
			'order_shipping_name',
			'order_shipping_method',
			'order_shipping_price',
			'order_shipping_tax',
			'order_shipping_params',
			'order_partner_id',
			'order_partner_price',
			'order_partner_paid',
			'order_partner_currency_id',
			'order_ip',
			'order_site_id',
			'order_lang',
			'order_token',
		);

		$customFields = $this->getCustomFields('order');
		if (empty($customFields))
		{
			return $fields;
		}
		return array_merge($fields, $customFields);
	}

	protected function getAddressFields($type) {
		$fields = array(
			$type . '_address_id',
			$type . '_address_user_id',
			$type . '_address_published',
			$type . '_address_default',
		);

		$customFields = $this->getCustomFields('address', $type);
		if (empty($customFields))
		{
			return $fields;
		}

		foreach($customFields as $customField)
		{
			$fields[] = $type . '_' . $customField;
		}
		return $fields;
	}

	protected function getItemFields() {
		$fields = array(
			'order_product_id',
			'order_id',
			'product_id',
			'order_product_quantity',
			'order_product_name',
			'order_product_code',
			'order_product_price',
			'order_product_tax',
			'order_product_tax_info',
			'order_product_options',
			'order_product_status',
			'order_product_shipping_id',
			'order_product_shipping_method',
			'order_product_shipping_price',
			'order_product_shipping_tax',
			'order_product_shipping_params',
			'order_product_params',
			'order_product_id',
		);

		$customFields = $this->getCustomFields('product');
		if (!empty($customFields))
		{
			foreach($customFields as $customField)
			{
				$fields[] = 'order_' . $customField;
			}
		}

		$customFields = $this->getCustomFields('item');
		if (empty($customFields))
		{
			return $fields;
		}
		return array_merge($fields, $customFields);
	}
}
