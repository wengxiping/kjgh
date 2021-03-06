<?php
/**
* Mosets Tree 
*
* @package Mosets Tree 0.8
* @copyright (C) 2004 Lee Cher Yeong
* @url http://www.mosets.com/
* @author Lee Cher Yeong <mtree@mosets.com>
**/
defined('_JEXEC') or die('Restricted access');

//Base plugin class.
require_once JPATH_ROOT.'/components/com_mtree/Savant2/Plugin.php';

class Savant2_Plugin_ahrefrecommend extends Savant2_Plugin {
	
	function plugin()
	{
		global $mtconf;
		$my	= JFactory::getUser();

		list($link, $attr) = array_merge(func_get_args(), array(null));

		# Load Parameters
		$params = new JRegistry( $link->attribs );
		$params->def( 'show_recommend', $mtconf->get('show_recommend') );

		if ( $params->get( 'show_recommend' ) == 1 ) {

			$html = '';
			$html .= '<a href="';
			$html .= JRoute::_( 'index.php?option=com_mtree&task=recommend&link_id='.$link->link_id);
			$html .= '"';

			# Insert attributes
			if (is_array($attr)) {
				// from array
				foreach ($attr as $key => $val) {
					$key = htmlspecialchars($key);
					$val = htmlspecialchars($val);
					$html .= " $key=\"$val\"";
				}
			} elseif (! is_null($attr)) {
				// from scalar
				$html .= " $attr";
			}
			
			$html .= '>'.JText::_( 'COM_MTREE_RECOMMEND' )	."</a>";

			# Return the recommend link
			return $html;
		}

	}

}
?>