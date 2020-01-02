<?php
/**
 * @package         EngageBox
 * @version         3.5.2 Pro
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2019 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die;

// load the JS needed to handle the form data and send it back to the editor
$script = '
	jQuery(function($) {
		function insertEngageBoxButton() {
			// Get field values
			boxid = $("#jform_boxid").val();
			type  = $("#jform_type").val();
			label = $("#jform_label").val();
			cmd   = $("#jform_cmd").val();
			cmd   = (cmd == "close" && $("#jform_setcookie0:checked").val() == "1") ? "closeKeep" : cmd;
			
			// Construct HTML
			switch (type) {
				case "button":
					html = $("<button/>", {
						"text": label,
						"type": "button"
					});
					break;
				case "a":
					html = $("<a/>", {
						"text": label,
						"href": $("#jform_href").val()
					});
					if ($("#jform_followurl0:checked").val() == "1") {
						html.attr("data-ebox-prevent", 0)
					}
					break;
				case "div":
					html = $("<div/>", {
						"text": label
					});
					break;
			}
			
			// Common attributes
			html.attr("data-ebox-cmd", cmd);

			if (parseInt(boxid) > 0) {
				html.attr("data-ebox", boxid)
			}

			window.parent.jInsertEditorText(html.get(0).outerHTML, ' . json_encode($this->eName) . ');
			window.parent.jModalClose();
			return false;
		}

		$(".eboxEditorButton button").click(function() {
			insertEngageBoxButton();
		})
	})
';

$style = '
	.eboxEditorButton form, .eboxEditorButton .controls > * {
		margin:0;
	}
	.eboxHeader {
	    border-bottom: 1px dotted #ccc;
	    margin-bottom: 15px;
	    padding-bottom: 5px;
	}
	.eboxHeader p {
	    color:#666;
	    font-size: 11px;
	}
	.eboxHeader h3 {
	    font-size: 16px;
	    margin-bottom: 5px;
	    margin-top: 0;
	}
	.eboxEditorButton .control-group {
	    margin-bottom: 15px;
	}
	.eboxEditorButton {
	    padding: 5px;
	}
';

JFactory::getDocument()->addScriptDeclaration($script);
JFactory::getDocument()->addStyleDeclaration($style);

?>
<div class="eboxEditorButton">
	<form>
		<?php echo $this->form->renderFieldset("main") ?>
		<button class="btn btn-primary span12">
			<?php echo JText::_('PLG_EDITORS-XTD_ENGAGEBOX_INSERTBUTTON'); ?>
		</button>
	</form>
</div>
