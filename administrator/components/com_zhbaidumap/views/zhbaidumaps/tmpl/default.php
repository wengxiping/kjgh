<?php
/*------------------------------------------------------------------------
# com_zhbaidumap - Zh BaiduMap
# ------------------------------------------------------------------------
# author:    Dmitry Zhuk
# copyright: Copyright (C) 2011 zhuk.cc. All Rights Reserved.
# license:   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
# website:   http://zhuk.cc
# Technical Support Forum: http://forum.zhuk.cc/
-------------------------------------------------------------------------*/
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

    // Load for installer translation
    $currentLanguage = JFactory::getLanguage();
    $currentLangTag = $currentLanguage->getTag();

    $currentLanguage->load('com_installer', JPATH_ADMINISTRATOR, $currentLangTag, true);	

    foreach($this->extList as $i => $item) {

        $path = $item->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE;
        
        switch ($item->type)
        {
                case 'component':
                        $extension = $item->element;
                        $source = JPATH_ADMINISTRATOR . '/components/' . $extension;
                                $lang->load("$extension.sys", JPATH_ADMINISTRATOR, null, false, true)
                        ||	$lang->load("$extension.sys", $source, null, false, true);
                break;
                case 'file':
                        $extension = 'files_' . $item->element;
                                $lang->load("$extension.sys", JPATH_SITE, null, false, true);
                break;
                case 'library':
                        $extension = 'lib_' . $item->element;
                                $lang->load("$extension.sys", JPATH_SITE, null, false, true);
                break;
                case 'module':
                        $extension = $item->element;
                        $source = $path . '/modules/' . $extension;
                                $lang->load("$extension.sys", $path, null, false, true)
                        ||	$lang->load("$extension.sys", $source, null, false, true);
                break;
                case 'plugin':
                        $extension = 'plg_' . $item->folder . '_' . $item->element;
                        $source = JPATH_PLUGINS . '/' . $item->folder . '/' . $item->element;
                                $lang->load("$extension.sys", JPATH_ADMINISTRATOR, null, false, true)
                        ||	$lang->load("$extension.sys", $source, null, false, true);
                break;
                case 'template':
                        $extension = 'tpl_' . $item->element;
                        $source = $path . '/templates/' . $item->element;
                                $lang->load("$extension.sys", $path, null, false, true)
                        ||	$lang->load("$extension.sys", $source, null, false, true);
                break;
                case 'package':
                default:
                        $extension = $item->element;
                                $lang->load("$extension.sys", JPATH_SITE, null, false, true);
                break;
        } 

    }

    $document	= JFactory::getDocument();
    $document->addStyleSheet(JURI::root() .'administrator/components/com_zhbaidumap/assets/css/utils.css');
    
    $imgpath = JURI::root() .'administrator/components/com_zhbaidumap/assets/icons/';
    $utilspath = JURI::root() .'administrator/components/com_zhbaidumap/assets/utils/';

?>

<?php if(!empty( $this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>

<div class="row-fluid">
    <div class="span7">    

        <div class="well well-small">
            <h2 class="module-title nav-header"><?php echo JText::_('COM_ZHBAIDUMAP_DASHBOARD_MAIN'); ?></h2>
            <div class="row-striped">
                <div class="row-fluid">
                    
                <div class="zhbdm-panel-icon-wrapper">
                    <div class="zhbdm-panel-icon">
                        <a href="<?php echo JRoute::_('index.php?option=com_zhbaidumap&amp;view=mapmaps'); ?>"> 
                            <img src="<?php echo $utilspath ?>img_world_3d.png" title="<?php echo JText::_('COM_ZHBAIDUMAP_SUBMENU_MAPS'); ?>" alt="<?php echo JText::_('COM_ZHBAIDUMAP_SUBMENU_MAPS'); ?>"> 
                            <span><?php echo JText::_('COM_ZHBAIDUMAP_SUBMENU_MAPS'); ?></span> 
                        </a>
                    </div>        
                </div>   

                <div class="zhbdm-panel-icon-wrapper">
                    <div class="zhbdm-panel-icon">
                        <a href="<?php echo JRoute::_('index.php?option=com_zhbaidumap&amp;view=mapmarkers'); ?>"> 
                            <img src="<?php echo $utilspath ?>img_placemark.png" title="<?php echo JText::_('COM_ZHBAIDUMAP_SUBMENU_MAPMARKERS'); ?>" alt="<?php echo JText::_('COM_ZHBAIDUMAP_SUBMENU_MAPMARKERS'); ?>"> 
                            <span><?php echo JText::_('COM_ZHBAIDUMAP_SUBMENU_MAPMARKERS'); ?></span> 
                        </a>
                    </div>        
                </div>   
                    

                <div class="zhbdm-panel-icon-wrapper">
                    <div class="zhbdm-panel-icon">
                        <a href="<?php echo JRoute::_('index.php?option=com_zhbaidumap&amp;view=mapmarkergroups'); ?>"> 
                            <img src="<?php echo $utilspath ?>img_tag.png" title="<?php echo JText::_('COM_ZHBAIDUMAP_SUBMENU_MAPMARKERGROUPS'); ?>" alt="<?php echo JText::_('COM_ZHBAIDUMAP_SUBMENU_MAPMARKERGROUPS'); ?>"> 
                            <span><?php echo JText::_('COM_ZHBAIDUMAP_SUBMENU_MAPMARKERGROUPS'); ?></span> 
                        </a>
                    </div>        
                </div>   


                <div class="zhbdm-panel-icon-wrapper">
                    <div class="zhbdm-panel-icon">
                        <a href="<?php echo JRoute::_('index.php?option=com_zhbaidumap&amp;view=mappaths'); ?>"> 
                            <img src="<?php echo $utilspath ?>img_path.png" title="<?php echo JText::_('COM_ZHBAIDUMAP_SUBMENU_MAPPATHS'); ?>" alt="<?php echo JText::_('COM_ZHBAIDUMAP_SUBMENU_MAPPATHS'); ?>"> 
                            <span><?php echo JText::_('COM_ZHBAIDUMAP_SUBMENU_MAPPATHS'); ?></span> 
                        </a>
                    </div>        
                </div>   
                    

                <div class="zhbdm-panel-icon-wrapper">
                    <div class="zhbdm-panel-icon">
                        <a href="<?php echo JRoute::_('index.php?option=com_categories&amp;extension=com_zhbaidumap&amp;view=categories'); ?>"> 
                            <img src="<?php echo $utilspath ?>img_category.png" title="<?php echo JText::_('COM_ZHBAIDUMAP_SUBMENU_CATEGORIES'); ?>" alt="<?php echo JText::_('COM_ZHBAIDUMAP_SUBMENU_CATEGORIES'); ?>"> 
                            <span><?php echo JText::_('COM_ZHBAIDUMAP_SUBMENU_CATEGORIES'); ?></span> 
                        </a>
                    </div>        
                </div>   
                                   

                <div class="zhbdm-panel-icon-wrapper">
                    <div class="zhbdm-panel-icon">
                        <a href="<?php echo JRoute::_('index.php?option=com_zhbaidumap&amp;view=abouts'); ?>"> 
                            <img src="<?php echo $utilspath ?>img_about.png" title="<?php echo JText::_('COM_ZHBAIDUMAP_SUBMENU_ABOUT'); ?>" alt="<?php echo JText::_('COM_ZHBAIDUMAP_SUBMENU_ABOUT'); ?>"> 
                            <span><?php echo JText::_('COM_ZHBAIDUMAP_SUBMENU_ABOUT'); ?></span> 
                        </a>
                    </div>        
                </div>   
                    
                </div>            
            </div>        
        </div>
    
        <div class="well well-small">
            <h2 class="module-title nav-header"><?php echo JText::_('COM_ZHBAIDUMAP_DASHBOARD_SUPPORT'); ?></h2>
            <p class="zhbdm-panel-comment"><?php echo JText::_('COM_ZHBAIDUMAP_DASHBOARD_SUPPORT_COMMENT'); ?></p>
            <div>
                <div class="row-fluid"> 
                    <div>
                        <ul class="zhbdm-panel-ul">
                            <li><i class="icon icon-question"></i><a href="http://forum.zhuk.cc/index.php/zh-baidumap" target="_blank"><?php echo JText::_('COM_ZHBAIDUMAP_DASHBOARD_SUPPORT_FORUM'); ?></a></li>
                            <li><i class="icon icon-book"></i><a href="http://wiki.zhuk.cc/index.php/Zh_BaiduMap" target="_blank"><?php echo JText::_('COM_ZHBAIDUMAP_DASHBOARD_SUPPORT_DOC'); ?></a></li>
                        </ul>
                    </div>
                </div>
            </div>        
        </div>
        
        <div class="well well-small">
            <h2 class="module-title nav-header"><?php echo JText::_('COM_ZHBAIDUMAP_DASHBOARD_FEEDBACK'); ?></h2>
            <p class="zhbdm-panel-comment"><?php echo JText::_('COM_ZHBAIDUMAP_DASHBOARD_FEEDBACK_COMMENT'); ?></p>
            <div>
                <div class="row-fluid"> 
                    <div>
                        <ul class="zhbdm-panel-ul">
                            <li><i class="icon icon-thumbs-up"></i><a href="https://extensions.joomla.org/extensions/extension/maps-a-weather/maps-a-locations/zh-baidumap/" target="_blank"><?php echo JText::_('COM_ZHBAIDUMAP_DASHBOARD_FEEDBACK_RATE'); ?></a></li>
                        </ul>
                    </div>
                </div>
            </div>  
            
            <div class="row-fluid"> 
                <div>
                    <ul class="zhbdm-panel-ul">
                        <li><i class="icon icon-loop"></i><a href="https://www.transifex.com/dmitryzhuk/zh-baidumap/dashboard/" target="_blank"><?php echo JText::_('COM_ZHBAIDUMAP_DASHBOARD_TRANSLATE'); ?></a></li>
                    </ul>
                </div>
            </div>
            
        </div>                
    </div>


    <div class="span5">
        <div class="well well-small">
            <h2 class="module-title nav-header"><?php echo JText::_('COM_ZHBAIDUMAP_DASHBOARD_INFO'); ?></h2>
            <div>
                <div class="row-fluid"> 
                    <div><img class="zhbdm-panel-image" src="<?php echo $utilspath ?>img_main_bdm.png" title="<?php echo JText::_('COM_ZHBAIDUMAP_DASHBOARD_INFO'); ?>" alt="<?php echo JText::_('COM_ZHBAIDUMAP_DASHBOARD_INFO'); ?>"> 
                    </div>
                    <div class="dl-horizontal">    
                        <hr class="hr-condensed">
                        <dt><?php echo JText::_('COM_ZHBAIDUMAP_DASHBOARD_JED_DOWNLOAD'); ?></dt>
                        <dd><a href="https://extensions.joomla.org/extensions/extension/maps-a-weather/maps-a-locations/zh-baidumap/" target="_blank"><?php echo JText::_('COM_ZHBAIDUMAP_DASHBOARD_LAST_VERSION'); ?></a></dd>
                        <hr class="hr-condensed">
                        <dt>&nbsp;</dt>
                        <dd><a href="http://joomla.zhuk.cc/index.php/zhbaidumap-main" target="_blank"><?php echo JText::_('COM_ZHBAIDUMAP_DASHBOARD_INFO_DEMO'); ?></a></dd>
                        <hr class="hr-condensed">
                        <dt><?php echo JText::_('COM_ZHBAIDUMAP_DASHBOARD_INFO_VERSION'); ?></dt>
                        <dd>&nbsp;</dd><?php
                        foreach($this->extList as $i => $item) {
                          echo "<dt class=\"zhbdm-dt\">" . JText::_('COM_INSTALLER_TYPE_' . strtoupper($item->type)) . "</dt>";   
                          $manifest = json_decode($item->manifest_cache, true);
                          echo "<dd>&nbsp;";   
                          echo $manifest['version'] . "&nbsp;&nbsp;";   
                          if ((int)$item->enabled == 1)
                          {
                            echo '<img src="'.$utilspath.'published1.png" title="'.JText::_("JSTATUS").'" alt="'.JText::_("JSTATUS").'">';
                          }
                          elseif ((int)$item->enabled == 0)
                          {
                            echo '<img src="'.$utilspath.'published0.png" title="'.JText::_("JSTATUS").'" alt="'.JText::_("JSTATUS").'">';
                          }
                          else 
                          {
                              echo JText::_("JSTATUS"). ": " . $item->enabled;
                          }
                          echo "&nbsp;&nbsp;" . JText::_($item->name);                          
                          echo "</dd>";   
                        }
                        ?>
                        </dd>
                        <hr class="hr-condensed">
                        <dt><?php echo JText::_('COM_ZHBAIDUMAP_DASHBOARD_INFO_AUTHOR'); ?></dt>
                        <dd><a href="http://zhuk.cc" target="_blank">Dmitry Zhuk</a></dd>
                        <hr class="hr-condensed">
                        <dt><?php echo JText::_('COM_ZHBAIDUMAP_DASHBOARD_INFO_COPYRIGHT'); ?></dt>
                        <dd><a href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GNU GPLv2 or later</a><br /><br /><?php echo JText::_("COM_ZHBAIDUMAP_DASHBOARD_OTHER_LICENSE"); ?></dd>
                        <hr class="hr-condensed">
                         <dt><?php echo JText::_('COM_ZHBAIDUMAP_DASHBOARD_INFO_DONATE'); ?></dt>
                        <dd><p><a href="http://joomla.zhuk.cc/index.php/donate" target="_blank"><img src="<?php echo $utilspath ?>btn_donate_CC_LG.gif" alt="Donate" width="147" height="47" /></a></p></dd>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>

</div>