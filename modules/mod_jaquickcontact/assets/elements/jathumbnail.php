<?php
/**
 * ------------------------------------------------------------------------
 * JA Quick Contact Module for J3.x
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.form.formfield');

class JFormFieldJathumbnail extends JFormField {
	protected $type = "Jathumbnail";
	
	protected function getInput() {
		$this->addAssets();
		$thumbPath = JPATH_SITE.'/modules/mod_jaquickcontact/assets/elements/jathumbnail/thumbs/';
		$imgPath = JURI::root().'modules/mod_jaquickcontact/assets/elements/jathumbnail/thumbs/';
		$options = array();
		//echo $thumbPath;die();
		if (JFolder::exists($thumbPath)) {
			$thumbs = JFolder::files($thumbPath);
			if (count($thumbs)) {
				foreach ($thumbs as $key => $thumb) {
					$img = '<img class="ja-thumbnail" src="'.$imgPath.$thumb.'" rel="popover"/>';
					$option = JHtml::_('select.option',JFile::stripExt($thumb),$img);
					$options[] = $option;
				}
			}
		}
		
		$html = array();
		$class = !empty($this->class) ? ' class="radio ' . $this->class . '"' : ' class="radio"';
		
		$html[] = '<fieldset id="' . $this->id . '"' . $class. ' >';

        foreach ($options as $i => $option)
        {
            $checked = ((string) $option->value == (string) $this->value) ? ' checked="checked"' : '';
            $class = 'class="ja-style"';

            $onclick = !empty($option->onclick) ? ' onclick="' . $option->onclick . '"' : '';
            $onchange = !empty($option->onchange) ? ' onchange="' . $option->onchange . '"' : '';

            $html[] = '<input type="radio" id="' . $this->id . $i . '" name="' . $this->name . '" value="'
                . htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8') . '"' . $checked . $class . $onclick
                . $onchange . ' style="display:none;" />';

            $html[] = '<label for="' . $this->id . $i . '"' . $class . ' >'
                . JText::alt($option->text, preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)) . '</label>';

        }

        $html[] = '</fieldset>';

        return implode($html);
	}
	
	protected function addAssets() {
		$doc = JFactory::getDocument();
		$path = JURI::root().'modules/mod_jaquickcontact/assets/elements/jathumbnail/';
		$doc->addScript($path.'js/script.js');
		$doc->addStyleSheet($path.'css/style.css');
	}
}