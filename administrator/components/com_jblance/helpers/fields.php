<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	13 March 2012
 * @file name	:	helpers/fields.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Helper Class to generate Fields (jblance)
 */
defined('_JEXEC') or die('Restricted access');

class JBFieldsHelper {

	var $id	= null;
    //6.ShowCustom
    function getNewFieldHTML($ct, $id = null, $type = 'profile'){
        JHtml::_('bootstrap.tooltip');
        if(!empty($ct)){
            $row = null;
            if(!empty($id)){
                $db = JFactory::getDbo();
                if($type == 'project'){
                    $query = "SELECT * FROM #__jblance_custom_field_value WHERE projectid=".$db->quote($id)." AND fieldid=".$db->quote($ct->id);
                }
                else {
                    $query = "SELECT * FROM #__jblance_custom_field_value WHERE userid=".$db->quote($id)." AND fieldid=".$db->quote($ct->id);
                }

                $db->setQuery($query);
                $row = $db->loadObject();
            }
            $title = JHtml::tooltipText(JText::_($ct->field_title), JText::_($ct->tips));
            $val = (!empty($row->value)) ? $row->value : null;
            $req = '';
            if($ct->required){
                if($ct->field_type == 'Radio') $req = 'validate-Radio';
                elseif($ct->field_type == 'Checkbox') $req = 'validate-checkbox';
                elseif($ct->field_type == 'Multiple select') $req = 'validate-dropdown';
                else $req = 'required';
            }
            switch($ct->field_type){
                case 'Textbox':
                    ?>
                    <input class="hasTooltip <?php echo $ct->class.' '.$req; ?>" type="text" name="custom_field_<?php echo $ct->id; ?>" id="custom_field_<?php echo $ct->id; ?>" value="<?php echo $val; ?>" title="<?php echo $title; ?>" />
                    <?php
                    break;
                case 'Textarea':
                    //$val = (!empty($row[$ct->id]->valuetext)) ? $row[$ct->id]->valuetext : null;
                    ?>
                    <div class="xp-right-textarea"><textarea style="resize: none;" class="hasTooltip <?php echo $ct->class.' '.$req; ?>" rows="4" cols="50" data-id="<?php echo $ct->id; ?>" name="custom_field_<?php echo $ct->id; ?>" id="custom_field_<?php echo $ct->id; ?>" title="<?php echo $title; ?>" ><?php echo $val; ?></textarea>
                    </div>
                    <?php
                    break;
                case 'Radio':
                    if(!empty($ct->value)){
                        $values = explode(";", $ct->value);
                        $put = array();
                        $inline = ($ct->show_type == 'left-to-right') ? 'inline' : '';	// if it is left-to-right, add inline css
                        foreach($values as $value){
                            if($value){
                                $checked = ($val == $value) ? ' checked': '';
                                ?>
                                <label class="radio <?php echo $inline?>">
                                    <input class="hasTooltip <?php echo $ct->class.' '.$req; ?>" type="radio" name="custom_field_<?php echo $ct->id; ?>" title="<?php echo $title; ?>" value="<?php echo $value; ?>"<?php echo $checked; ?>> <?php echo JText::_($value); ?>
                                </label>
                                <?php
                            }
                        }
                    }
                    break;
                case 'Checkbox':
                    if(!empty($ct->value)){
                        $values = explode(";", $ct->value);
                        $put = array();
                        if($ct->show_type == 'left-to-right'){
                            $x = 0;
                            foreach($values as $value){
                                if($value){
                                    $checked = (in_array($value, explode(";", $val))) ? ' checked': '';
                                    ?>
                                    <input id="c<?php echo $ct->id;?>b<?php echo $x; ?>" class="hasTooltip <?php echo $ct->class.' '.$req; ?>" type="checkbox" title="<?php echo $title; ?>" name="custom_field_<?php echo $ct->id; ?>[]" value="<?php echo $value; ?>"<?php echo $checked; ?>> <?php echo JText::_($value); ?>&nbsp;
                                    <?php
                                    $x++;
                                }
                            }
                        }
                        else {
                            $x = 0;
                            foreach($values as $value){
                                if($value){
                                    $checked = (in_array($value, explode(";",$val))) ? ' checked': '';
                                    ?>
                                    <input id="c<?php echo $ct->id;?>b<?php echo $x; ?>" class="hasTooltip <?php echo $ct->class.' '.$req; ?>" type="checkbox" title="<?php echo $title; ?>" name="custom_field_<?php echo $ct->id; ?>[]" value="<?php echo $value; ?>"<?php echo $checked; ?>> <?php echo JText::_($value); ?><br />
                                    <?php
                                    $x++;
                                }
                            }
                        }
                    }
                    break;
                case 'Select':
                    if($ct->value_type == 'custom'){
                        ?>
                        <div class="xp-right-select">
                        <?php
                        if(!empty($ct->value)){
                            $values = explode(";", $ct->value);
                            $put = array();
                            foreach($values as $value){
                                if($value){
                                    $put[] = JHtml::_('select.option', $value, JText::_($value), 'value', 'text');
                                }
                            }
                            echo JHtml::_('select.genericlist', $put, 'custom_field_'.$ct->id, "class='hasTooltip $ct->class $req' title='$title' size='1'", 'value', 'text', $val);
                        }
                        ?>
                                        <span class="pull-right">
                                            展示状态 ：
                            				<?php
                                            $access = (!empty($row->access)) ? $row->access : 0;
                                            $select = JblanceHelper::get('helper.select');
                                            $privacy = $select->getSelectPrivacy('field_access_'.$ct->id, $access, $ct->visible, $ct->field_for);
                                            echo $privacy;
                                            ?>
                            				</span>
                        </div>
                        <?php
                    }
                    else {
                        $select = JblanceHelper::get('helper.select');		// create an instance of the class selectHelper
                        $attribs = "class='hasTooltip $ct->class $req' title='$title' size='1'";
                        echo $select->getSelectTable($ct->value, 'custom_field_'.$ct->id, $val, 'COM_JBLANCE_PLEASE_SELECT', $attribs, '');
                    }
                    break;
                case 'Multiple Select':
                    if(!empty($ct->value)){
                        $values = explode(";", $ct->value);
                        $put = array();
                        $val =  explode(";", $val);
                        foreach($values as $value){
                            if($value){
                                $put[] = JHtml::_('select.option', $value, JText::_($value), 'value', 'text');
                            }
                        }
                        $size = (count($put) < 5) ? count($put) : 5;
                        echo JHtml::_('select.genericlist', $put, 'custom_field_'.$ct->id.'[]', "class='hasTooltip $ct->class $req' title='$title' size='$size' multiple", 'value', 'text', $val );
                        echo '&nbsp;';
                        echo JText::_('COM_JBLANCE_HOLD_CTRL_FOR_MULTIPLE_SELECT');
                    }
                    break;
                case 'Location':
                    $select = JblanceHelper::get('helper.select');		// create an instance of the class selectHelper
                    $attribs = "class='hasTooltip $ct->class $req' size='10'";
                    $loctree = $select->getSelectLocationTree('custom_field_'.$ct->id, $val, 'COM_JBLANCE_PLEASE_SELECT', $attribs, '');
                    echo $loctree;
                    break;
                case 'Location2':
                    $doc = JFactory::getDocument();
                    $doc->addScript("components/com_jblance/js/Autocompleter.js");
                    $doc->addStyleSheet("components/com_jblance/css/Autocompleter.css");

                    $js = "\nwindow.addEvent('domready', function() {";
                    $js .= "\n\tnew Autocompleter.Request.JSON('custom_field_$ct->id', 'index.php?option=com_jblance&task=getlocation', {";
                    $js .= "\n'postVar': 'custom_field_$ct->id',
					postData: {'fieldName': 'custom_field_$ct->id'},
					'overflow': true,
					'selectMode': 'type-ahead'
					});
					});\n";
                    $doc->addScriptDeclaration($js);
                    ?>
                    <input class="hasTooltip <?php echo $ct->class.' '.$req; ?>" type="text" name="custom_field_<?php echo $ct->id; ?>" id="custom_field_<?php echo $ct->id; ?>" value="<?php echo $val; ?>" title="<?php echo $title; ?>" />

                    <?php
                    break;
                case 'URL':
                    ?>
                    <input class="hasTooltip <?php echo $ct->class.' '.$req; ?>" type="text" name="custom_field_<?php echo $ct->id; ?>" id="custom_field_<?php echo $ct->id; ?>" value="<?php echo $val; ?>" title="<?php echo $title; ?>" />
                    <?php
                    break;
                case 'Email':
                    ?>
                    <input class="hasTooltip <?php echo $ct->class.' '.$req; ?>" type="text" name="custom_field_<?php echo $ct->id; ?>" id="custom_field_<?php echo $ct->id; ?>" value="<?php echo $val; ?>" title="<?php echo $title; ?>" />
                    <?php
                    break;
                case 'Date':
                    echo JHtml::_('calendar', $val, 'custom_field_'.$ct->id, 'custom_field_'.$ct->id, '%Y-%m-%d', array('class'=>$ct->class.' hasTooltip '.$req, 'title'=>$title));
                    break;
                case 'Birthdate':
                    $doc = JFactory::getDocument();
                    $doc->addScript(JURI::root()."components/com_jblance/js/jason-calendar.js");
                    $translation = self::translateCalendar();

                    if ($translation)
                        $doc->addScriptDeclaration($translation);

                    if(!empty($val))
                        echo "<script>DateInput('custom_field_$ct->id', true, 'YYYY-MM-DD', '$val')</script>";
                    else
                        echo "<script>DateInput('custom_field_$ct->id', true, 'YYYY-MM-DD')</script>";
                    break;
                case 'File':
                    $config = JblanceHelper::getConfig();
                    $tipmsg = JHtml::tooltipText(JText::_($ct->field_title), JText::_('COM_JBLANCE_ALLOWED_FILE_TYPES').' : '.$config->projectFileText.'<br>'.JText::_('COM_JBLANCE_MAXIMUM_FILE_SIZE').' : '.$config->projectMaxsize.' kB');
                    // the download task is based on front/backend basis
                    $app	= JFactory::getApplication();
                    $downloadTask = ($app->isAdmin()) ? 'admproject.download' : 'project.download';
                    ?>
                    <span class="hasTooltip" title="<?php echo $tipmsg; ?>"><i class="jbf-icon-question-sign"></i></span>
                    <input type="file" name="custom_field_<?php echo $ct->id; ?>" id="custom_field_<?php echo $ct->id; ?>" class="<?php echo (empty($val) ? $req : ''); ?>" />
                    <?php if(!empty($val)){ ?>
                    <label class="checkbox">
                        <input type="checkbox" name="chk-customfile-<?php echo $ct->id; ?>" value="<?php echo $val; ?>" />
                        <?php echo LinkHelper::getDownloadLink('customfile', $row->id, $downloadTask); //$row->id is the unique 'id' value of table custom field value ?>
                    </label>
                    <input type="hidden" name="hdn-customfile-<?php echo $ct->id; ?>" value="<?php echo $val; ?>" />
                <?php } ?>
                    <?php
                    break;
                case 'YouTube':
                    $doc = JFactory::getDocument();
                    $js = "
							jQuery(document).ready(function(){
								document.formvalidator.setHandler('youtube', function(value){
									regex = /^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/;
									return regex.test(value);
								});
							});
						";
                    $doc->addScriptDeclaration($js);
                    ?>
                    <input class="hasTooltip input-xxlarge validate-youtube <?php echo $ct->class.' '.$req; ?>" type="text" name="custom_field_<?php echo $ct->id; ?>" id="custom_field_<?php echo $ct->id; ?>" value="<?php echo $val; ?>" title="<?php echo $title; ?>" />
                    <?php
                    break;
            }
        }
    }
	//6.ShowCustom
	function getFieldHTML($ct, $id = null, $type = 'profile'){
		JHtml::_('bootstrap.tooltip');
		if(!empty($ct)){
			$row = null;
			if(!empty($id)){
				$db = JFactory::getDbo();
				if($type == 'project'){
					$query = "SELECT * FROM #__jblance_custom_field_value WHERE projectid=".$db->quote($id)." AND fieldid=".$db->quote($ct->id);
				}
				else {
					$query = "SELECT * FROM #__jblance_custom_field_value WHERE userid=".$db->quote($id)." AND fieldid=".$db->quote($ct->id);
				}

				$db->setQuery($query);
				$row = $db->loadObject();
			}
			$title = JHtml::tooltipText(JText::_($ct->field_title), JText::_($ct->tips));
			$val = (!empty($row->value)) ? $row->value : null;
			$req = '';
			if($ct->required){
				if($ct->field_type == 'Radio') $req = 'validate-Radio';
				elseif($ct->field_type == 'Checkbox') $req = 'validate-checkbox';
				elseif($ct->field_type == 'Multiple select') $req = 'validate-dropdown';
				else $req = 'required';
			}
			switch($ct->field_type){
				case 'Textbox':
					?>
					<input class="hasTooltip <?php echo $ct->class.' '.$req; ?>" type="text" name="custom_field_<?php echo $ct->id; ?>" id="custom_field_<?php echo $ct->id; ?>" value="<?php echo $val; ?>" title="<?php echo $title; ?>" />
				<?php
				break;
				case 'Textarea':
					//$val = (!empty($row[$ct->id]->valuetext)) ? $row[$ct->id]->valuetext : null;
					?>
					<textarea class="hasTooltip <?php echo $ct->class.' '.$req; ?>" rows="4" cols="50" name="custom_field_<?php echo $ct->id; ?>" id="custom_field_<?php echo $ct->id; ?>" title="<?php echo $title; ?>" ><?php echo $val; ?></textarea>
					<?php
				break;
				case 'Radio':
					if(!empty($ct->value)){
						$values = explode(";", $ct->value);
						$put = array();
						$inline = ($ct->show_type == 'left-to-right') ? 'inline' : '';	// if it is left-to-right, add inline css
						foreach($values as $value){
							if($value){
								$checked = ($val == $value) ? ' checked': '';
								?>
								<label class="radio <?php echo $inline?>">
									<input class="hasTooltip <?php echo $ct->class.' '.$req; ?>" type="radio" name="custom_field_<?php echo $ct->id; ?>" title="<?php echo $title; ?>" value="<?php echo $value; ?>"<?php echo $checked; ?>> <?php echo JText::_($value); ?>
								</label>
							<?php
							}
						}
					}
				break;
				case 'Checkbox':
					if(!empty($ct->value)){
						$values = explode(";", $ct->value);
						$put = array();
						if($ct->show_type == 'left-to-right'){
							$x = 0;
							foreach($values as $value){
								if($value){
									$checked = (in_array($value, explode(";", $val))) ? ' checked': '';
									?>
									<input id="c<?php echo $ct->id;?>b<?php echo $x; ?>" class="hasTooltip <?php echo $ct->class.' '.$req; ?>" type="checkbox" title="<?php echo $title; ?>" name="custom_field_<?php echo $ct->id; ?>[]" value="<?php echo $value; ?>"<?php echo $checked; ?>> <?php echo JText::_($value); ?>&nbsp;
									<?php
									$x++;
								}
							}
						}
						else {
							$x = 0;
							foreach($values as $value){
								if($value){
									$checked = (in_array($value, explode(";",$val))) ? ' checked': '';
									?>
									<input id="c<?php echo $ct->id;?>b<?php echo $x; ?>" class="hasTooltip <?php echo $ct->class.' '.$req; ?>" type="checkbox" title="<?php echo $title; ?>" name="custom_field_<?php echo $ct->id; ?>[]" value="<?php echo $value; ?>"<?php echo $checked; ?>> <?php echo JText::_($value); ?><br />
									<?php
									$x++;
								}
							}
						}
					}
				break;
				case 'Select':
					if($ct->value_type == 'custom'){
						if(!empty($ct->value)){
							$values = explode(";", $ct->value);
							$put = array();
							foreach($values as $value){
								if($value){
									$put[] = JHtml::_('select.option', $value, JText::_($value), 'value', 'text');
								}
							}
							echo JHtml::_('select.genericlist', $put, 'custom_field_'.$ct->id, "class='hasTooltip $ct->class $req' title='$title' size='1'", 'value', 'text', $val);
						}
					}
					else {
						$select = JblanceHelper::get('helper.select');		// create an instance of the class selectHelper
						$attribs = "class='hasTooltip $ct->class $req' title='$title' size='1'";
						echo $select->getSelectTable($ct->value, 'custom_field_'.$ct->id, $val, 'COM_JBLANCE_PLEASE_SELECT', $attribs, '');
					}
				break;
				case 'Multiple Select':
					if(!empty($ct->value)){
						$values = explode(";", $ct->value);
						$put = array();
						$val =  explode(";", $val);
						foreach($values as $value){
							if($value){
								$put[] = JHtml::_('select.option', $value, JText::_($value), 'value', 'text');
							}
						}
						$size = (count($put) < 5) ? count($put) : 5;
						echo JHtml::_('select.genericlist', $put, 'custom_field_'.$ct->id.'[]', "class='hasTooltip $ct->class $req' title='$title' size='$size' multiple", 'value', 'text', $val );
						echo '&nbsp;';
						echo JText::_('COM_JBLANCE_HOLD_CTRL_FOR_MULTIPLE_SELECT');
					}
				break;
				case 'Location':
					$select = JblanceHelper::get('helper.select');		// create an instance of the class selectHelper
					$attribs = "class='hasTooltip $ct->class $req' size='10'";
					$loctree = $select->getSelectLocationTree('custom_field_'.$ct->id, $val, 'COM_JBLANCE_PLEASE_SELECT', $attribs, '');
					echo $loctree;
				break;
				case 'Location2':
					$doc = JFactory::getDocument();
					$doc->addScript("components/com_jblance/js/Autocompleter.js");
					$doc->addStyleSheet("components/com_jblance/css/Autocompleter.css");

					$js = "\nwindow.addEvent('domready', function() {";
					$js .= "\n\tnew Autocompleter.Request.JSON('custom_field_$ct->id', 'index.php?option=com_jblance&task=getlocation', {";
					$js .= "\n'postVar': 'custom_field_$ct->id',
					postData: {'fieldName': 'custom_field_$ct->id'},
					'overflow': true,
					'selectMode': 'type-ahead'
					});
					});\n";
					$doc->addScriptDeclaration($js);
					?>
					<input class="hasTooltip <?php echo $ct->class.' '.$req; ?>" type="text" name="custom_field_<?php echo $ct->id; ?>" id="custom_field_<?php echo $ct->id; ?>" value="<?php echo $val; ?>" title="<?php echo $title; ?>" />

					<?php
				break;
				case 'URL':
					?>
					<input class="hasTooltip <?php echo $ct->class.' '.$req; ?>" type="text" name="custom_field_<?php echo $ct->id; ?>" id="custom_field_<?php echo $ct->id; ?>" value="<?php echo $val; ?>" title="<?php echo $title; ?>" />
					<?php
				break;
				case 'Email':
					?>
					<input class="hasTooltip <?php echo $ct->class.' '.$req; ?>" type="text" name="custom_field_<?php echo $ct->id; ?>" id="custom_field_<?php echo $ct->id; ?>" value="<?php echo $val; ?>" title="<?php echo $title; ?>" />
					<?php
				break;
				case 'Date':
					echo JHtml::_('calendar', $val, 'custom_field_'.$ct->id, 'custom_field_'.$ct->id, '%Y-%m-%d', array('class'=>$ct->class.' hasTooltip '.$req, 'title'=>$title));
				break;
				case 'Birthdate':
					$doc = JFactory::getDocument();
					$doc->addScript(JURI::root()."components/com_jblance/js/jason-calendar.js");
					$translation = self::translateCalendar();

					if ($translation)
						$doc->addScriptDeclaration($translation);

					if(!empty($val))
						echo "<script>DateInput('custom_field_$ct->id', true, 'YYYY-MM-DD', '$val')</script>";
					else
						echo "<script>DateInput('custom_field_$ct->id', true, 'YYYY-MM-DD')</script>";
				break;
				case 'File':
					$config = JblanceHelper::getConfig();
					$tipmsg = JHtml::tooltipText(JText::_($ct->field_title), JText::_('COM_JBLANCE_ALLOWED_FILE_TYPES').' : '.$config->projectFileText.'<br>'.JText::_('COM_JBLANCE_MAXIMUM_FILE_SIZE').' : '.$config->projectMaxsize.' kB');
					// the download task is based on front/backend basis
					$app	= JFactory::getApplication();
					$downloadTask = ($app->isAdmin()) ? 'admproject.download' : 'project.download';
					?>
					<span class="hasTooltip" title="<?php echo $tipmsg; ?>"><i class="jbf-icon-question-sign"></i></span>
					<input type="file" name="custom_field_<?php echo $ct->id; ?>" id="custom_field_<?php echo $ct->id; ?>" class="<?php echo (empty($val) ? $req : ''); ?>" />
					<?php if(!empty($val)){ ?>
					<label class="checkbox">
						<input type="checkbox" name="chk-customfile-<?php echo $ct->id; ?>" value="<?php echo $val; ?>" />
	  					<?php echo LinkHelper::getDownloadLink('customfile', $row->id, $downloadTask); //$row->id is the unique 'id' value of table custom field value ?>
					</label>
					<input type="hidden" name="hdn-customfile-<?php echo $ct->id; ?>" value="<?php echo $val; ?>" />
					<?php } ?>
				<?php
				break;
				case 'YouTube':
					$doc = JFactory::getDocument();
					$js = "
							jQuery(document).ready(function(){
								document.formvalidator.setHandler('youtube', function(value){
									regex = /^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/;
									return regex.test(value);
								});
							});
						";
					$doc->addScriptDeclaration($js);
					?>
					<input class="hasTooltip input-xxlarge validate-youtube <?php echo $ct->class.' '.$req; ?>" type="text" name="custom_field_<?php echo $ct->id; ?>" id="custom_field_<?php echo $ct->id; ?>" value="<?php echo $val; ?>" title="<?php echo $title; ?>" />
					<?php
				break;
			}
			?>
				<span class="pull-right">
				<?php
				$access = (!empty($row->access)) ? $row->access : 0;
				$select = JblanceHelper::get('helper.select');
				$privacy = $select->getSelectPrivacy('field_access_'.$ct->id, $access, $ct->visible, $ct->field_for);
				echo $privacy;
				?>
				</span>
		<?php


		}
	}

	protected static function translateCalendar(){
		static $jsscript = 0;

		if ($jsscript == 0){
			$return = 'WeekDays = new Array ("' . JText::_('SUNDAY', true) . '", "' . JText::_('MONDAY', true) . '", "'
			. JText::_('TUESDAY', true) . '", "' . JText::_('WEDNESDAY', true) . '", "' . JText::_('THURSDAY', true) . '", "'
			. JText::_('FRIDAY', true) . '", "' . JText::_('SATURDAY', true) . '", "' . JText::_('SUNDAY', true) . '");'
			. ' WeekDaysShort = new Array ("' . JText::_('SUN', true) . '", "' . JText::_('MON', true) . '", "' . JText::_('TUE', true) . '", "'
			. JText::_('WED', true) . '", "' . JText::_('THU', true) . '", "' . JText::_('FRI', true) . '", "' . JText::_('SAT', true) . '", "'
			. JText::_('SUN', true) . '"); MonthNames = new Array ("' . JText::_('JANUARY', true) . '", "'
			. JText::_('FEBRUARY', true) . '", "' . JText::_('MARCH', true) . '", "' . JText::_('APRIL', true) . '", "' . JText::_('MAY', true)
			. '", "' . JText::_('JUNE', true) . '", "' . JText::_('JULY', true) . '", "' . JText::_('AUGUST', true) . '", "'
			. JText::_('SEPTEMBER', true) . '", "' . JText::_('OCTOBER', true) . '", "' . JText::_('NOVEMBER', true) . '", "'
			. JText::_('DECEMBER', true) . '");' . ' MonthNamesShort = new Array ("' . JText::_('JANUARY_SHORT', true) . '", "'
			. JText::_('FEBRUARY_SHORT', true) . '", "' . JText::_('MARCH_SHORT', true) . '", "' . JText::_('APRIL_SHORT', true) . '", "'
			. JText::_('MAY_SHORT', true) . '", "' . JText::_('JUNE_SHORT', true) . '", "' . JText::_('JULY_SHORT', true) . '", "'
			. JText::_('AUGUST_SHORT', true) . '", "' . JText::_('SEPTEMBER_SHORT', true) . '", "' . JText::_('OCTOBER_SHORT', true) . '", "'
			. JText::_('NOVEMBER_SHORT', true) . '", "' . JText::_('DECEMBER_SHORT', true) . '");'
			. ' CalendarTT = {};CalendarTT["TODAY"] = "' . JText::_('JLIB_HTML_BEHAVIOR_GO_TODAY', true) . '";'
			. ' CalendarTT["YEAR"] = "' . JText::_('JYEAR', true) . '"; CalendarTT["MONTH"] = "' . JText::_('', true) . '";'
			. ' CalendarTT["DATE"] = "' . JText::_('JDATE', true) . '"; CalendarTT["CALENDAR"] = "' . JText::_('JLIB_HTML_CALENDAR', true) . '";'
			;
			$jsscript = 1;
			return $return;
		}
		else {
			return false;
		}
	}

	//7.showCustomFieldValue
	function getFieldHTMLValues($ct = null, $id = null, $type = 'profile'){
		if(!empty($ct)){
			$row = null;
			if(!empty($id)){
				$db = JFactory::getDbo();
				if($type == 'project'){
					$query = "SELECT * FROM #__jblance_custom_field_value WHERE projectid=".$db->quote($id)." AND fieldid=".$db->quote($ct->id);
				}
				else {
					$query = "SELECT * FROM #__jblance_custom_field_value WHERE userid=".$db->quote($id)." AND fieldid=".$db->quote($ct->id);
				}
				$db->setQuery($query);
				$row = $db->loadObject();
			}
			$val = (!empty($row->value)) ? $row->value : null;
			switch($ct->field_type){
				case 'Textbox':
				case 'Location2':
				case 'Radio':
					$val = JText::_($val);
					break;
				case 'Textarea':
					$val = nl2br($val);
					break;
				case 'Select':
					//if the field is Database type, then join with the correspondng table to get the actual value.
					if($ct->value_type == 'database'){
						$select  = JblanceHelper::get('helper.select');
						$colName = $select->getColumnName($row[$ct->id]->tblName);
						$query = "SELECT ".$db->quoteName($colName)." FROM ".$db->quoteName($row[$ct->id]->tblName)." ".
								 "WHERE id=".$db->quote($row[$ct->id]->value)." AND published=1";
						$db->setQuery($query);
						$val = $db->loadResult();
					}
					else
						$val = JText::_($val);
					break;
				case 'Checkbox':
				case 'Multiple Select':
					$val = explode(";", $val);
					$newval = array();
					foreach($val as $key=>$value){
						$newval[] = JText::_($value);		//this extra step to convert for language translation
					}
					$val = implode(", ", $newval);
					break;
				case 'Location':
					$temp = JblanceHelper::getFullLocationTree($val);
					$val = implode (', ', $temp);
					break;
				case 'URL':
					if(!empty($val)){
						if(preg_match("#https?://#", $val) === 0){
							$val = 'http://'.$val;
						}
						$val = '<a href='.$val.' target="_blank">'.$val.'</a>';
					}
					else {
						$val = '';
					}
					break;
				case 'Email':
					$val = ($val != '') ? '<a href=mailto:'.$val.' target="_blank">'.$val.'</a>' : '';
					break;
				case 'Date':
				case 'Birthdate':
					$config = JblanceHelper::getConfig();
					$dformat = $config->dateFormat;
					$val = ($val != '') ? JHtml::_('date', $val, $dformat, false) : '';
					break;
				case 'File':
					$val = ($val != '') ? LinkHelper::getDownloadLink('customfile', $row->id, 'project.download') : '';	//$row->id is the unique id of custom field value table
					break;
				case 'YouTube':
					if(!empty($val)){
						$youtubeUrl =  JUri::getInstance($val);
						$videoId = $youtubeUrl->getVar('v');

						$val = '
								<div>
									<object width="426" height="240">
										<param name="movie" value="https://www.youtube.com/v/'.$videoId.'?version=3"></param>
										<param name="allowScriptAccess" value="always"></param>
										<embed src="https://www.youtube.com/v/'.$videoId.'?version=3" type="application/x-shockwave-flash" allowscriptaccess="always" width="426" height="240"></embed>
									</object>
								</div>
							   ';
					?>

					<?php
					//$val = ' ';
					}
					break;
			}
			if(!empty($val)){
				$val  = $this->getFieldPrivacy($val, $row->access, $row->userid, $ct->visible);
				echo $val;
			}
			else {
				echo '<span class="label label-important">'.JText::_('COM_JBLANCE_NOT_MENTIONED').'</span>';
			}
		}
	}

	function getFieldPrivacy($val, $access, $targetId, $visible){
		$user = JFactory::getUser();
		$myId = $user->id;

		$returnVal = '<span class="label label-inverse">'.JText::_('COM_JBLANCE_CONFIDENTIAL').'</span>';

		// if the field is personal, return it confidential
		if($visible == 'personal' && $myId != $targetId)
			return $returnVal;

		switch($access){
			case 0 :
				$returnVal = $val;
				break;
			case 10 :
				if(!$user->guest)
					$returnVal = $val;
				break;
			case 20 :
				if($myId == $targetId)
					$returnVal = $val;
				break;
			default:
				$returnVal = JText::_('COM_JBLANCE_CONFIDENTIAL');
				break;
		}
		return $returnVal;
	}

	//6.ShowCustom
	function getSearchFieldHTML($custom = null, $sfield = null, $type = 'profile'){
		if(!empty($custom)){
			$row = null;

			if(count($sfield)){
				foreach($sfield as $key=>$value){
					$row[$key] = $value;
				}
			} ?>
			<table class="" width="100%">
			<?php foreach($custom as $ct){?>
				<tr>
					<td class="key" valign="top">
						<?php
							$title = JText::_($ct->field_title).':: '.JText::_($ct->tips);
							$labelsuffix = '';
							if($ct->field_type == 'Checkbox') $labelsuffix = '[]'; //added to validate checkbox
						?>
						<label title="<?php echo $title; ?>" class="hasTooltip" for="custom_field_<?php echo $ct->id.$labelsuffix; ?>"><?php echo JText::_($ct->field_title); ?><span class="redfont"><?php echo ($ct->required)? '*' : ''; ?></span>:</label>
					</td>
					<td>
						<?php
						$val = (!empty($row[$ct->id])) ? $row[$ct->id] : null;
						$req = '';
						if($ct->required){
							if($ct->field_type == 'radio') $req = 'validate-radio';
							elseif($ct->field_type == 'Checkbox') $req = 'validate-checkbox';
							elseif($ct->field_type == 'Multiple Select') $req = 'validate-dropdown';
							else $req = 'required';
						}
						switch($ct->field_type){
						case 'Radio':
							if(!empty($ct->value)){
								$values = explode(";", $ct->value);
								$put = array();
								if($ct->show_type == 'left-to-right'){
									foreach($values as $value){
										if($value){
											$put[] = JHtml::_('select.option',  $value, JText::_($value));
										}
									}
									echo JHtml::_('select.radiolist', $put, 'sfields['.$ct->id.']', "class='hasTooltip $ct->class $req' title='$title'", 'value', 'text', $val);?>
								<?php
								}
								else {
									foreach($values as $value){
										if($value){
											$checked = ($val == $value) ? ' checked': '';
										?>
											<input class="hasTooltip <?php echo $ct->class.' '.$req; ?>" type="radio" name="sfields[<?php echo $ct->id; ?>]" title="<?php echo $title; ?>" value="<?php echo $value; ?>"<?php echo $checked; ?>> <?php echo $value; ?><br />
										<?php
										}
									}
								}
							}
						break;
						case 'Checkbox':
							if(!empty($ct->value)){
								$values = explode(";", $ct->value);
								$put = array();
								if($ct->show_type == 'left-to-right'){
									$x = 0;
									foreach($values as $value){
										if($value){
											if(!empty($val))
												$checked = (in_array($value, $val)) ? ' checked': '';
											?>
											<input id="c<?php echo $ct->id;?>b<?php echo $x; ?>" class="hasTooltip <?php echo $ct->class.' '.$req; ?>" type="checkbox" title="<?php echo $title; ?>" name="sfields[<?php echo $ct->id; ?>][]" value="<?php echo $value; ?>"<?php echo $checked; ?>> <?php echo $value; ?>&nbsp;
											<?php
											$x++;
										}
									}
								}
								else {
									$x = 0;
									foreach($values as $value){
										if($value){
											if(!empty($val))
												$checked = (in_array($value, $val)) ? ' checked': '';
											?>
											<input id="c<?php echo $ct->id;?>b<?php echo $x; ?>" class="hasTooltip <?php echo $ct->class.' '.$req; ?>" type="checkbox" title="<?php echo $title; ?>" name="sfields[<?php echo $ct->id; ?>][]" value="<?php echo $value; ?>"<?php echo $checked; ?>> <?php echo $value; ?><br />
											<?php
											$x++;
										}
									}
								}
							}
						break;
						case 'Select':
							if($ct->value_type == 'custom'){
								if(!empty($ct->value)){
									$values = explode(";", $ct->value);
									$put = array();
									foreach($values as $value){
										if($value){
											$put[] = JHtml::_('select.option', $value, JText::_($value), 'value', 'text');
										}
									}
									echo JHtml::_('select.genericlist', $put, 'sfields['.$ct->id.']', "class='hasTooltip $ct->class $req' title='$title' size='1'", 'value', 'text', $val);
								}
							}
							else {
								$select = JblanceHelper::get('helper.select');
								$attribs = "class='hasTooltip $ct->class $req' title='$title' size='1'";
								echo $select->getSelectTable($ct->value, 'sfields['.$ct->id.']', $val, 'COM_JBLANCE_PLEASE_SELECT', $attribs, '');
							}
						break;
						case 'Location':
							$select = JblanceHelper::get('helper.select');		// create an instance of the class selectHelper
							$attribs = "class='hasTooltip $ct->class $req' size='10'";
							$loctree = $select->getSelectLocationTree('sfields['.$ct->id.']', $val, 'COM_JBLANCE_PLEASE_SELECT', $attribs, '');
							echo $loctree;
						break;
						case 'Multiple Select':
							if(!empty($ct->value)){
								$values = explode(";", $ct->value);
								$put = array();
								foreach($values as $value){
									if($value){
										$put[] = JHtml::_('select.option', $value, JText::_($value), 'value', 'text');
									}
								}
								$size = (count($put) < 5) ? count($put) : 5;
								echo JHtml::_('select.genericlist', $put, 'sfields['.$ct->id.'][]', "class='hasTooltip $ct->class $req' title='$title' size='$size' multiple", 'value', 'text', $val);
								echo '&nbsp;';
								echo JText::_('COM_JBLANCE_HOLD_CTRL_FOR_MULTIPLE_SELECT');
							}
						break;
					}
						?>
					</td>
				</tr>
			<?php } ?>
			</table>
			<?php
		}
	}

	//1.Insert Custom Field
	function saveFieldValues($type, $id, $post){
		$db = JFactory::getDbo();
		$app  = JFactory::getApplication();

		$query = "SELECT * FROM #__jblance_custom_field WHERE field_for=".$db->quote($type)." AND published=1";
		$db->setQuery($query);
		$custom = $db->loadObjectList();

		if(count($custom)){
			foreach($custom as $ct){
				$file = $app->input->files->get('custom_field_'.$ct->id);
				if(isset($post['custom_field_'.$ct->id]) or ($file['size'] > 0)){

					$value = isset($post['custom_field_'.$ct->id]) ? $post['custom_field_'.$ct->id] : '';
					$value = (is_array($value)) ? implode(";", $value) : $value; 		// $value will be array in case of multiple select and checkbox
					$access = $post['field_access_'.$ct->id];

					switch($ct->field_type){
						case 'Textarea':
							//$fvalue = 'valuetext';
							break;
						case 'Checkbox':
						case 'Multiple Select':
							//$value = (is_array($value)) ? implode(";", $value) : $value;
							break;
						case 'File':
							$value = isset($post['hdn-customfile-'.$ct->id]) ? $post['hdn-customfile-'.$ct->id] : '';
							$response = JBMediaHelper::uploadCustomFieldFile($ct->id);
							if($response['result'] == 'OK'){
								$value = $response['attachvalue'];
							}
							elseif($response['result'] == 'NO')
								$value = '';

							break;
					}
					if($type == 'project'){
						$query = "SELECT COUNT(*) FROM #__jblance_custom_field_value WHERE fieldid=".$db->quote($ct->id)." AND projectid=".$db->quote($id);
					}
					else {
						$query = "SELECT COUNT(*) FROM #__jblance_custom_field_value WHERE fieldid=".$db->quote($ct->id)." AND userid=".$db->quote($id);
					}
					$db->setQuery($query);
					if($db->loadResult()){
						if($type == 'project'){
							$query = "UPDATE #__jblance_custom_field_value SET value=".$db->quote($value).", access=".$db->quote($access)." WHERE fieldid=".$db->quote($ct->id)." AND projectid=".$db->quote($id);
						}
						else {
							$query = "UPDATE #__jblance_custom_field_value SET value=".$db->quote($value).", access=".$db->quote($access)." WHERE fieldid=".$db->quote($ct->id)." AND userid=".$db->quote($id);
						}
					}
					else {
						if($type == 'project'){
							$query = "INSERT INTO #__jblance_custom_field_value (fieldid, projectid, value, access) VALUES (".$db->quote($ct->id).", ".$db->quote($id).", ".$db->quote($value).", ".$db->quote($access).")";
						}
						else {
							$query = "INSERT INTO #__jblance_custom_field_value (fieldid, userid, value, access) VALUES (".$db->quote($ct->id).", ".$db->quote($id).", ".$db->quote($value).", ".$db->quote($access).")";
						}
					}

					$db->setQuery($query);
					if(!$db->execute()){
						JError::raiseError(500, $db->getErrorMsg());
					}
				}
			}
		}
	}


	/**
	 * Return the custom profile data based on the given field code
	 *
	 * @param	string	$fieldCode	The field code that is given for the specific field.
	 */
	public function getFieldValue($fieldId, $user_project_id, $field_type = 'profile'){

		$db		= JFactory::getDbo();

		$user_or_project = ($field_type == 'profile') ? 'b.userid' : 'b.projectid';

		/* $query	= "SELECT b.*,a.field_type,a.value_type,a.value AS tblName FROM #__jblance_custom_field AS a "
				. "INNER JOIN #__jblance_custom_field_value AS b ON b.fieldid=a.id AND $user_or_project=".$db->quote($user_project_id)." "
				. "INNER JOIN #__jblance_user AS c ON c.user_id= b.userid "
				. "LEFT JOIN #__jblance_usergroup_field AS d ON c.ug_id = d.parent AND d.field_id = b.fieldid "
				. "WHERE b.fieldid =".$db->quote($fieldId);echo $query.'<br>'; */
		$query	= "SELECT b.*,a.field_type,a.value_type,a.value AS tblName FROM #__jblance_custom_field AS a ".
				  "INNER JOIN #__jblance_custom_field_value AS b ON b.fieldid=a.id AND ".$db->quoteName($user_or_project)."=".$db->quote($user_project_id)." ".
				  "WHERE b.fieldid =".$db->quote($fieldId);//echo $query.'<br>';

		$db->setQuery($query);
		$result	= $db->loadObject();

		if($db->getErrorNum()){
			JError::raiseError(500, $db->stderr());
		}

		//if the field is Database type, then join with the correspondng table to get the actual value.
		if($result->value_type == 'database'){
			$colName = selectHelper::getColumnName($result->tblName);
			$query = "SELECT ".$db->quoteName($colName)." FROM ".$db->quoteName($result->tblName)." ".
					 "WHERE id=".$db->quote($result->value)." AND published=1 ".
					 "ORDER BY ordering";
			$db->setQuery($query);
			$result->value = $db->loadResult();
		}

		//if the field is Database type, then join with the correspondng table to get the actual value.
		/* if($result->field_type == 'Textarea'){
			$result->value = $result->valuetext;
		} */

		return $result->value;
	}


	public function getUserGroupTypeFields($groupId){
		$app	= JFactory::getApplication();
		$db		= JFactory::getDbo();

		//get all the fields
		$query	= "SELECT * FROM #__jblance_custom_field ".
				  "WHERE published=1 AND field_for='profile' ".
				  "ORDER BY ordering";
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		//get the id for the usergroup
		$query	= "SELECT field_id FROM #__jblance_usergroup_field ".
				  "WHERE parent=".$db->quote($groupId);
		$db->setQuery($query);
		$filterIds	= $db->loadColumn();

		//if there is no field assigned for user group return null
		if(empty($filterIds))
			return array();

		$parents = $children = array();

		foreach($rows as $ct){
			if($ct->parent == 0){
				if(self::hasChild($ct->id, $filterIds))		//add if the parent has children, else not
					$parents[] = $ct;
			}
			else {
				if(in_array($ct->id, $filterIds))			//add if the children is available for the particular usergroup
					$children[] = $ct;
			}
		}
		$ordered = array();
		$data = array();

		if(count($parents)){
			foreach($parents as $pt){
				$ordered[] = $pt;
				foreach($children as $ct){
					if($ct->parent == $pt->id){
						$ordered[]= $ct;
					}
				}
			}
			$rows = $ordered;
		}

		return $rows;
	}

	function hasChild($parentId, $filterIds){
		$db 	= JFactory::getDbo();
		$query = "SELECT id FROM #__jblance_custom_field WHERE parent=".$db->quote($parentId);
		$db->setQuery($query);
		$childIds	= $db->loadColumn();

		if(array_intersect($childIds, $filterIds))
			return true;
	}
}
?>
