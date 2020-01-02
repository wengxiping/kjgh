<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2016-present Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

class mFieldType_audioplayer2 extends mFieldType_file {
	function getJSValidationFunction() {
		return 'function(){return hasExt(document.mtForm.' . $this->getName() . '.value,\'mp3\')}';
	}

	function getJSValidationMessage() {
		return JText::_( 'FLD_AUDIOPLAYER2_PLEASE_SELECT_AN_AUDIO_FILE' );
	}

	function getOutput($view=1) {
		$id = $this->getId();
		$params['audiowidth'] = $this->getParam('audiowidth', 300);
		$params['autoplay'] = $this->getParam('autoplay', 0);
		$params['loop'] = ($this->getParam('loop', 0) == '1') ? 'true' : 'false' ;
		$params['displayfilename'] = $this->getParam('displayfilename',1);

		$html = '';
		$html .= '<div class="mt-audioplayer2-control">';
		$html .= '<script src="' . $this->getFieldTypeAttachmentURL('mediaelement-and-player.min.js'). '"></script>';
		$html .= '<link rel="stylesheet" href="' . $this->getFieldTypeAttachmentURL('mediaelementplayer.css'). '" />';
		$html .= '<audio id="audio-player-' . $id . '" src="' . $this->getDataAttachmentURL() . '" type="audio/mp3" controls="controls"';
		if( $params['autoplay'] ) {
			$html .= ' autoplay';
		}
		$html .= '>';
		$html .= '</div>';

		$html .= '<script>';
		$html .= 'jQuery(\'#audio-player-' . $id . '\').mediaelementplayer({';
		$html .= <<<MEDIAELEMENTOPTIONS
    // width of audio player
    audioWidth: {$params['audiowidth']},
    // height of audio player
    audioHeight: 30,
    // initial volume when the player starts
    startVolume: 0.8,
    // useful for <audio> player loops
    loop: {$params['loop']},
    // enables Flash and Silverlight to resize to content size
    enableAutosize: true,
    // the order of controls you want on the control bar (and other plugins below)
    features: ['playpause','progress','current','duration','tracks','volume','fullscreen'],
    // force iPad's native controls
    iPadUseNativeControls: false,
    // force iPhone's native controls
    iPhoneUseNativeControls: false,
    // force Android's native controls
    AndroidUseNativeControls: false,
    // forces the hour marker (##:00:00)
    alwaysShowHours: false,
    // turns keyboard support on and off for this instance
    enableKeyboard: true,
    // when this player starts, it will pause other players
    pauseOtherPlayers: true
MEDIAELEMENTOPTIONS;
		$html .= '});';
		$html .= '</script>';

		if($params['displayfilename']) {
			$html .= "\n" . '<a href="' . $this->getDataAttachmentURL() . '" target="_blank">';
			$html .= $this->getValue();
			$html .= '</a>';
		}
		return $html;
	}
}
