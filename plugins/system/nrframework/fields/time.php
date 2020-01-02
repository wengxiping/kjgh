<?php
/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2018 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

// No direct access to this file
defined('_JEXEC') or die;

require_once dirname(__DIR__) . '/helpers/field.php';

class JFormFieldNR_Time extends NRFormField
{
	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	public function getInput()
	{
		// Setup properties
		$this->hint      = $this->get('hint', '00:00');
		$this->class     = $this->get('class', 'input-mini');
		$this->placement = $this->get('placement', 'top');
		$this->align     = $this->get('align', 'left');
		$this->autoclose = $this->get('autoclose', 'true');
		$this->default   = $this->get('default', 'now');
		$this->donetext  = $this->get('donetext', 'Done');

		// Add styles and scripts to DOM
		JHtml::_('jquery.framework');
		JHtml::script('plg_system_nrframework/jquery-clockpicker.min.js', false, true);
		JHtml::stylesheet('plg_system_nrframework/jquery-clockpicker.min.css', false, true);

		static $run;
		// Run once to initialize it
		if (!$run)
		{
			$this->doc->addScriptDeclaration('
				jQuery(function($) {
					$(".clockpicker").clockpicker();
				});
        	');

			// Increase the font-size a little bit on Joomla 4
			if (defined('nrJ4'))
			{
				$this->doc->addStyleDeclaration('
			      	.clockpicker-popover {
			        	font-size: .93rem;
			        }
				');	
			}

			// Fix a CSS conflict caused by the template.css on Joomla 3
			if (!defined('nrJ4'))
			{
				// Fuck you template.css
				$this->doc->addStyleDeclaration('
					.clockpicker-align-left.popover > .arrow {
					    left: 25px;
					}
				');
			}

			$run = true;
		}

		return '
			<div class="input-group input-append clockpicker" data-donetext="' . $this->donetext . '" data-default="' . $this->default . '" data-placement="' . $this->placement . '" data-align="' . $this->align . '" data-autoclose="' . $this->autoclose . '">
				<input class="' . $this->class . ' form-control" placeholder="' . $this->hint . '" name="' . $this->name . '" type="text" class="form-control" value="' . $this->value . '">
				
				<span class="input-group-addon input-group-append">
					<span class="btn btn-secondary">
						<span class="icon-clock"></span>
					</span>
				</span>
			</div>';
	}
}