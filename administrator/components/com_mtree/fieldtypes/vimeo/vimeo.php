<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2015-present Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

class mFieldType_vimeo extends mFieldType {

    function getOutput($view=1) {
        $html ='';
        $id = $this->getVideoId();

        $params['vimeoWidth'] = $this->getParam('vimeoWidth',560);
        $params['vimeoHeight'] = $this->getParam('vimeoHeight',315);
        $params['vimeoParameters'] = $this->getParam('vimeoParameters','portrait=0&amp;color=333');

        $html .= '<div class="vimeo-player-container">';
        $html .= '<iframe';
        $html .= ' class="vimeo-player"';
        $html .= ' type="text/html"';
        $html .= ' width="'.$params['vimeoWidth'].'"';
        $html .= ' height="'.$params['vimeoHeight'].'"';
        $html .= ' src="//player.vimeo.com/video/'.$id;

        if (!empty($params['vimeoParameters'])) {
            $html .= '?'.$params['vimeoParameters'];
        }

        $html .= '"';
        $html .= ' webkitallowfullscreen';
        $html .= ' mozallowfullscreen';
        $html .= ' allowfullscreen';
        $html .= ' frameborder="0"';
        $html .= '>';
        $html .= '</iframe>';
        $html .= '</div>';

        return $html;
    }

    function getVideoId() {
        $value = $this->getValue();
        $id = null;

        if(empty($value))
        {
            return null;
        }
        $url = parse_url($value);

        if( $url['host'] == 'vimeo.com' ) {
            if (preg_match('/(?:https?:\/\/)?(?:www\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)(\d+)(?:$|\/|\?)/', $value, $match)) {
                $id = $match[3];
            }
        }

        return $id;
    }

    function getInputHTML() {
        $vimeoInputDescription = $this->getParam('vimeoInputDescription','Enter the full URL of the Vimeo video page.<br />eg: <b>https://vimeo.com/22714098</b>');

        $html = '';
        $html .= '<input type="text" name="' . $this->getInputFieldName(1) . '" id="' . $this->getInputFieldName(1) . '" size="' . $this->getSize() . '" value="' . htmlspecialchars($this->getInputValue()) . '" />';

        if(!empty($vimeoInputDescription))
        {
            $html .= '<p>' . $vimeoInputDescription . '</p>';
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