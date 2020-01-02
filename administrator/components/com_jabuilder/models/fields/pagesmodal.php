<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

class JFormFieldPagesmodal extends JFormFieldList
{
	protected $type = 'pagesmodal' ;
	
	protected function getInput()
	{
		$allowEdit  = ((string) $this->element['edit'] == 'true') ? true : false;
		$allowClear = ((string) $this->element['clear'] != 'false') ? true : false;

		// Load language
		JFactory::getLanguage()->load('com_jabuilder', JPATH_ADMINISTRATOR);

		// The active article id field.
		$value = (int) $this->value > 0 ? (int) $this->value : '';

		// Build the script.
		$script = array();

		// Select button script
		$script[] = '	function jSelectArticle_' . $this->id . '(id, title, catid, object) {';
		$script[] = '		document.getElementById("' . $this->id . '_id").value = id;';
		$script[] = '		document.getElementById("' . $this->id . '_name").value = title;';

		if ($allowEdit)
		{
			$script[] = '		if (id == "' . $value . '") {';
			$script[] = '			jQuery("#' . $this->id . '_edit").removeClass("hidden");';
			$script[] = '		} else {';
			$script[] = '			jQuery("#' . $this->id . '_edit").addClass("hidden");';
			$script[] = '		}';
		}

		if ($allowClear)
		{
			$script[] = '		jQuery("#' . $this->id . '_clear").removeClass("hidden");';
		}

		$script[] = '		jQuery("#articleSelect' . $this->id . 'Modal").modal("hide");';

		if ($this->required)
		{
			$script[] = '		document.formvalidator.validate(document.getElementById("' . $this->id . '_id"));';
			$script[] = '		document.formvalidator.validate(document.getElementById("' . $this->id . '_name"));';
		}

		$script[] = '	}';

		// Edit button script
		$script[] = '	function jEditArticle_' . $value . '(title) {';
		$script[] = '		document.getElementById("' . $this->id . '_name").value = title;';
		$script[] = '	}';

		// Clear button script
		static $scriptClear;

		if ($allowClear && !$scriptClear)
		{
			$scriptClear = true;

			$script[] = '	function jClearArticle(id) {';
			$script[] = '		document.getElementById(id + "_id").value = "";';
			$script[] = '		document.getElementById(id + "_name").value = "' .
				htmlspecialchars(JText::_('COM_JABUIDLER_SELECT_A_PAGE', true), ENT_COMPAT, 'UTF-8') . '";';
			$script[] = '		jQuery("#"+id + "_clear").addClass("hidden");';
			$script[] = '		if (document.getElementById(id + "_edit")) {';
			$script[] = '			jQuery("#"+id + "_edit").addClass("hidden");';
			$script[] = '		}';
			$script[] = '		return false;';
			$script[] = '	}';
		}

		// Add the script to the document head.
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

		// Setup variables for display.
		$html = array();

		$linkArticles = 'index.php?option=com_jabuilder&amp;view=pages&amp;layout=modal&amp;tmpl=component'
			. '&amp;function=jSelectArticle_' . $this->id;
		$input = JFactory::getApplication()->input;
		$Itemid = $input->get('id');
		$linkArticle  = JURI::root().'index.php?option=com_jabuilder&amp;view=page&amp;';

		if (isset($this->element['language']))
		{
			$linkArticles .= '&amp;forcedLanguage=' . $this->element['language'];
			$linkArticle  .= '&amp;forcedLanguage=' . $this->element['language'];
		}

		$urlSelect = $linkArticles . '&amp;' . JSession::getFormToken() . '=1';
		$urlEdit   = $linkArticle . '&amp;id=' . $value .'&amp;Itemid='.$Itemid . '&amp;preview=1';

		if ($value)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('title'))
				->from($db->quoteName('#__jabuilder_pages'))
				->where($db->quoteName('id') . ' = ' . (int) $value);
			$db->setQuery($query);

			try
			{
				$title = $db->loadResult();
			}
			catch (RuntimeException $e)
			{
				JError::raiseWarning(500, $e->getMessage());
			}
		}

		if (empty($title))
		{
			$title = JText::_('COM_JABUIDLER_SELECT_A_PAGE');
		}

		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		// The current article display field.
		$html[] = '<span class="input-append">';
		$html[] = '<input class="input-medium" id="' . $this->id . '_name" type="text" value="' . $title . '" disabled="disabled" size="35" />';

		// Select article button
		$html[] = '<a'
			. ' class="btn hasTooltip"'
			. ' data-toggle="modal"'
			. ' role="button"'
			. ' href="#articleSelect' . $this->id . 'Modal"'
			. ' title="' . JHtml::tooltipText(JText::_('COM_JABUILDER_CHANGE_PAGE')) . '">'
			. '<span class="icon-file"></span> ' . JText::_('JSELECT')
			. '</a>';

		// Edit article button
		if ($allowEdit)
		{
			$html[] = '<a'
				. ' class="btn hasTooltip' . ($value ? '' : ' hidden') . '"'
				. ' id="' . $this->id . '_edit"'
				. ' data-toggle="modal"'
				. ' role="button"'
				. ' href="#articleEdit' . $value . 'Modal"'
				. ' title="' . JHtml::tooltipText(JText::_('COM_JABUILDER_PREVIEW')) . '">'
				. '<span class="icon-edit"></span> ' . JText::_('COM_JABUILDER_PREVIEW')
				. '</a>';
		}

		// Clear article button
		if ($allowClear)
		{
			$html[] = '<button'
				. ' class="btn' . ($value ? '' : ' hidden') . '"'
				. ' id="' . $this->id . '_clear"'
				. ' onclick="return jClearArticle(\'' . $this->id . '\')">'
				. '<span class="icon-remove"></span>' . JText::_('JCLEAR')
				. '</button>';
		}

		$html[] = '</span>';

		// Select article modal
		$html[] = JHtml::_(
			'bootstrap.renderModal',
			'articleSelect' . $this->id . 'Modal',
			array(
				'title'       => JText::_('COM_JABUILDER_CHANGE_PAGE'),
				'url'         => $urlSelect,
				'height'      => '400px',
				'width'       => '800px',
				'bodyHeight'  => '70',
				'modalWidth'  => '80',
				'footer'      => '<button type="button" class="btn" data-dismiss="modal" aria-hidden="true">'
						. JText::_("JLIB_HTML_BEHAVIOR_CLOSE") . '</button>',
			)
		);

		// Edit article modal
		$html[] = JHtml::_(
			'bootstrap.renderModal',
			'articleEdit' . $value . 'Modal',
			array(
				'title'       => JText::_('COM_JABUILDER_PREVIEW'),
				'backdrop'    => 'static',
				'keyboard'    => false,
				'closeButton' => false,
				'url'         => $urlEdit,
				'height'      => '400px',
				'width'       => '800px',
				'bodyHeight'  => '70',
				'modalWidth'  => '80',
				'footer'      => '<button type="button" class="btn" data-dismiss="modal" aria-hidden="true"'
						. ' onclick="jQuery(\'#articleEdit' . $value . 'Modal iframe\').contents().find(\'#closeBtn\').click();">'
						. JText::_("JLIB_HTML_BEHAVIOR_CLOSE") . '</button>'
			)
		);

		// Note: class='required' for client side validation.
		$class = $this->required ? ' class="required modal-value"' : '';

		$html[] = '<input type="hidden" id="' . $this->id . '_id"' . $class . ' name="' . $this->name . '" value="' . $value . '" />';

		return implode("\n", $html);
	}
	
	protected function getLabel()
	{
		return str_replace($this->id, $this->id . '_id', parent::getLabel());
	}
	
}