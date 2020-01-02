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

class JFormFieldNR_Well extends NRFormField
{
    /**
     * The field type.
     *
     * @var string
     */
    public $type = 'nr_well';

    /**
     * Layout to render the form field
     *
     * @var  string
     */
    protected $renderLayout = 'well';

    /**
     * Override renderer include path
     *
     * @return  array
     */
    protected function getLayoutPaths()
    {
        return JPATH_PLUGINS . '/system/nrframework/layouts/';
    }

    /**
     *  Method to render the input field
     *
     *  @return  string  
     */
    protected function getInput()
    {   
        JHtml::stylesheet('plg_system_nrframework/fields.css', false, true);

        $title       = $this->get('label');
        $description = $this->get('description');
        $url         = $this->get('url');
        $class       = $this->get('class');
        $start       = $this->get('start', 0);
        $end         = $this->get('end', 0);
        $info        = $this->get("html", null);

        if ($info)
        {
            $info = str_replace("{{", "<", $info);
            $info = str_replace("}}", ">", $info);
        }

        $html = array();

        if ($start || !$end)
        {
            if ($title)
            {
                $html[] = '<h4>' . $this->prepareText($title) . '</h4>';
            }
            if ($description)
            {
                $html[] = '<div class="well-desc">' . $this->prepareText($description) . $info . '</div>';
            }

            if ($url)
            {
                $html[] = '
                    <a class="btn btn-secondary wellbtn" target="_blank" href="' . $url . '">
                        <span class="icon-info"></span>
                    </a>
                ';
            }
        }

        if ($end) {
            $html[] = '</div>';
        }

        return implode('', $html);
    }

    /**
     * Method to get a control group with label and input.
     *
     * @param   array  $options  Options to be passed into the rendering of the field
     *
     * @return  string  A string containing the html for the control group
     *
     * @since   3.2
     */
    public function renderField($options = array())
    {
        // Return on Joomla versions => 3.5
        if ((version_compare(JVERSION, '3.5.0', '>=')) || (method_exists(get_parent_class(),'getLayoutPaths')))
        {
            return parent::renderField($options);
        }

        if ($this->hidden)
        {
            return $this->getInput();
        }
        if (!isset($options['class']))
        {
            $options['class'] = '';
        }
        $options['rel'] = '';
        if (empty($options['hiddenLabel']) && $this->getAttribute('hiddenLabel'))
        {
            $options['hiddenLabel'] = true;
        }
        if ($showonstring = $this->getAttribute('showon'))
        {
            $showonarr = array();
            foreach (preg_split('%\[AND\]|\[OR\]%', $showonstring) as $showonfield)
            {
                $showon   = explode(':', $showonfield, 2);
                $showonarr[] = array(
                    'field'  => str_replace('[]', '', $this->getName($showon[0])),
                    'values' => explode(',', $showon[1]),
                    'op'     => (preg_match('%\[(AND|OR)\]' . $showonfield . '%', $showonstring, $matches)) ? $matches[1] : '',
                );
            }
            $options['rel'] = ' data-showon=\'' . json_encode($showonarr) . '\'';
            $options['showonEnabled'] = true;
        }
        $data = array(
            'input'   => $this->getInput(),
            'label'   => $this->getLabel(),
            'options' => $options,
        );

        $layout = new JLayoutFile($this->renderLayout, $this->getLayoutPaths());
        return $layout->render($data);
    }
}