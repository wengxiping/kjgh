<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla Platform.
 * Supports a generic list of options.
 *
 * @since  1.7.0
 */
class JFormFieldMenuCategoryList extends JFormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $type = 'MenuCategoryList';

    /**
     * Method to get the field input markup for a generic list.
     * Use the multiple attribute to enable multiselect.
     *
     * @return  string  The field input markup.
     *
     * @since   3.7.0
     */
    protected function getInput()
    {
        // dump($this->form->getData('id'));
        // die;

//        $db = JFactory::getDbo();
//        $query = $db->getQuery(true)
//            ->select('*')
//            ->from('#__limit_category as a')->where("a.menu_id = 0");
//
//        $limit_items = $db->setQuery($query)->loadObjectList();
//        $category = array();
//        foreach ($limit_items as $val) {
//            array_push($category, $val->category_id);
//        }
//        $query = $db->getQuery(true)
//            ->select('*')
//            ->from('#__jblance_category as a')->where('a.parent = 0');
//
//        $items = $db->setQuery($query)->loadObjectList();

        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__jblance_category as a')->order('ordering asc')->where('a.parent = 0');

        $items = $db->setQuery($query)->loadObjectList();
        $category = explode(",", $this->form->getData()->get('categorylist'));

        $html = "<div class='editCategory'>";
        foreach ($items as $key => $val) {
            $persentIsChechedText = in_array($val->id, $category) ? "checked" : "";
            $html .= "<div class='parent-category'>";
            $html .= "<div class='parent-label'><label class='checkboxParent'><input type='checkbox' . $persentIsChechedText . name=" . $this->name . "[]" . " value=" . $val->id . " class='parent-ipt'><span class='parent-font'>" . $val->category . "</span></label></div>";
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__jblance_category as a')->order('ordering asc')->where('a.parent = ' . $val->id);

            $category_items = $db->setQuery($query)->loadObjectList();
            $html .= "<div class='small-box'>";
            foreach ($category_items as $_key => $_val) {
                $childrenIsChechedText = in_array($_val->id, $category) ? "checked" : "";
                $html .= "<div class='small-category'>";
                $html .= "<div class='small-label'><label class='checkboxSmall'><input type='checkbox' . $childrenIsChechedText . name=" . $this->name . "[]" . " value=" . $_val->id . " class='small-ipt'><span class='small-font'>" . $_val->category . "</span></label></div>";
                $html .= "</div>";
            }
            $html .= '</div>';
            $html .= "</div>";
        }
        $html .= "</div>";
        $html .= <<<EOF
<script>
    jQuery(document).ready(function ($) {
        $("#jform_category_chzn").change(function () {
            if ($(this).val() == 2) {
                $("#category_lists").css('display', 'block');
            } else if ($(this).val() == 1) {
                $("#category_lists").css('display', 'none');
            }
            console.log($(this).val());
        })
        $(".checkboxParent input[type=checkbox]").on('click', function () {
            console.log('checked', $(this).is(":checked"));
            if (!$(this).is(":checked")) {
                $(this).parent().parent().parent().find(".small-box input[type=checkbox]").attr('checked', false);
            } else {
                $(this).parent().parent().parent().find(".small-box input[type=checkbox]").attr('checked', true);
            }
        })
        $(".checkboxSmall input[type=checkbox]").on('click', function () {
            var _len = $(this).parents('.small-box').children('.small-category').length;
            var _checked_len = $(this).parents('.small-box').find(".small-category input[type=checkbox]:checked").length;
            if (_len === _checked_len) {
                $(this).parents(".small-box").parent().find(".parent-label input[type=checkbox]").attr('checked', true);
            }
            if (!$(this).is(":checked")) {
                $(this).parent().parent().parent().parent().parent().find("div[class=parent-label] input[type=checkbox]").attr('checked', false)
            }
        });
    });
</script>
<style>
    .editCategory {
        width: 100%;
        height: 300px;
        overflow-y: scroll;
    }

    .parent-font {
        font-size: 18px;
        font-weight: 300;
        margin-left: 10px;
    }

    .parent-category .parent-label {
        height: 40px;
        display: flex;
        justify-content: flex-start;
        align-items: center;
        border-bottom: 2px solid #F0F0F0;
    }

    .parent-category .parent-label .parent-ipt {
        margin: 0 !important;
    }

    .parent-category .small-box {
        width: 100%;
        display: flex;
        justify-content: flex-start;
        align-items: center;
        flex-wrap: wrap;
        justify-items: center;
    }

    .parent-category .small-box .small-category {
        display: flex;
        justify-content: flex-start;
        align-items: center;
    }

    .parent-category .small-box .small-label {
        padding: 10px 0 5px 10px;
    }

    .parent-category .small-label .small-font {
        margin-left: 3px;
    }

    .parent-category .small-box .small-label .small-ipt {
        margin: 0 !important;
    }

</style>
EOF;

        return $html;
    }

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     *
     * @since   3.7.0
     */
    protected function getOptions()
    {
        $fieldname = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname);
        $options = array();

        foreach ($this->element->xpath('option') as $option) {
            // Filter requirements
            if ($requires = explode(',', (string)$option['requires'])) {
                // Requires multilanguage
                if (in_array('multilanguage', $requires) && !JLanguageMultilang::isEnabled()) {
                    continue;
                }

                // Requires associations
                if (in_array('associations', $requires) && !JLanguageAssociations::isEnabled()) {
                    continue;
                }

                // Requires adminlanguage
                if (in_array('adminlanguage', $requires) && !JModuleHelper::isAdminMultilang()) {
                    continue;
                }

                // Requires vote plugin
                if (in_array('vote', $requires) && !JPluginHelper::isEnabled('content', 'vote')) {
                    continue;
                }
            }

            $value = (string)$option['value'];
            $text = trim((string)$option) != '' ? trim((string)$option) : $value;

            $disabled = (string)$option['disabled'];
            $disabled = ($disabled == 'true' || $disabled == 'disabled' || $disabled == '1');
            $disabled = $disabled || ($this->readonly && $value != $this->value);

            $checked = (string)$option['checked'];
            $checked = ($checked == 'true' || $checked == 'checked' || $checked == '1');

            $selected = (string)$option['selected'];
            $selected = ($selected == 'true' || $selected == 'selected' || $selected == '1');

            $tmp = array(
                'value' => $value,
                'text' => JText::alt($text, $fieldname),
                'disable' => $disabled,
                'class' => (string)$option['class'],
                'selected' => ($checked || $selected),
                'checked' => ($checked || $selected),
            );

            // Set some event handler attributes. But really, should be using unobtrusive js.
            $tmp['onclick'] = (string)$option['onclick'];
            $tmp['onchange'] = (string)$option['onchange'];

            if ((string)$option['showon']) {
                $tmp['optionattr'] = " data-showon='" .
                    json_encode(
                        JFormHelper::parseShowOnConditions((string)$option['showon'], $this->formControl, $this->group)
                    )
                    . "'";
            }
            // Add the option object to the result set.
            $options[] = (object)$tmp;
        }

        if ($this->element['useglobal']) {
            $tmp = new stdClass;
            $tmp->value = '';
            $tmp->text = JText::_('JGLOBAL_USE_GLOBAL');
            $component = JFactory::getApplication()->input->getCmd('option');

            // Get correct component for menu items
            if ($component == 'com_menus') {
                $link = $this->form->getData()->get('link');
                $uri = new JUri($link);
                $component = $uri->getVar('option', 'com_menus');
            }

            $params = JComponentHelper::getParams($component);
            $value = $params->get($this->fieldname);

            // Try with global configuration
            if (is_null($value)) {
                $value = JFactory::getConfig()->get($this->fieldname);
            }

            // Try with menu configuration
            if (is_null($value) && JFactory::getApplication()->input->getCmd('option') == 'com_menus') {
                $value = JComponentHelper::getParams('com_menus')->get($this->fieldname);
            }

            if (!is_null($value)) {
                $value = (string)$value;

                foreach ($options as $option) {
                    if ($option->value === $value) {
                        $value = $option->text;

                        break;
                    }
                }

                $tmp->text = JText::sprintf('JGLOBAL_USE_GLOBAL_VALUE', $value);
            }

            array_unshift($options, $tmp);
        }

        reset($options);

        return $options;
    }

    /**
     * Method to add an option to the list field.
     *
     * @param string $text Text/Language variable of the option.
     * @param array $attributes Array of attributes ('name' => 'value' format)
     *
     * @return  JFormFieldList  For chaining.
     *
     * @since   3.7.0
     */
    public function addOption($text, $attributes = array())
    {
        if ($text && $this->element instanceof SimpleXMLElement) {
            $child = $this->element->addChild('option', $text);

            foreach ($attributes as $name => $value) {
                $child->addAttribute($name, $value);
            }
        }

        return $this;
    }

    /**
     * Method to get certain otherwise inaccessible properties from the form field object.
     *
     * @param string $name The property name for which to get the value.
     *
     * @return  mixed  The property value or null.
     *
     * @since   3.7.0
     */
    public function __get($name)
    {
        if ($name == 'options') {
            return $this->getOptions();
        }

        return parent::__get($name);
    }
}
