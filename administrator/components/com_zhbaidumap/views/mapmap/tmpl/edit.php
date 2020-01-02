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
        $map_height = "400px";
        $map_height_wrap = "450px";
    }
    else
    {
        $map_height = ((int)$this->map_height - 50) . "px";
        $map_height_wrap = (int)$this->map_height . "px";
    }
}
else 
{
    $map_height = "400px";
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
		<li class="active"><a href="#tab0" data-toggle="tab"><?php echo JText::_('COM_ZHBAIDUMAP_MAP_MAP'); ?></a></li>
		<li><a href="#tab1" data-toggle="tab"><?php echo JText::_('COM_ZHBAIDUMAP_MAP_DETAIL'); ?></a></li>
		<li><a href="#tab2" data-toggle="tab"><?php echo JText::_('COM_ZHBAIDUMAP_MAP_DECOR_HEADER'); ?></a></li>
		<li><a href="#tab3" data-toggle="tab"><?php echo JText::_('COM_ZHBAIDUMAP_MAP_DECOR_FOOTER'); ?></a></li>
                <li><a href="#tab4" data-toggle="tab"><?php echo JText::_('COM_ZHBAIDUMAP_MAP_DECOR_STYLE'); ?></a></li>
		<li><a href="#tab5" data-toggle="tab"><?php echo JText::_('COM_ZHBAIDUMAP_MAP_MAPDECOR'); ?></a></li>
		<li><a href="#tab6" data-toggle="tab"><?php echo JText::_('COM_ZHBAIDUMAP_MAP_MAPMARKER'); ?></a></li>
		<li><a href="#tab7" data-toggle="tab"><?php echo JText::_('COM_ZHBAIDUMAP_MAP_DETAIL_PLACEMARKLIST'); ?></a></li>
		<li><a href="#tab8" data-toggle="tab"><?php echo JText::_('COM_ZHBAIDUMAP_MAP_MAPMARKERGROUP'); ?></a></li>
		<!-- <li><a href="#tab9" data-toggle="tab"><?php echo JText::_('COM_ZHBAIDUMAP_MAP_MAPROUTE'); ?></a></li> -->
		<li><a href="#tab12" data-toggle="tab"><?php echo JText::_('COM_ZHBAIDUMAP_MAP_MAPADVANCED'); ?></a></li>
		<li><a href="#tab13" data-toggle="tab"><?php echo JText::_('COM_ZHBAIDUMAP_MAP_GEOLOCATION'); ?></a></li>
		<li><a href="#tab14" data-toggle="tab"><?php echo JText::_('COM_ZHBAIDUMAP_MAPMARKER_DETAIL_INTEGRATION'); ?></a></li>
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
				
					<?php foreach($this->form->getFieldset('mapmain') as $field): ?>
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
<div>
<div id="mapDivWrapper" class="row-fluid" style="margin:0;padding:0;width:100%;height:<?php echo $map_height_wrap ?>">

    <div id="placesDivAC" class="row-fluid" style="margin:0;padding:0;width:100%;height:50px">
        <input id="searchTextField" type="text" class="span5" size="200">
        <?php  echo '  <button id="findAddressButton" onclick="Do_Find(); return false;">'.JText::_('COM_ZHBAIDUMAP_MAPMARKER_DETAIL_DOFINDBUTTON').'</button>'; ?>
    </div>
<div id="BDMapsID" class="row-fluid" style="margin:0;padding:0;width:100%;height:<?php echo $map_height ?>">
	
<?php 


$document	= JFactory::getDocument();
$loadmodules	='';

$document->addStyleSheet(JURI::root() .'administrator/components/com_zhbaidumap/assets/css/admin.css');


$mapDefLat = $this->mapDefLat;
$mapDefLng = $this->mapDefLng;

$mapMapTypeBaidu = $this->mapMapTypeBaidu;
$mapMapTypeOSM = $this->mapMapTypeOSM;
$mapMapTypeCustom = $this->mapMapTypeCustom;


//Script begin
$scripttext = '<script type="text/javascript" >//<![CDATA[' ."\n";


	$scripttext .= 'var initialLocation;' ."\n";
        $scripttext .= 'var initialZoom;' ."\n";
	$scripttext .= 'var spblocation;' ."\n";
	$scripttext .= 'var browserSupportFlag =  new Boolean();' ."\n";
	$scripttext .= 'var map;' ."\n";
	$scripttext .= 'var infowindow;' ."\n";
	$scripttext .= 'var marker;' ."\n";
        $scripttext .= 'var geocoder;' ."\n";
        $scripttext .= 'var inputPlacesAC;' ."\n";


	$scripttext .= 'function initialize() {' ."\n";

	$scripttext .= 'infowindow = new BMap.InfoWindow();' ."\n";
        $scripttext .= 'geocoder = new BMap.Geocoder();'."\n";
	
	if ($mapDefLat != "" && $mapDefLng !="")
	{
		$scripttext .= 'spblocation = new BMap.Point('.$mapDefLng.', '.$mapDefLat.');' ."\n";
	}
	else
	{
		$scripttext .= 'spblocation = new BMap.Point(116.404, 39.915);' ."\n";
	}

        $scripttext .= 'initialZoom = 14;' ."\n";


        $curr_maptype_list = '';


        if ((int)$mapMapTypeBaidu != 0
          || ((int)$mapMapTypeCustom == 0
             && (int)$mapMapTypeOSM == 0))
        {
                $curr_maptype_list .= '	  BMAP_NORMAL_MAP,' ."\n";
                $curr_maptype_list .= '	  BMAP_PERSPECTIVE_MAP,' ."\n";
                $curr_maptype_list .= '	  BMAP_SATELLITE_MAP,' ."\n";
                $curr_maptype_list .= '	  BMAP_HYBRID_MAP' ."\n";
        }


         $scripttext .= 'var mpTypes = ['.$curr_maptype_list.'];'."\n";

        $scripttext .= '  map = new BMap.Map(document.getElementById("BDMapsID"));' ."\n";

            //$scripttext .= 'map.setMapType(mapTypeOSM);';
            //$scripttext .= 'map.addTileLayer(tileLayerOSM);';

        $scripttext .= '  map.addControl(new BMap.MapTypeControl(mpTypes));' ."\n";
        
        $scripttext .= '  inputPlacesAC = document.getElementById(\'searchTextField\');' ."\n";
                                        
	if (isset($this->item->latitude) && isset($this->item->longitude) )
	{
		$scripttext .= 'initialLocation = new BMap.Point('.$this->item->longitude.', ' .$this->item->latitude.');' ."\n";
	        $scripttext .= '  map.setCenter(initialLocation);' ."\n";

		$scripttext .= '  marker = new BMap.Marker(initialLocation, {' ."\n";
		$scripttext .= '      enableDragging:true, ' ."\n";
		// Replace to new, because all charters are shown
		//$scripttext .= '      title:"'.htmlspecialchars(str_replace('\\', '/', $this->item->title) , ENT_QUOTES, 'UTF-8').'"' ."\n";		
		$scripttext .= '      title:"'.str_replace('\\', '/', str_replace('"', '\'\'', $this->item->title)).'"' ."\n";
		$scripttext .= '});'."\n";

		$scripttext .= '    marker.addEventListener(\'dragend\', function(event) {' ."\n";
		$scripttext .= '    document.forms.adminForm.jform_longitude.value = event.point.lng;' ."\n";
		$scripttext .= '    document.forms.adminForm.jform_latitude.value = event.point.lat;' ."\n";
		$scripttext .= '    });' ."\n";
		
		$scripttext .= '    map.addEventListener(\'click\', function(event) {' ."\n";
		$scripttext .= '    marker.setPosition(event.point);' ."\n";
		$scripttext .= '    document.forms.adminForm.jform_longitude.value = event.point.lng;' ."\n";
		$scripttext .= '    document.forms.adminForm.jform_latitude.value = event.point.lat;' ."\n";
		$scripttext .= '    });' ."\n";

	    $scripttext .= '  map.addOverlay(marker);' ."\n";
            $scripttext .= '  map.setCenter(initialLocation);' ."\n";
            $scripttext .= '  marker.setPosition(initialLocation);' ."\n";

	}
	else
	{
		$scripttext .= 'initialLocation = spblocation;' ."\n";
	        $scripttext .= '  map.setCenter(initialLocation);' ."\n";

		$scripttext .= '  marker = new BMap.Marker(initialLocation, {' ."\n";
		$scripttext .= '      enableDragging:true, ' ."\n";
		// Replace to new, because all charters are shown
		//$scripttext .= '      title:"'.htmlspecialchars(str_replace('\\', '/', $this->item->title) , ENT_QUOTES, 'UTF-8').'"' ."\n";		
		$scripttext .= '      title:"'.str_replace('\\', '/', str_replace('"', '\'\'', $this->item->title)).'"' ."\n";
		$scripttext .= '});'."\n";

	    $scripttext .= '  map.addOverlay(marker);' ."\n";
		

		$scripttext .= '    marker.addEventListener(\'dragend\', function(event) {' ."\n";
		$scripttext .= '    document.forms.adminForm.jform_longitude.value = event.point.lng;' ."\n";
		$scripttext .= '    document.forms.adminForm.jform_latitude.value = event.point.lat;' ."\n";
		$scripttext .= '    });' ."\n";
		
		$scripttext .= '    map.addEventListener(\'click\', function(event) {' ."\n";
		$scripttext .= '    marker.setPosition(event.point);' ."\n";
		$scripttext .= '    document.forms.adminForm.jform_longitude.value = event.point.lng;' ."\n";
		$scripttext .= '    document.forms.adminForm.jform_latitude.value = event.point.lat;' ."\n";
		$scripttext .= '    });' ."\n";

        $scripttext .= '    map.setCenter(initialLocation);' ."\n";
		$scripttext .= '    marker.setPosition(initialLocation);' ."\n";

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
                
    $scripttext .= '  var ac = new BMap.Autocomplete(' ."\n";
    $scripttext .= '  	{"input" : "searchTextField"' ."\n";
    $scripttext .= '  	,"location" : map' ."\n";
    $scripttext .= '  });' ."\n";
    $scripttext .= '  ac.addEventListener("onconfirm", function(e) {' ."\n";
    $scripttext .= '    var ac_value = e.item.value;' ."\n";
    $scripttext .= '    myACValue = ac_value.province +  ac_value.city +  ac_value.district +  ac_value.street +  ac_value.business;' ."\n";
    $scripttext .= '    setACPlace();' ."\n";
    $scripttext .= '  });' ."\n";

    $scripttext .= '  function setACPlace(){' ."\n";
    $scripttext .= '      function acFunction(){' ."\n";
    $scripttext .= '          latlngAC = local.getResults().getPoi(0).point;' ."\n";
    $scripttext .= '          marker.setPosition(latlngAC);' ."\n";
    $scripttext .= '          marker.setTitle(local.getResults().getPoi(0).address);' ."\n";
    $scripttext .= '          map.setCenter(latlngAC);' ."\n";
    $scripttext .= '          map.setZoom(17);' ."\n";
    // For normal render marker after move
    //$scripttext .= '  map.addOverlay(marker);' ."\n";
    $scripttext .= '          document.forms.adminForm.jform_longitude.value = latlngAC.lng;' ."\n";
    $scripttext .= '          document.forms.adminForm.jform_latitude.value = latlngAC.lat;' ."\n";
    $scripttext .= '      };' ."\n";
    $scripttext .= '      var local = new BMap.LocalSearch(map, {' ."\n";
    $scripttext .= '        onSearchComplete: acFunction' ."\n";
    $scripttext .= '      });' ."\n";
    $scripttext .= '      local.search(myACValue);' ."\n";
    $scripttext .= '  };' ."\n";               

    $scripttext .= '  map.centerAndZoom(initialLocation, initialZoom);' ."\n";
    $scripttext .= '  map.enableDoubleClickZoom();' ."\n";
    $scripttext .= '  map.addControl(new BMap.MapTypeControl());' ."\n";
    $scripttext .= '  map.addControl(new BMap.ScaleControl());' ."\n";
    $scripttext .= '  map.addControl(new BMap.OverviewMapControl());' ."\n";
    $scripttext .= '  map.addControl(new BMap.NavigationControl());' ."\n";
    //$scripttext .= '  map.addControl(new BMap.NavigationControl({type: BMap.NavigationControl.BMAP_NAVIGATION_CONTROL_SMALL}));' ."\n";
	

// end initialize	
$scripttext .= '};' ."\n";

$scripttext .= 'function loadScript() {' ."\n";
$scripttext .= '  var script = document.createElement("script");' ."\n";
$scripttext .= '  script.type = "text/javascript";' ."\n";
$scripttext .= '  script.src = "'.$urlProtocol.'://api.map.baidu.com/api?&ak='.$apikey.$mainScriptMiddle.'&callback=initialize";' ."\n";
$scripttext .= '  document.body.appendChild(script);' ."\n";
$scripttext .= '};' ."\n";

// Find button
$scripttext .= 'function Do_Find() {'."\n";
$scripttext .= '  geocoder.getPoint( inputPlacesAC.value , function(point) {'."\n";
$scripttext .= '  if (point) {'."\n";
$scripttext .= '    var latlngFind = point;' ."\n";

$scripttext .= '  marker.setPosition(latlngFind);' ."\n";
$scripttext .= '  marker.setTitle(inputPlacesAC.value);' ."\n";

$scripttext .= '  map.setCenter(latlngFind);' ."\n";
$scripttext .= '  map.setZoom(17);' ."\n";


// For normal render marker after move
//$scripttext .= '  map.addOverlay(marker);' ."\n";
$scripttext .= '  document.forms.adminForm.jform_longitude.value = latlngFind.lng;' ."\n";
$scripttext .= '  document.forms.adminForm.jform_latitude.value = latlngFind.lat;' ."\n";

$scripttext .= '  }'."\n";
$scripttext .= '  else'."\n";
$scripttext .= '  {'."\n";
$scripttext .= '    alert("'.JText::_('COM_ZHBAIDUMAP_MAPMARKER_GEOCODING_ERROR_REASON').': " + "status" + "\n" + "'.JText::_('COM_ZHBAIDUMAP_MAPMARKER_GEOCODING_ERROR_ADDRESS').': "+inputPlacesAC.value);'."\n";
$scripttext .= '  }'."\n";
$scripttext .= '}, "");'."\n";

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

</div>
</div>
                        </div>      
                    </div>  
                            
		<div class="tab-pane" id="tab1">
			<fieldset class="adminform">
				
					<?php foreach($this->form->getFieldset('details') as $field): ?>
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
		<div class="tab-pane" id="tab2">
			<fieldset class="adminform">
				
					<?php foreach($this->form->getFieldset('mapdecorheader') as $field): ?>
						<div class="control-group">
						<?php 
						if ($field->id == 'jform_headerhtml')
						{
							echo '<div class="clr"></div>';
							?>
							<div class="control-label">
							<?php 
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
		<div class="tab-pane" id="tab3">
			<fieldset class="adminform">
				
					<?php foreach($this->form->getFieldset('mapdecorfooter') as $field): ?>
						<div class="control-group">
						<?php 
						if ($field->id == 'jform_footerhtml')
						{
							echo '<div class="clr"></div>';
							?>
							<div class="control-label">
							<?php 
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
		<div class="tab-pane" id="tab4">
			<fieldset class="adminform">
				
					<?php foreach($this->form->getFieldset('mapdecorstyle') as $field): ?>
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
		<div class="tab-pane" id="tab5">
			<?php echo JHtml::_('sliders.start', 'zhbaidumap-slider-mapcontrols'); ?>

			<?php echo JHtml::_('sliders.panel', JText::_('COM_ZHBAIDUMAP_MAP_MAPDECOR'), 'map-controls');?>
				<fieldset class="adminform">
					
						<?php foreach($this->form->getFieldset('mapdecor') as $field): ?>
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
			<?php /* echo JHtml::_('sliders.panel', JText::_('COM_ZHBAIDUMAP_MAP_GEOFINDCONTROL'), 'map-findcontrol');?>
			<fieldset class="adminform">
			
				<?php foreach($this->form->getFieldset('mapcontrolgeofind') as $field): ?>
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
			<?php */ ?>
			<?php echo JHtml::_('sliders.panel', JText::_('COM_ZHBAIDUMAP_MAP_MAPPOSITION'), 'control-positions');?>
			<fieldset class="adminform">
			
				<?php foreach($this->form->getFieldset('positions') as $field): ?>
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
			<?php echo JHtml::_('sliders.end'); ?>
		</div>
		<div class="tab-pane" id="tab6">
			<fieldset class="adminform">
				
					<?php foreach($this->form->getFieldset('mapmarker') as $field): ?>
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
		<div class="tab-pane" id="tab7">
			<fieldset class="adminform">
				
					<?php foreach($this->form->getFieldset('mapmarkerlist') as $field): ?>
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
		<div class="tab-pane" id="tab8">
			
			<fieldset class="adminform">
				
					<?php foreach($this->form->getFieldset('mapmarkergroup') as $field): ?>
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
		<div class="tab-pane" id="tab9">
			
			<fieldset class="adminform">
				
					<?php /* foreach($this->form->getFieldset('maproute') as $field): ?>
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
		<div class="tab-pane" id="tab12">
			
			<fieldset class="adminform">
				
					<?php foreach($this->form->getFieldset('mapadvanced') as $field): ?>
						<div class="control-group">
						<?php
                                                if ($field->id == 'jform_override_id')
						{
							?>
							<div class="control-label">
							<?php 
								echo $field->label;
							?>
							</div>
							<div class="controls">
							<?php 
								array_unshift($this->mapOverrideList, JHTML::_('select.option', '0', JText::_( 'COM_ZHBAIDUMAP_MAP_DETAIL_OVERRIDE_DEFAULT'), 'value', 'text')); 
								echo JHTML::_( 'select.genericlist', $this->mapOverrideList, 'jform[override_id]',  'class="inputbox required" size="1"', 'value', 'text', (int)$this->item->override_id, 'jform_override_id');
								//echo $field->label;
								//echo $field->input;
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
		<div class="tab-pane" id="tab13">
			
			<fieldset class="adminform">
				
					<?php foreach($this->form->getFieldset('mapgeolocation') as $field): ?>
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
		<div class="tab-pane" id="tab14">
			<fieldset class="adminform">
				
					<?php foreach($this->form->getFieldset('integration') as $field): ?>
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
	




<div>
	<input type="hidden" name="task" value="mapmap.edit" />
	<?php echo JHtml::_('form.token'); ?>
</div>


</div>

</form>


