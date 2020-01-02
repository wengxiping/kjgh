<?php
/**
 * @version		2.6.x
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

function ja_k2_treerecurse(&$params, $id = 0, $level = 0, $begin = false)
{

	static $output;
	if ($begin) {
		$output = '';
	}
	$mainframe = JFactory::getApplication();
	$root_id = (int)$params->get('root_id');
	$end_level = $params->get('end_level', NULL);
	$id = (int)$id;
	$catid = JRequest::getInt('id');
	$option = JRequest::getCmd('option');
	$view = JRequest::getCmd('view');

	$user = JFactory::getUser();
	$aid = (int)$user->get('aid');
	$db = JFactory::getDBO();

	$jak2Filter = JComponentHelper::getComponent('com_jak2filter');

	switch ($params->get('categoriesListOrdering'))
	{

		case 'alpha' :
			$orderby = 'name';
			break;

		case 'ralpha' :
			$orderby = 'name DESC';
			break;

		case 'order' :
			$orderby = 'ordering';
			break;

		case 'reversedefault' :
			$orderby = 'id DESC';
			break;

		default :
			$orderby = 'id ASC';
			break;
	}

	if (($root_id != 0) && ($level == 0))
	{
		$query = "SELECT * FROM #__k2_categories WHERE parent={$root_id} AND published=1 AND trash=0 ";

	}
	else
	{
		$query = "SELECT * FROM #__k2_categories WHERE parent={$id} AND published=1 AND trash=0 ";
	}

	if (K2_JVERSION != '15')
	{
		$query .= " AND access IN(".implode(',', $user->getAuthorisedViewLevels()).") ";
		if ($mainframe->getLanguageFilter())
		{
			$languageTag = JFactory::getLanguage()->getTag();
			$query .= " AND language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") ";
		}

	}
	else
	{
		$query .= " AND access <= {$aid}";
	}

	$query .= " ORDER BY {$orderby}";

	$db->setQuery($query);
	$rows = $db->loadObjectList();
	if ($db->getErrorNum())
	{
		echo $db->stderr();
		return false;
	}

	if ($level < intval($end_level) || is_null($end_level))
	{
		$output .= '<ul class="level'.$level.'">';
		$i = 0;
		foreach ($rows as $row)
		{
			if ($params->get('categoriesListItemsCounter'))
			{
				$row->numOfItems = ' ('.modK2ToolsHelper::countCategoryItems($row->id).')';
			}
			else
			{
				$row->numOfItems = '';
			}

			if (($option == 'com_k2') && ($view == 'itemlist') && ($catid == $row->id))
			{
				$liClass = 'activeCategory';
				if ($i >= 4) {
					$liClass .= 'collapse';
				}
			}
			else
			{
				$liClass = '';

				if ($i >= 4) {
					$liClass .= 'collapse';
				}
			}

			$catParams = new JRegistry($row->params);
			$color = $catParams->get('category_color', '#1d9bdc');
			$icon = $catParams->get('category_icon', 'images/joomlart/directory-icons/default.png');


			$link = JRoute::_(K2HelperRoute::getCategoryRoute($row->id.':'.urlencode($row->alias)));
			if (modK2ToolsHelper::hasChildren($row->id))
			{
				$output .= '<li class="'.$liClass.'" ><a style="color: '.$color.'; border-color: '.$color.'" href="'.urldecode($link).'"><img src="'.$icon.'"  alt="'.$row->name.'" /><span class="catTitle">'.$row->name.'</span><span class="catCounter">'.$row->numOfItems.'</span></a>';
				ja_k2_treerecurse($params, $row->id, $level + 1);
				$output .= '</li>';
				$i++;
			}
			else
			{
				$output .= '<li class="'.$liClass.'" ><a href="'.urldecode($link).'"><i class="fa fa-check-circle" style="color: '.$color.';"></i><span class="catTitle">'.$row->name.'</span><span class="catCounter" style="color: '.$color.';">'.$row->numOfItems.'</span></a></li>';
			}
		}
		$output .= '</ul>';
	}

	return $output;
}

$output = ja_k2_treerecurse($params, 0, 0, true);
?>

<div class="k2CategoriesListBlock">
	<?php echo $output; ?>
	<div id="arrow-down" class="arrow-down"></div>
</div>

<script type="text/javascript">
 	(function($){
		$(document).ready(function(){ 
		  $( "#arrow-down" ).click(function() {
		  	if ($('.k2CategoriesListBlock .collapse.in').length) {
		  		$('.k2CategoriesListBlock .collapse.in').removeClass('in');
		  	} else {
		  		$('.k2CategoriesListBlock .collapse').addClass('in');
		  	}

		  	if($( "#arrow-down.arrow-down" ).length) {
		  		$( "#arrow-down").removeClass('arrow-down');
		  		$( "#arrow-down").addClass('arrow-up');
		  	} else {
		  		$( "#arrow-down").addClass('arrow-down');
		  		$( "#arrow-down").removeClass('arrow-up');
		  	}
			});
	  });
  })(jQuery);
</script>