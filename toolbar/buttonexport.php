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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class JToolbarButtonExport extends JButton
{
	protected $name = 'Export';

	public function fetchButton($type = 'Export', $namekey = '', $id = 'export') {
		Factory::getLanguage()->load('plg_hikashop_bforderexport', dirname(__DIR__));

		$message = Text::_('PLG_HIKASHOP_BFORDEREXPORT_NO_CRITERIA');
		$message = addslashes($message);

		$js = "
function hikaExport(){
	if (document.adminForm.boxchecked.value == 0 &&
	    document.adminForm.search.value == '' &&
	    document.adminForm.period_start.value == '' &&
	    document.adminForm.period_end.value == ''
	    ) {
		alert('" . $message . "');
	}
	else {
		submitbutton('export');
	}
	var form = document.getElementById('adminForm');
	form.task.value = '';
	return false;
}";
		if(HIKASHOP_J40)
			$btnClass = 'btn btn-info';
		else
			$btnClass = 'btn btn-small';

		Factory::getDocument()->addScriptDeclaration($js);
		if(!HIKASHOP_J30)
		{
			return '<a href="#" target="_blank" onclick="return hikaExport1();" class="toolbar"><span class="icon-32-archive" title="' . JText::_('HIKA_EXPORT', true) . '"></span>' . JText::_('HIKA_EXPORT') . '</a>';
		}
		return '<button class="'.$btnClass.'" onclick="return hikaExport();"><i class="icon-upload"></i> '.JText::_('HIKA_EXPORT').'</button>';
	}

	public function fetchId($type = 'Export', $html = '', $id = 'export') {
		return $this->name . '-' . $id;
	}
}
