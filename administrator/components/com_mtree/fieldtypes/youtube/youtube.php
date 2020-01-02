<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2012-2017 Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

class mFieldType_youtube extends mFieldType {

	function getOutput($view=1) {
		$html ='';

		$params['youtubeWidth'] = $this->getParam('youtubeWidth',560);
		$params['youtubeHeight'] = $this->getParam('youtubeHeight',315);

        $html .= '<div class="youtube-player-container">';
		$html .= '<iframe';
		$html .= ' class="youtube-player"';
		$html .= ' type="text/html"';
		$html .= ' width="'.$params['youtubeWidth'].'"';
		$html .= ' height="'.$params['youtubeHeight'].'"';
		$html .= ' src="' . $this->getSrcValue() . '"';
		$html .= ' allowfullscreen';
		$html .= ' frameborder="0"';
		$html .= '>';
		$html .= '</iframe>';
        $html .= '</div>';

		return $html;
	}

	function getSrcValue()
	{
		$params['youtubeParameters'] = $this->getParam('youtubeParameters','showinfo=0&modestbranding=1&controls=1&rel=0');

		if ( $id = $this->getYoutubeListIdFromUrl($this->getValue()) )
		{
			return '//www.youtube.com/embed/videoseries?list=' . $id . '&'.$params['youtubeParameters'];
		}

		$id = $this->getVideoId();
		return '//www.youtube.com/embed/'.$id.'?'.$params['youtubeParameters'];
	}

	function getYoutubeListIdFromUrl($youtubeUrl)
	{
		$url = parse_url($youtubeUrl);

		if(isset($url['query']))
		{
			parse_str($url['query'], $query);

			if(isset($query['list']) && !empty($query['list']))
			{
				return $query['list'];
			}
		}

		return false;
	}

	function getVideoId() {
		$value = $this->getValue();
		$id = null;
		
		if(empty($value))
		{
			return null;
		}
		$url = parse_url($value);

		if( isset($url['host']) && $url['host'] == 'youtu.be' )
		{
			$id = substr($url['path'],1);
		}
		elseif( isset($url['query']) )
		{
			parse_str($url['query'], $query);
			if (isset($query['v'])) {
		        	$id = $query['v'];
			}
		}

		return $id;
	}
	
	function getInputHTML() {
		$youtubeInputDescription = $this->getParam('youtubeInputDescription','Enter the full URL of the Youtube video page.<br />ie: <b>https://www.youtube.com/watch?v=uZ3tB1UO1hM</b>');

		$html = '';
		$html .= '<input type="text" name="' . $this->getInputFieldName(1) . '" id="' . $this->getInputFieldName(1) . '" size="' . $this->getSize() . '" value="' . htmlspecialchars($this->getInputValue()) . '" />';
		
		if(!empty($youtubeInputDescription))
		{
			$html .= '<p>' . $youtubeInputDescription . '</p>';
		}

		return $html;
	}
	
	function getSearchHTML( $showSearchValue=false, $showPlaceholder=false, $idprefix='search_' )
	{
		$checkboxLabel = $this->getParam('checkboxLabel','Contains video');
		$checkbox_value = $this->getSearchValue();
		
		$html = '';
		$html .= '<label for="' . $this->getName() . '" class="checkbox">';
		$html .= '<input type="checkbox" name="' . $this->getSearchFieldName(1) . '"';
		$html .=' value="1"';
		$html .=' id="' . $this->getSearchFieldName(1) . '"';
		if( $showSearchValue && $checkbox_value == 1 ) {
			$html .= ' checked';
		}
		$html .= ' />';
		$html .= '&nbsp;';
		$html .= $checkboxLabel;
		$html .= '</label>';
		return $html;
	}
	
	function getWhereCondition() {
		if( func_num_args() == 0 ) {
			return null;
		} else {
			return '(cfv#.value <> \'\')';
		}
	}
}