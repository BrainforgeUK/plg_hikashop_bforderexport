<?php
/**
 * @package   Order export enhancement.
 * @version   0.0.1
 * @author    https://www.brainforge.co.uk
 * @copyright Copyright (C) 2020 Jonathan Brain. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Plugin\CMSPlugin;

class plgHikashopBFOrderExport extends CMSPlugin
{
	protected $paymentClass;
	protected $shippingClass;
	protected $invalid;

	public function __construct($subject, $config)
	{
		parent::__construct($subject, $config);
	}

	public function onHikashopAfterDisplayView(&$view)
	{
		if (!hikashop_isClient('administrator'))
		{
			return;
		}

		switch(get_class($view))
		{
			case 'OrderViewOrder':
				require_once __DIR__ . '/toolbar/buttonexport.php';
				break;
		}
	}

	public function onBeforeOrderExportQuery(&$filters, $paramBase)
	{
		foreach(array('search', 'order_created', 'order_id') as $filterType) {
			if (!empty($filters[$filterType])) {
				return;
			}
		}

		$filters = array('1 = 0');
	}

	public function onBeforeOrderExport(&$rows, $obj)
	{
		if (empty($rows))
		{
			return;
		}

		$fields = $this->params->get('fields');
		if (empty($fields))
		{
			$fields = array();
		}
		else
		{
			$fields = (array) $fields;
		}

		$this->paymentClass = hikashop_get('class.payment');
		$this->shippingClass = hikashop_get('class.shipping');
		$this->invalid = '*** INVALID ***';
		$this->payments = array();
		$this->shippings = array();

		$newRows = array();
		foreach($rows as $rowId=>$row)
		{
			$newRows[$rowId] = new stdClass();
			foreach($fields as $field)
			{
				$column = $field->column;
				if (isset($row->$column))
				{
					$newRows[$rowId]->$column = $row->$column;
					continue;
				}

				if (strpos($column, 'order_product_') === 0)
				{
					if (!isset($newRows[$rowId]->products))
					{
						$newRows[$rowId]->products = array();
					}
					foreach($row->products as $id=>$product)
					{
						if (!isset($newRows[$rowId]->products[$id]))
						{
							$newRows[$rowId]->products[$id] = new stdClass();
						}

						if (isset($product->$column))
						{
							$newRows[$rowId]->products[$id]->$column = $product->$column;
							continue;
						}

						$value = $this->getpaymentorshipping($product, 'order_product_', substr($column, 14));
						if ($value !== false)
						{
							$newRows[$rowId]->products[$id]->$column = $value;
							continue;
						}

						$newRows[$rowId]->products[$id]->$column = $this->invalid;
					}
					continue;
				}

				if (strpos($column, 'order_currency_') === 0)
				{
					$value = $this->getfrominfo($row, 'order_currency_', substr($column, 6));
					if ($value !== false)
					{
						$newRows[$rowId]->$column = $value;
						continue;
					}
				}

				$value = $this->getpaymentorshipping($row, 'order_', substr($column, 6));
				if ($value !== false)
				{
					$newRows[$rowId]->$column = $value;
					continue;
				}

				$newRows[$rowId]->$column = $this->invalid;
			}
		}

		$rows = $newRows;
	}

	protected function getpaymentorshipping($row, $prefix, $column)
	{
		switch($column)
		{
			case 'payment_name':
				return $this->getPayment($row, $prefix, $column);
			case 'shipping_name':
				return $this->getShipping($row, $prefix, $column);
			default:
				return false;
		}
	}

	protected function getPayment($row, $prefix, $column)
	{
		$ordercolumn = $prefix . 'payment_id';
		if($payment = $this->paymentClass->get($row->$ordercolumn))
		{
			if (isset($payment->$column))
			{
				return $payment->$column;
			}
			return $this->invalid;
		}

		$ordercolumn = $prefix . 'payment_method';
		return $row->$ordercolumn;
	}


	protected function getShipping($row, $prefix, $column)
	{
		$ordercolumn = $prefix . 'shipping_id';
		if($shipping = $this->shippingClass->get($row->$ordercolumn))
		{
			if (isset($shipping->$column))
			{
				return $shipping->$column;
			}
			return $this->invalid;
		}

		$ordercolumn = $prefix . 'shipping_method';
		return $row->$ordercolumn;
	}

	protected function getfrominfo($row, $prefix, $column)
	{
		$ordercolumn = $prefix . 'info';
		if (empty($row->$ordercolumn))
		{
			return '';
		}

		$info = unserialize($row->$ordercolumn);
		if (isset($info->$column))
		{
			return $info->$column;
		}
		return $this->invalid;
	}
}
