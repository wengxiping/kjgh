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

use NRFramework\Cache;
use Joomla\Registry\Registry;

class EBHelper
{
    public static $prefix = "rstbox-";
    public static $assdir = "components/com_rstbox/assets/";

    /**
     *  Get Visitor ID
     *
     *  @return  string
     */
    public static function getVisitorID()
    {
        return NRFramework\VisitorToken::getInstance()->get();
    }

    public static function geoPluginNeedsUpdate()
    {
        // Check if TGeoIP plugin is enabled
        if (!NRFramework\Extension::pluginIsEnabled('tgeoip'))
        {
            return;
        }

        $plugin_path = JPATH_PLUGINS . '/system/tgeoip/';

        // Load plugin language (Needed by Joomla 4)
        JFactory::getLanguage()->load('plg_system_tgeoip', $plugin_path);

        // Load TGeoIP classes
        @include_once $plugin_path . 'vendor/autoload.php';
        @include_once $plugin_path . 'helper/tgeoip.php';

        if (!class_exists('TGeoIP'))
        {
            return;
        }
        
        // Check if database needs update. 
        $geo = new TGeoIP();
        if (!$geo->needsUpdate())
        {
            return;
        }

        // Database is too old and needs an update! Let's inform user.
        return true;
    }

    /**
     *  Returns all available box types
     *
     *  @return  array
     */
    public static function getBoxTypes()
    {
        JPluginHelper::importPlugin('engagebox');
        JFactory::getApplication()->triggerEvent('onEngageBoxTypes', array(&$types));

        asort($types);

        return $types;
    }

    /**
     *  Returns permissions
     *
     *  @return  object
     */
    public static function getActions()
    {
        $user = JFactory::getUser();
        $result = new JObject;
        $assetName = 'com_rstbox';

        $actions = array(
            'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.delete'
        );

        foreach ($actions as $action)
        {
            $result->set($action, $user->authorise($action, $assetName));
        }

        return $result;
    }

    public static function renderLayout($file, $displayData)
    {
        $layout = new JLayoutFile($file, null, array('debug' => false, 'client' => 1, 'component' => 'com_rstbox'));
        return $layout->render($displayData);
    }

    public static function boxRemoveCookie($id)
    {
        $cookie = "rstbox_" . md5(JPATH_SITE) . "_" . $id;
        JFactory::getApplication()->input->cookie->set($cookie, null, time() - 1, "/");
    }

    public static function boxHasCookie($id)
    {
        if (!$id)
        {
            return;
        }

        $cookie = "rstbox_" . md5(JPATH_SITE) . "_" . $id;
        $cookieValue = JFactory::getApplication()->input->cookie->get($cookie);

        if ($cookieValue)
        {
            return true;
        }

        return false;
    }

    public static function checkPublishingAssignments(&$boxes)
    {
        if (!$boxes)
        {
            return;
        }

        // Load Framework based publishing assignments
        $assignments = new NRFramework\Assignments();

        // Load local publishing assignments
        require_once(__DIR__ . "/assignments.php");

        foreach ($boxes as $key => $box)
        {
            $params = new Registry($box->params);

            // Prepare boxes that mirror other boxes assignments
            if ($params->get('mirror', false) && $params->get('mirror_box'))
            {
                self::mirrorBoxAssignments($params, $params->get('mirror_box'));
                $box->params = $params->toString();
            }

            // Check local assignments
            $localAssignments = new ebAssignments($box, $params);
            $pass = $localAssignments->passAll();

            // If testmode is enabled disable the User Groups assignment
            if ($box->testmode)
            {
                $params->set("assign_usergroups", "0");
                $box->params = $params->toString();
            }

            // Check global assignments only if local assignments passed
            if ($pass)
            {
                $pass = $assignments->passAll($box, $params->get('assignmentMatchingMethod', 'and'));
            }  

            if (!$pass)
            {
                unset($boxes[$key]);
            }
        }
    }

    public static function mirrorBoxAssignments(&$params, $box_id)
    {   
        // Load $box_id
        if (!$box = self::getBox($box_id))
        {
            return;
        }

        // To prevent user frustration, we ignore the following assignments because they are not displayed in the Publishing Assignments.
        $params_to_ignore = [
            'assign_impressions',
            'assign_offline'
        ];

        // Gather params to merge
        foreach ($box as $param_key => $param_value)
        {
            if (strpos($param_key, 'assign') === false || in_array($param_key, $params_to_ignore))
            {
                continue;
            }

            $params[$param_key] = $param_value;
        }
    }

    public static function getBox($id)
    {
        $hash = md5('box_' . $id);

        if (Cache::has($hash))
        {
            return Cache::read($hash);
        }

        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_rstbox/' . 'models');
        JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_rstbox/' . 'tables');
        $model = JModelLegacy::getInstance('Item', 'RstboxModel', ['ignore_request' => true]);
        $box = $model->getItem($id);

        return Cache::set($hash, $box);
    }

    public static function getBoxes()
    {
        $user   = JFactory::getUser();
        $isRoot = $user->authorise('core.admin');
        $cParam = JComponentHelper::getParams('com_rstbox');

        // Get boxes from database
        $query = "select b.* from #__rstbox b ";
        $query .= "where b.published = 1 ";

        if (!$isRoot)
        { 
            $query .= " AND b.testmode=0"; 
        }

        $db = JFactory::getDBO();
        $db->setQuery($query);
        $boxes = $db->loadObjectList();

        // Run publishing assignment checks against all boxes
        self::checkPublishingAssignments($boxes);

        // If we don't have any valid boxes return
        if (!is_array($boxes))
        {
            return;
        }

        // Load EngageBox Plugins
        JPluginHelper::importPlugin('engagebox');

        foreach ($boxes as $key => $box)
        {
            $box->params = new Registry($box->params);
            $settings = new stdClass();

            $box->content = JFactory::getApplication()->triggerEvent('onEngageBoxTypeRender', array($box));
            $box->content = implode(" ", $box->content);

            /* Classes */
            $classes = array(
                "rstbox_".$box->position,
                "rstbox_".$box->boxtype,
                $box->params->get("classsuffix", ""),
                'eb-' . $box->params->get('mode', 'popup'),
                self::prefixClass($box->params->get("aligncontent")),
                $box->params->get("boxshadow", "1") != "none" ? "rstbox_shd_".$box->params->get("boxshadow", "1") : false,
                "form".ucfirst($box->params->get("formorient", "ver"))
            );

            /* CSS */
            $style = array(
                "max-width:".$box->params->get("width"),
                "height:".$box->params->get("height"),
                "background-color:".$box->params->get("backgroundcolor"),
                "color:".$box->params->get("textcolor"),
                "border:". $box->params->get("bordertype", "solid") . " " . $box->params->get("borderwidth", "15px") . " " . $box->params->get("bordercolor", "#000"),
                "border-radius:".$box->params->get("borderradius", "0px"),
                "padding:".$box->params->get("padding", "20px"),
                //"margin:".$box->params->get("margin", "0"),
                "z-index:".$box->params->get("zindex", "99999")
            );

            // Background Image
            if ($box->params->get("bgimage", false))
            {
                $bgImage = array(
                    "background-image: url(".JURI::root() . $box->params->get("bgimagefile").")",
                    "background-repeat:".strtolower($box->params->get("bgrepeat")),
                    "background-size:".strtolower($box->params->get("bgsize")),
                    "background-position:".strtolower($box->params->get("bgposition"))
                );

                $style = array_merge($style, $bgImage);
            }

            /* Background Overlay */
            if ($box->params->get("overlay", false)) 
            {
                $bgOverlay = array(
                    $box->params->get("overlay_color"),
                    $box->params->get("overlayclick")
                );

                $settings->overlay = implode(":", $bgOverlay);
            }

            /* Other Settings */
            $settings->delay             = $box->params->get("triggerdelay");
            $settings->transitionin      = $box->params->get("animationin", "rstbox.slideUpIn");
            $settings->transitionout     = $box->params->get("animationout", "rstbox.slideUpOut");
            $settings->duration          = $box->params->get("duration", "400");
            $settings->autohide          = $box->params->get("autohide", 0);
            $settings->closeopened       = $box->params->get("closeopened", false);
            $settings->preventpagescroll = $box->params->get("preventpagescroll", "2") == "2" ? $cParam->get("preventpagescroll", false) : $box->params->get("preventpagescroll");
            $settings->log               = is_null($box->params->get("stats", null)) ? $cParam->get("stats", 1) : $box->params->get("stats");
            $settings->testmode          = $box->testmode;
            $settings->autoclose         = ($box->params->get("autoclose", false) && $box->params->get("autoclosevalue") > 0) ? $box->params->get("autoclosevalue") : false;

            if ($box->triggermethod == "onclick") { 
                $settings->triggerelement = $box->params->get("triggerelement");
                $settings->triggerpreventdefault = $box->params->get("preventdefault", 0);
            }

            if ($box->triggermethod == 'userleave') {
                $settings->exittimer = $box->params->get('exittimer', 1000);
            }

            /* Box Trigger Attribute */
            $trigger = $box->triggermethod;

            if ($box->triggermethod == "pageheight")
            { 
                $trigger .= ":".$box->params->get("triggerpercentage"); 
            }

            if (in_array($box->triggermethod, array("element")))
            { 
                $trigger .= ":".$box->params->get("triggerelement"); 
            }

            if ($box->triggermethod == "elementHover") 
            { 
                $trigger .= ":".$box->params->get("triggerelement").":".$box->params->get("triggerdelay"); 
                $box->params->set("triggerdelay", 0);
            }

            $rtl = $box->params->get("rtl", "2") == "2" ? $cParam->get("rtl", false) : $box->params->get("rtl");

            if ($rtl)
            {
                $classes[] = "rstboxRTL";
            }

            // HTML Attributes
            $box->HTMLattributes = implode(" ",
                array(
                    'id="rstbox_'.$box->id.'"',
                    ($rtl) ? 'dir="rtl"' : "",
                    'class="rstbox '.implode(" ",$classes).'"',
                    'data-settings=\''.json_encode($settings).'\'',
                    'data-trigger="'.$trigger.'"',
                    'data-cookietype="'.$box->params->get("cookietype", "days").'"',
                    'data-cookietime="'.$box->cookie.'"',
                    'data-title="'.$box->name.'"',
                    'style="'.implode(";", $style).'"'
                )
            );

            JFactory::getApplication()->triggerEvent('onEngageBoxAfterRender', array(&$boxes[$key]));
        }

        return $boxes;
    }

    public static function prefixClass($classes)
    {
        if (empty($classes) || is_null($classes))
        {
            return;
        }

        $arr = $classes;

        if (!is_array($classes))
        {
            $arr = explode(" ", $classes);
        }

        foreach ($arr as $key => $value)
        {
            $arr[$key] = self::$prefix . $value;
        }

        return implode(" ", $arr);
    }

    /**
     *  Loads front-end and back-end component media files
     *
     *  @param   boolean  $front  [description]
     *
     *  @return  void
     */
    public static function loadassets($front = false)
    {
        $params = JComponentHelper::getParams('com_rstbox');
        
        if ($params->get("loadjQuery", true))
        {
            JHtml::_('jquery.framework');
        }

        // Frontend
        if ($front)
        {
            if ($params->get('loadCSS', true))
            {
                \JHtml::stylesheet('com_rstbox/engagebox.css', ['relative' => true, 'version' => 'auto']);
            }

            if ($params->get('loadVelocity', true))
            {
                \JHtml::script('com_rstbox/vendor/velocity.js', ['relative' => true, 'version' => 'auto']);
                \JHtml::script('com_rstbox/vendor/velocity.ui.js', ['relative' => true, 'version' => 'auto']);
            }

            \JHtml::script('com_rstbox/engagebox.js', ['relative' => true, 'version' => 'auto']);

            return;
        }

        // Backend
        $version = NRFramework\Functions::getExtensionVersion("com_rstbox");
        $path    = JURI::root(true)."/administrator/" . self::$assdir;
        JFactory::getDocument()->addStyleSheet($path.'css/styles.css?v=' . $version);
    }

    public static function loadBoxes()
    {
        $hash = 'boxes';

        if (Cache::has($hash))
        {
            return Cache::read($hash);
        }

        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_rstbox/' . 'models');
        JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_rstbox/' . 'tables');
        $model = JModelLegacy::getInstance('Items', 'RstboxModel', ['ignore_request' => true]);
        $result = $model->getItems();

        return Cache::set($hash, $result);
    }

    public static function boxIsMirrored($id)
    {
        $boxes = self::loadBoxes();

        foreach ($boxes as $key => $box)
        {
            if (!isset($box->params->mirror) || !isset($box->params->mirror_box))
            {
                continue;
            }

            if ($box->params->mirror && $box->params->mirror_box == $id)
            {
                return true;
            }
        }

        return false;
    }
}