<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2018 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('JPATH_PLATFORM') or die;
JFormHelper::loadFieldClass('list');

class JFormFieldImagePicker extends JFormFieldList
{
	protected function getInput()
	{	
		$params = (array) $this->element->attributes();
		$params = new JRegistry($params["@attributes"]);

		$showlabels = $params->get('showlabels', 'true');
		$hideselect = $params->get('hideselect', 'true');
		
		JHtml::script('plg_system_nrframework/image-picker.min.js', false, true);
		JHtml::stylesheet('plg_system_nrframework/image-picker.css', false, true);

        JFactory::getDocument()->addScriptDeclaration('
			jQuery(function($) {
				obj = $("#' . $this->id . '");
				obj.imagepicker({
					show_label:  ' . (string) $showlabels . ',
					hide_select: ' . (string) $hideselect . ',
					initialized: function() {
						if (classes = obj.attr("class")) {
							// The custom-select class is added by Joomla 4 and causes styling issues. Needs to be removed.
							classes = classes.replace("custom-select", ""); 

							obj.next().addClass(classes);
						}
					}
				});
			});
        ');

        if ($hideselect == "true")
        {
	        JFactory::getDocument()->addStyleDeclaration('
				#' . $this->id . '_chzn {
					display:none !important;
	    		}
	        ');      	
        }

		return str_replace('onclick="', 'data-img-src="' . JURI::root() . '', parent::getInput());
	}
}
