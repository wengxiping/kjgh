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
// No direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');

// Map Types
$maptypes = $this->mapTypeList;
$apikey = $this->mapapikey;
$urlProtocol = "http";
if ($this->httpsprotocol != "")
{
	if ((int)$this->httpsprotocol == 0)
	{
		$urlProtocol = 'https';
	}
}	

if ($this->map_height != "")
{
    if ((int)$this->map_height == 0)
    {
        $map_height = "420px";
        $map_height_wrap = "450px";
    }
    else
    {
        $map_height = ((int)$this->map_height - 30) . "px";
        $map_height_wrap = (int)$this->map_height . "px";
    }
}
else 
{
    $map_height = "420px";
    $map_height_wrap = "450px";
}

$mainScriptMiddle = "";

if ($this->mapapiversion != "")
{
	if ($mainScriptMiddle == "")
	{
		$mainScriptMiddle = 'v='.$this->mapapiversion;
	}
	else
	{
		$mainScriptMiddle .= '&v='.$this->mapapiversion;
	}

}

?>
<form action="<?php echo JRoute::_('index.php?option=com_zhbaidumap&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">

<div class="span12 form-horizontal">

<div class="tabbable">
    <ul class="nav nav-pills">
                <li class="active"><a href="#tab0" data-toggle="tab"><?php echo JText::_( 'COM_ZHBAIDUMAP_MAPPATH_PATH' ); ?></a></li>
		<li><a href="#tab1" data-toggle="tab"><?php echo JText::_( 'COM_ZHBAIDUMAP_MAPPATH_DETAIL' ); ?></a></li>
		<li><a href="#tab2" data-toggle="tab"><?php echo JText::_('COM_ZHBAIDUMAP_MAPPATH_PATHADVANCED'); ?></a></li>
		<!-- <li><a href="#tab3" data-toggle="tab"><?php /* echo JText::_('COM_ZHBAIDUMAP_MAPPATH_DETAIL_KMLLAYER_TITLE'); */ ?></a></li> -->
                <li><a href="#tab5" data-toggle="tab"><?php echo JText::_('COM_ZHBAIDUMAP_MAPPATH_DETAIL_IMGGROUND_TITLE'); ?></a></li>
		<?php
		$fieldSets = $this->form->getFieldsets('params');
		foreach ($fieldSets as $name => $fieldSet) :
		?>
		<li><a href="#params-<?php echo $name;?>" data-toggle="tab"><?php echo JText::_($fieldSet->label);?></a></li>
		<?php endforeach; ?>
    </ul>
</div>
<div class="tab-content">
    

		<div class="tab-pane active" id="tab0">
			<div>
				<fieldset class="adminform">
				
					<?php foreach($this->form->getFieldset('pathmain') as $field): 
						
						if ($field->id == 'jform_mapid')
						{
							?>
							<div class="control-label">
							<?php 
								echo $field->label;
							?>
							</div>
							<div class="controls">
							<?php 
								array_unshift($this->mapList, JHTML::_('select.option', '', JText::_( 'COM_ZHBAIDUMAP_MAPPATH_FILTER_MAP'), 'value', 'text')); 
								echo JHTML::_( 'select.genericlist', $this->mapList, 'jform[mapid]',  'class="inputbox span5 required" size="1"', 'value', 'text', (int)$this->item->mapid, 'jform_mapid');
								//echo $field->label;
								//echo $field->input;
							?>
							</div>
							<?php 
						}
						else
						{?>
						<div class="control-group">
						<?php 
							?>
							<div class="control-label">
							<?php 
								echo $field->label;
							?>
							</div>
							<div class="controls">
							<?php 
								echo $field->input;
							?>
							</div>
							<?php 
						?>
						</div>

						<?php }?>

					<?php endforeach; ?>
				
				</fieldset>
			
				<div>


                                            <div id="mapDivWrapper" class="row-fluid" style="margin:0;padding:0;width:100%;height:<?php echo $map_height_wrap ?>">

                                            <div id="BDMapsID" class="row-fluid" style="margin:0;padding:0;width:100%;height:<?php echo $map_height ?>">

                                                  
                                            <?php 

                                            $document	= JFactory::getDocument();

                                            $document->addStyleSheet(JURI::root() .'administrator/components/com_zhbaidumap/assets/css/admin.css');

                                            $mapDefLat = $this->mapDefLat;
                                            $mapDefLng = $this->mapDefLng;


                                            $mapMapTypeBaidu = $this->mapMapTypeBaidu;
                                            $mapMapTypeOSM = $this->mapMapTypeOSM;
                                            $mapMapTypeCustom = $this->mapMapTypeCustom;


                                            //Script begin
                                            $scripttext = '<script type="text/javascript" >//<![CDATA[' ."\n";

                                                    $scripttext .= 'var initialLocation;' ."\n";
                                                    $scripttext .= 'var spblocation;' ."\n";
                                                    $scripttext .= 'var browserSupportFlag =  new Boolean();' ."\n";
                                                    $scripttext .= 'var map;' ."\n";
                                                    $scripttext .= 'var infowindow;' ."\n";
                                                    $scripttext .= 'var marker;' ."\n";

                                                    $scripttext .= 'function initialize() {' ."\n";

                                                    $scripttext .= 'infowindow = new BMap.InfoWindow();' ."\n";

                                                    if ($mapDefLat != "" && $mapDefLng !="")
                                                    {
                                                            $scripttext .= 'spblocation = new BMap.Point('.$mapDefLng.', '.$mapDefLat.');' ."\n";
                                                    }
                                                    else
                                                    {
                                                            $scripttext .= 'spblocation = new BMap.Point(116.404, 39.915);' ."\n";
                                                    }
 
                                                    $scripttext .= '    map = new BMap.Map(document.getElementById("BDMapsID"));' ."\n";

  
                                                // Clear the current selection when the drawing mode is changed, or when the
                                                // map is clicked.
                                            /*   
                                               $scripttext .= '      map.addEventListener(\'click\', getSelectionPathChanged);' ."\n";
                                            */	
                                                    $scripttext .= 'initialLocation = spblocation;' ."\n";
                                                    $scripttext .= '    map.setCenter(initialLocation);' ."\n";

                                                            // New version without marker
                                                            $scripttext .= '  marker = new BMap.Marker(initialLocation, {' ."\n";
                                                            $scripttext .= '      enableDragging:true, ' ."\n";
                                                            // Replace to new, because all charters are shown
                                                            //$scripttext .= '      title:"'.htmlspecialchars(str_replace('\\', '/', $this->item->title) , ENT_QUOTES, 'UTF-8').'"' ."\n";		
                                                            $scripttext .= '      title:"'.str_replace('\\', '/', str_replace('"', '\'\'', $this->item->title)).'"' ."\n";
                                                            $scripttext .= '});'."\n";

                                                            $scripttext .= '    marker.addEventListener(\'dragend\', function(event) {' ."\n";
                                                            $scripttext .= '    document.forms.adminForm.jform_helpitem.value = event.point.lng +","+event.point.lat;' ."\n";
                                                            $scripttext .= '    });' ."\n";

                                                            $scripttext .= '    map.addEventListener(\'click\', function(event) {' ."\n";
                                                            $scripttext .= '    marker.setPosition(event.point);' ."\n";
                                                            $scripttext .= '    document.forms.adminForm.jform_helpitem.value = event.point.lng +","+event.point.lat;' ."\n";
                                                            $scripttext .= '    });' ."\n";

                                                        $scripttext .= '  map.addOverlay(marker);' ."\n";

                                                            $scripttext .= '  marker.setPosition(initialLocation);' ."\n";

                                                            $scripttext .= '  map.centerAndZoom(initialLocation, 14);' ."\n";
                                                            $scripttext .= '  map.enableDoubleClickZoom();' ."\n";
                                                            $scripttext .= '  map.addControl(new BMap.MapTypeControl());' ."\n";
                                                            $scripttext .= '  map.addControl(new BMap.ScaleControl());' ."\n";
                                                            $scripttext .= '  map.addControl(new BMap.OverviewMapControl());' ."\n";
                                                            $scripttext .= '  map.addControl(new BMap.NavigationControl());' ."\n";

                                                    if ((int)$this->item->id == 0)		
                                                    {
                                                            $scripttext .= '  var geolocation = new BMap.Geolocation();' ."\n";
                                                            $scripttext .= '  geolocation.getCurrentPosition(function(r){' ."\n";
                                                            $scripttext .= '    if(this.getStatus() == BMAP_STATUS_SUCCESS){' ."\n";
                                                            $scripttext .= '      initialLocation = new BMap.Point(r.point.lng, r.point.lat);' ."\n";
                                                    $scripttext .= '      map.setCenter(initialLocation);' ."\n";
                                                            $scripttext .= '      marker.setPosition(initialLocation);' ."\n";
                                                            //$scripttext .= '    alert(\'detected\'+r.point.lng+\',\'+r.point.lat);' ."\n";
                                                            $scripttext .= '    }' ."\n";
                                                            $scripttext .= '    else {' ."\n";
                                                            $scripttext .= '      initialLocation = spblocation;' ."\n";
                                                            //$scripttext .= '    alert(\'failed\'+this.getStatus());' ."\n";
                                                    $scripttext .= '      map.setCenter(initialLocation);' ."\n";
                                                            $scripttext .= '      marker.setPosition(initialLocation);' ."\n";
                                                            $scripttext .= '    }' ."\n";
                                                            $scripttext .= '  });'."\n";
                                                    }
                                                    else
                                                    {
                                                            if ($this->item->path != "")
                                                            {
                                                                    if ((int)$this->item->objecttype == 0
                                                                     || (int)$this->item->objecttype == 1) 
                                                                    {

                                                                            $scripttext .= ' var allCoordinates = [ '."\n";
                                                                            $scripttext .=' new BMap.Point('.str_replace(";","), new BMap.Point(", $this->item->path).') '."\n";
                                                                            $scripttext .= ' ]; '."\n";


                                                                            if ((int)$this->item->objecttype == 0) 
                                                                            {
                                                                                    $scripttext .= ' var plPath'. $this->item->id.' = new BMap.Polyline('."\n";
                                                                            }
                                                                            else
                                                                            {
                                                                                    $scripttext .= ' var plPath'. $this->item->id.' = new BMap.Polygon('."\n";
                                                                            }

                                                                            $scripttext .= ' allCoordinates, {'."\n";

                                                                            /*
                                                                            if (isset($this->item->geodesic) && (int)$this->item->geodesic == 1) 
                                                                            {
                                                                                    $scripttext .= ' geodesic: true, '."\n";
                                                                            }
                                                                            else
                                                                            {
                                                                                    $scripttext .= ' geodesic: false, '."\n";
                                                                            }
                                                                            */

                                                                            // $scripttext .= ' editable: true, '."\n";

                                                                            $scripttext .= ' strokeColor: "'.$this->item->color.'"'."\n";
                                                                            $scripttext .= ',strokeOpacity: '.$this->item->opacity."\n";
                                                                            $scripttext .= ',strokeWeight: '.$this->item->weight."\n";
                                                                            if ((int)$this->item->objecttype == 1) 
                                                                            {
                                                                                    if ($this->item->fillcolor != "")
                                                                                    {
                                                                                            $scripttext .= ',fillColor: "'.$this->item->fillcolor.'"'."\n";
                                                                                    }
                                                                                    if ($this->item->fillopacity != "")
                                                                                    {
                                                                                            $scripttext .= ',fillOpacity: '.$this->item->fillopacity."\n";
                                                                                    }
                                                                            }

                                                                            $scripttext .= ' });'."\n";


                                                                            $scripttext .= 'map.addOverlay(plPath'. $this->item->id.');'."\n";

                                                                    }
                                                                    else if ((int)$this->item->objecttype == 2)
                                                                    {
                                                                            if ($this->item->radius != "")
                                                                            {
                                                                                    $arrayPathCoords = explode(';', $this->item->path);
                                                                                    $arrayPathIndex = 0;
                                                                                    foreach ($arrayPathCoords as $currentpathcoordinates) 
                                                                                    {
                                                                                            $arrayPathIndex += 1;
                                                                                            $scripttext .= ' var plPath'.$arrayPathIndex.'_'. $this->item->id.' = new BMap.Circle('."\n";
                                                                                            $scripttext .= ' new BMap.Point('.$currentpathcoordinates.'), '."\n";
                                                                                            $scripttext .= ' '.$this->item->radius.', '."\n";
                                                                                            $scripttext .= '{'."\n";
                                                                                            $scripttext .= ' strokeColor: "'.$this->item->color.'"'."\n";
                                                                                            $scripttext .= ',strokeOpacity: '.$this->item->opacity."\n";
                                                                                            $scripttext .= ',strokeWeight: '.$this->item->weight."\n";
                                                                                            if ($this->item->fillcolor != "")
                                                                                            {
                                                                                                    $scripttext .= ',fillColor: "'.$this->item->fillcolor.'"'."\n";
                                                                                            }
                                                                                            if ($this->item->fillopacity != "")
                                                                                            {
                                                                                                    $scripttext .= ',fillOpacity: '.$this->item->fillopacity."\n";
                                                                                            }
                                                                                            $scripttext .= '  });' ."\n";
                                                                                            $scripttext .= 'map.addOverlay(plPath'.$arrayPathIndex.'_'. $this->item->id.');'."\n";
                                                                                    }

                                                                            }
                                                                    }
                                                                    else
                                                                    {
                                                                    }

                                                                    $arrayPC = explode(';', $this->item->path);
                                                                    $coordsPCxy = 0;
                                                                    foreach ($arrayPC as $currentpathcoordinates) 
                                                                    {	
                                                                            $coordsPC = explode(',', $currentpathcoordinates);
                                                                            if ($coordsPCxy == 0)
                                                                            {
                                                                                    $coordsPCxMin = $coordsPC[0];
                                                                                    $coordsPCxMax = $coordsPC[0];
                                                                                    $coordsPCyMin = $coordsPC[1];
                                                                                    $coordsPCyMax = $coordsPC[1];
                                                                                    $coordsPCxy = 1;
                                                                            }
                                                                            else
                                                                            {
                                                                                    if (isset($coordsPC[0]) && isset($coordsPC[1]))
                                                                                    {
                                                                                            if ($coordsPC[0] < $coordsPCxMin)
                                                                                            {
                                                                                                    $coordsPCxMin = $coordsPC[0];
                                                                                            }
                                                                                            if ($coordsPC[1] < $coordsPCyMin)
                                                                                            {
                                                                                                    $coordsPCyMin = $coordsPC[1];
                                                                                            }
                                                                                            if ($coordsPC[0] > $coordsPCxMax)
                                                                                            {
                                                                                                    $coordsPCxMax = $coordsPC[0];
                                                                                            }
                                                                                            if ($coordsPC[1] > $coordsPCyMax)
                                                                                            {
                                                                                                    $coordsPCyMax = $coordsPC[1];
                                                                                            }
                                                                                    }
                                                                            }

                                                                    }

                                                                    if ($coordsPCxy == 1)
                                                                    {
                                                                            $scripttext .= 'map.setViewport([' ."\n";
                                                                            $scripttext .= '  new BMap.Point('.$coordsPCxMin.', '.$coordsPCyMin.'),' ."\n";
                                                                            $scripttext .= '  new BMap.Point('.$coordsPCxMax.', '.$coordsPCyMax.')]);' ."\n";
                                                                    }

                                                            }
                                                    }


                                            // end initialize	
                                            $scripttext .= '};' ."\n";

                                            $scripttext .= 'function loadScript() {' ."\n";
                                            $scripttext .= '  var script = document.createElement("script");' ."\n";
                                            $scripttext .= '  script.type = "text/javascript";' ."\n";
                                            $scripttext .= '  script.src = "'.$urlProtocol.'://api.map.baidu.com/api?&ak='.$apikey.$mainScriptMiddle.'&callback=initialize";' ."\n";
                                            $scripttext .= '  document.body.appendChild(script);' ."\n";
                                            $scripttext .= '};' ."\n";

                                            $scripttext .= 'window.onload = loadScript;' ."\n";

                                            $scripttext .= '//]]></script>' ."\n";
                                            // Script end

                                            echo $scripttext;



                                            ?>
                                            </div>


                                            <?php
                                                    $credits ='<div>'."\n";
                                                if ((int)$mapMapTypeOSM != 0)
                                                    {
                                                            $credits .= 'OSM '.JText::_('COM_ZHBAIDUMAP_MAP_POWEREDBY').': ';
                                                            $credits .= '<a href="http://www.openstreetmap.org/" target="_blank">OpenStreetMap</a>'."\n";
                                                    }
                                                    $credits .='</div>'."\n";
                                            echo $credits;
                                            ?>

                                            <div class="row-fluid">
                                                    <input type="hidden" name="task" value="mappath.edit" />
                                                    <?php echo JHtml::_('form.token'); ?>
                                            </div>


                                            </div>
				

				</div>
			</div>
		</div>
	
    
	<div class="tab-pane" id="tab1">
		<fieldset class="adminform">
			
				<?php foreach($this->form->getFieldset('details') as $field): ?>
				<div class="control-group">
					<?php 
						if ($field->id == 'jform_markergroup')
						{
							?>
							<div class="control-label">
							<?php 
								echo $field->label;
							?>
							</div>
							<div class="controls">
							<?php 
								array_unshift($this->markerGroupList, JHTML::_('select.option', '', JText::_( 'COM_ZHBAIDUMAP_MAPMARKER_FILTER_PLACEMARK_GROUP'), 'value', 'text')); 
								echo JHTML::_( 'select.genericlist', $this->markerGroupList, 'jform[markergroup]',  'class="inputbox span5" size="1"', 'value', 'text', (int)$this->item->markergroup, 'jform_markergroup');
								//echo $field->label;
								//echo $field->input;
							?>
							</div>
							<?php 
						}
						else if ($field->id == 'jform_descriptionhtml')
						{
							?>
							<div class="control-label">
							<?php 
								echo '<div class="clr"></div>';
								echo $field->label;
							?>
							</div>
							<div class="controls">
							<?php 
								echo '<div class="clr"></div>';
								echo $field->input;
							?>
							</div>
							<?php 
						}
						else
						{
							?>
							<div class="control-label">
							<?php 
								echo $field->label;
							?>
							</div>
							<div class="controls">
							<?php 
								echo $field->input;
							?>
							</div>
							<?php 
						}
						?>
				</div>
				<?php endforeach; ?>

			
		</fieldset>
	</div>
	<div class="tab-pane" id="tab2">
		
		<fieldset class="adminform">
			
				<?php foreach($this->form->getFieldset('pathadvanced') as $field): ?>
				<div class="control-group">
					<?php 
						?>
						<div class="control-label">
						<?php 
							echo $field->label;
						?>
						</div>
						<div class="controls">
						<?php 
							echo $field->input;
						?>
						</div>
						<?php 
					?>
				</div>
				<?php endforeach; ?>
			
		</fieldset>
	</div>
	<div class="tab-pane" id="tab3">
		
		<fieldset class="adminform">
			
				<?php /* foreach($this->form->getFieldset('kmloptions') as $field): ?>
				<div class="control-group">
					<?php 
						?>
						<div class="control-label">
						<?php 
							echo $field->label;
						?>
						</div>
						<div class="controls">
						<?php 
							echo $field->input;
						?>
						</div>
						<?php 
					?>
				</div>
				<?php endforeach; */ ?>
			
		</fieldset>
	</div>
	<div class="tab-pane" id="tab5">
		
		<fieldset class="adminform">
			
				<?php foreach($this->form->getFieldset('groundimage') as $field): ?>
				<div class="control-group">
					<?php 
						?>
						<div class="control-label">
						<?php 
							echo $field->label;
						?>
						</div>
						<div class="controls">
						<?php 
							echo $field->input;
						?>
						</div>
						<?php 
					?>
				</div>
				<?php endforeach; ?>
			
		</fieldset>
	</div>

	<?php echo $this->loadTemplate('params'); ?>

</div>

</form>


