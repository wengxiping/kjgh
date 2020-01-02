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

defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.file');

/**
 *  EngageBox Render Plugin
 */
class PlgSystemRstBox extends JPlugin
{
    /**
     *  Application Object
     *
     *  @var  object
     */
    protected $app;

    /**
     *  Boxes final HTML layout
     *
     *  @var  string
     */
    private $boxes;

    /**
     *  Component's param object
     *
     *  @var  JRegistry
     */
    private $param;

    /**
     *  The loaded indicator of helper
     *
     *  @var  boolean
     */
    private $init;

    /**
     *  List of valid AJAX tasks
     *
     *  @var  array
     */
    private $validAJAXTasks = array(
        "track"
    );

    /**
     *  Log Object
     *
     *  @var  Object
     */ 
    private $log;

    /**
     *  onAfterDispatch Event
     */
    public function onAfterDispatch()
    {
        // Get Helper
        if (!$this->getHelper())
        {
            return;
        }

        // Fetch all boxes
        if (!$boxes = EBHelper::getBoxes())
        {
            return;
        }

        if (!$this->param->get("forceloadmedia", false))
        {
            EBHelper::loadassets(true);
        }

        /* Prepare HTML */
        $html = EBHelper::renderLayout("rstbox", $boxes);

        if ($this->param->get("preparecontent", true))
        {
            $html = JHtml::_('content.prepare', $html);
        }

        $this->boxes = $html;
    }

    /**
     *  This hook tries to fix the showOn bug appeared in Joomla 3.7 (Fixed in Joomla 3.8.1)
     *  https://github.com/joomla/joomla-cms/pull/14007
     *
     *  Multiple showOn conditions appeared in Joomla 3.5.0
     *  https://github.com/joomla/joomla-cms/pull/8524
     *
     *  @return  bool
     */
    private function fixShowOn()
    {
        // Check we are on the right context.
        if ($this->app->isSite() || $this->app->input->get('option') != 'com_rstbox' || $this->app->input->get('view') != 'item')
        {
            return;
        }

        $replaces = [];
       
        // The code block below fixes an issue with not visible fields in the Close Button section and the YesNo box type.
        // The cause of the issue is the missing showOn [AND] operator on Joomla < 3.5.0. 
        // The best we can do, is to remove the 2nd showOn condition and make the fields always visible.
        if (version_compare(JVERSION, '3.5.0', '<'))
        {
            $replaces = array_merge($replaces, array(
                'showon_icon[AND]hide:0' => 'showon_icon',
                'showon_open[AND]show:1' => 'showon_open',
                'showon_url[AND]show:1'  => 'showon_url'
            ));
        } 

        // Let's fix the showOn bug appeared in Joomla 3.7.0.
        // Update: Fix restored in Joomla 3.8.1
        if (version_compare(JVERSION, '3.7', '>') && version_compare(JVERSION, '3.8.1', '<'))
        {
            $replaces = array_merge($replaces, array(
                'jform[yesno.no]'  => 'jform[yesno][no]',
                'jform[yesno.yes]' => 'jform[yesno][yes]'
            ));
        }
        
        // Make sure we have replacments items
        if (count($replaces) == 0)
        {
            return;
        }

        // Let's do the replacements
        $buffer = str_replace(array_keys($replaces), array_values($replaces), $this->app->getBody());
        $this->app->setBody($buffer);
    }

    /**
     *  Listening to the onAfterRender event in order to append the boxes to the document
     */
    public function onAfterRender() 
    {
        $this->fixShowOn();

        // Get Helper
        if (!$this->getHelper())
        {
            return;
        }

        // Break if no boxes found
        if (!$html = $this->boxes)
        {
            return;
        }

        // Prepare replacements
        $buffer = $this->app->getBody();
        $closingTag = "</body>";

        if (strpos($buffer, $closingTag))
        {
            // If </body> exists prepend the box HTML
            $buffer = str_replace($closingTag, $html . $closingTag, $buffer);
        } else 
        {
            // If </body> does not exist append to document's end
            $buffer .= $html;
        }
        
        // Set body's final layout
        $this->app->setBody($buffer);
    }

    /**
     *  Method to handle AJAX requests.
     *  If not passed a valid token the request will abort.
     *  
     *  Listening on URL: ?option=com_ajax&format=raw&plugin=rstbox&task=track
     *
     *  @return  JSON result formated in JSON
     */
    public function onAjaxRstbox()
    {
        JSession::checkToken("request") or die('Invalid Token');

        require_once(JPATH_ADMINISTRATOR . '/components/com_rstbox/helpers/helper.php');

        // Check if a valid task passed
        $task = $this->app->input->get('task', null);

        if (is_null($task) || !in_array($task, $this->validAJAXTasks))
        {
            return;
        }

        // Result object
        $info = new stdClass();
        $info->status = false;

        // Task Track
        if ($task == "track")
        {
            // Initializes Logger
            $logger = JPATH_ADMINISTRATOR . '/components/com_rstbox/helpers/log.php';

            if (!JFile::exists($logger) || !include_once($logger))
            {
                return;
            }

            $this->log = new eBoxlog();

            $boxid   = $this->app->input->get('box', null, 'INT');
            $eventid = $this->app->input->get('event', 1, 'INT');

            // Track event
            $result = $this->log->track($boxid, $eventid);

            $info->status = $result;
            $info->box = $boxid;
            $info->eventid = $eventid;

            // Housekeeping
            $this->log->clean();
        }

        echo json_encode($info);
    }

    /**
     *  Loads the helper classes of plugin
     *
     *  @return  bool
     */
    private function getHelper()
    {
        // Return if is helper is already loaded
        if ($this->init)    
        {
            return true;
        }

        // Return if we are not in frontend
        if (!$this->app->isSite())
        {
            return false;
        }

        // Return if compnent is not enabled
        $component = JComponentHelper::getComponent('com_rstbox', true);

        if (!$component->enabled)
        {   
            return;
        }

        $this->param = $component->params;

        // Handle the component execution when the tmpl request paramter is overriden
        if (!$this->param->get("executeoutputoverride", false) && $this->app->input->get('tmpl', null, "cmd") != null)
        {
            return false;
        }

        // Handle the component execution when the format request paramter is overriden
        if (!$this->param->get("executeonformat", false) && $this->app->input->get('format', "html", "cmd") != "html")
        {
            return false;
        }

        // Load Novarain Framework
        if (!@include_once(JPATH_PLUGINS . '/system/nrframework/autoload.php'))
        {
            return false;
        }
        
        // Return if document type is Feed
        if (NRFramework\Functions::isFeed())
        {
            return false;
        }

        // Load component's helper file
        require_once JPATH_ADMINISTRATOR . '/components/com_rstbox/helpers/helper.php';

        return ($this->init = true);
    }
}
