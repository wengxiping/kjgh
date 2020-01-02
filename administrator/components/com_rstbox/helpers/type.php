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
jimport('joomla.filesystem.folder');

/**
 *  Engage Box Type main class used by Engage Box plugins
 */
class EngageBoxType extends JPlugin
{
    /**
     *  Auto load plugin's language file
     *
     *  @var  boolean
     */
    protected $autoloadLanguage = true;

    /**
     *  Application Object
     *
     *  @var  object
     */
    protected $app;

    /**
     *  Returns Plugin's base path
     *
     *  @return  string
     */
    protected function getPath()
    {
        return JPATH_PLUGINS . "/engagebox/" . $this->name . "/";
    }

    /**
     *  Event ServiceName - Returns the service information
     *
     *  @return  array
     */
    public function onEngageBoxTypes(&$types)
    {
        $types[$this->name] = JText::_("PLG_ENGAGEBOX_" . strtoupper($this->name) . "_ALIAS");
    }

    /**
     *  Render plugin's output
     *
     *  @return  string
     */
    public function onEngageBoxTypeRender($box)
    {
        if ($box->boxtype !== $this->name)
        {
            return;
        }

        $layout = new JLayoutFile($this->name, $this->getPath() . "layout", array('debug' => false));
        return $layout->render($box);
    }

    /**
     *  Prepare form.
     *
     *  @param   JForm  $form  The form to be altered.
     *  @param   mixed  $data  The associated data for the form.
     *
     *  @return  boolean
     */
    public function onContentPrepareForm($form, $data)
    {
        // Return if we are in frontend or we don't have a valid form
        if ($this->app->isSite() || !($form instanceof JForm))
        {
            return true;
        }

        // Check we have a valid form context
        if ($form->getName() != "com_rstbox.item")
        {
            return true;
        }

        if (!isset($data->boxtype) || $data->boxtype != $this->name)
        {
            return true;
        }

        // Try to load form
        try
        {
            $form->loadFile($this->getForm(), false);
        }
        catch (Exception $e)
        {
            $this->app->enqueueMessage($e->getMessage(), 'error');
        }

        return true;
    }

    /**
     *  Get Plugin's form path
     *
     *  @return  string
     */
    protected function getForm()
    {
        $path = $this->getPath() . "/form/form.xml";

        if (JFile::exists($path))
        {
            return $path;
        }
    }
}

?>