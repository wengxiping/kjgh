<?php
/**
 * @package	Mosets Tree
 * @copyright	(C) 2015-present Mosets Consulting. All rights reserved.
 * @license	GNU General Public License
 * @author	Lee Cher Yeong <mtree@mosets.com>
 * @url		http://www.mosets.com/tree/
 */

defined('_JEXEC') or die('Restricted access');

class mFieldType_multipledates extends mFieldType
{
	/**
	 * Decide if to sort the date from past to future. If set to false, the dates will be displayed as it is entered.
	 *
	 * @var bool
	 */
	protected $sort_date = true;

	/**
	 * Use PHP's relative date/time format to specify if you want to output the dates from a specific relative date. Set
	 * to '0' if you want to output all entered dates.
	 *
	 * For example, if you only want to only output future dates, enter 'tomorrow'. Other possible value includes:
	 *  - today
	 *  - yesterday
	 *  - 2 days ago
	 *  - +2 weeks
	 *  - -7 weekdays
	 *
	 * @var string
	 *
	 * @see http://php.net/manual/en/datetime.formats.relative.php
	 */
	protected $output_dates_from_when_onwards = 'today';

	function getOutput($view=1)
	{
		$dateFormat = $this->getParam('dateFormat','%e %B %Y');

		// Set the locale so that the month names are localised.
		$locale = JFactory::getLanguage()->getLocale();
		setlocale(LC_TIME, $locale[0]);

		$arrDates = $this->getArrValue();

		$html = '<ul>';

		foreach($arrDates AS $unixTime)
		{
			$html .= '<li>';
			$html .= '<time datetime="' . strftime('%Y-%m-%d',$unixTime) . '">';
			$html .= strftime($dateFormat,$unixTime);
			$html .= '</time>';
			$html .= '</li>';
		}

		$html .= '</ul>';

		return $html;
	}

	/**
	 * Values are stored in YYYY-MM-DD format and are returned as such using getValue() as strings. This function
	 * converts the dates to Unix Time format and return them as array.
	 *
	 * Optionally sort dates too.
	 */
	function getArrValue($applyDateFilter = true)
	{
		$returnArrDates = array();

		$arrDates = explode(',',$this->getValue());

		$only_output_dates_from_this_date_onwards = strtotime($this->output_dates_from_when_onwards);

		foreach( $arrDates AS $date )
		{
			$unixTime = mktime(0,0,0,intval(substr($date,5,2)),intval(substr($date,8,2)),intval(substr($date,0,4)));

			if($applyDateFilter && $unixTime >= $only_output_dates_from_this_date_onwards) {
				$returnArrDates[] = $unixTime;
			}
		}

		if( $this->sort_date && !empty($returnArrDates) ) {
			sort($returnArrDates, SORT_NUMERIC);
		}

		return $returnArrDates;
	}

	function hasValue()
	{
		$arrValue = $this->getArrValue();

		if( empty($arrValue) ) {
			return false;
		}

		return true;
	}

	function getSearchHTML( $showSearchValue=false, $showPlaceholder=false, $idprefix='search_' )
	{
		$searchStartMonthOffset = $this->getParam('searchStartMonthOffset', 0);
		$searchTotalMonths = $this->getParam('searchTotalMonths', 36);

		$searchStartMonthYear = strtotime(sprintf("%+d",$searchStartMonthOffset) . ' month');

		$arrMonth = $this->getMonthArray($searchStartMonthYear, $searchTotalMonths);

		$html = '';
		$searchValue = $this->getSearchValue();

		$html .= '<select name="' . $this->getName() . '[]"';
		$html .= '>';

		if( $showPlaceholder ) {
			$html .= '<option value="">' . $this->getPlaceholderText() . '</option>';
		} else {
			$html .= '<option value="">&nbsp;</option>';
		}

		foreach($arrMonth AS $key => $value) {
			$html .= '<option value="'.htmlspecialchars($key).'"';
			if( $showSearchValue && $searchValue !== false && in_array($key,$searchValue) ) {
				$html .= ' selected=selected';
			}
			$html .= '>' . $value . '</option>';
		}
		$html .= '</select>';

		return $html;
	}

	function getMonthArray($startMonthYear, $totalMonths)
	{
		$start_month_timestamp = strtotime(date('F Y', $startMonthYear));

		$arrMonth = array();

		for( $i=0; $i < $totalMonths; $i++ )
		{
			$currentLoopingMonthYear = strtotime(sprintf("%+d",$i) . ' month', $start_month_timestamp);
			$arrMonth[strtolower(date('Y-m-', $currentLoopingMonthYear))] = date('F Y', $currentLoopingMonthYear);
		}

		return $arrMonth;
	}

	function getInputHTML()
	{
		includeJavascriptCSSFileDatepick();

		$html = '';

		$html .= $this->getCSS();

		$html .= '<input'
			. ($this->isRequired() ? ' required':'')
			. $this->getDataValidatorAttr()
			. ' class="'.($this->isRequired() ? ' required':'')
			. '" type="text" name="' . $this->getInputFieldName(1)
			. '" id="' . $this->getInputFieldID(1)
			. '" size="' . ($this->getSize()?$this->getSize():'30');
		$html .= '" value="' . htmlspecialchars($this->getInputValue()) ;
		$html .= '" />';

		$html .= $this->getInputJavascriptInit();

		$html .= '<span id="' . $this->getInputFieldID(1) . '-multipledatespick" class="inlinePicker"></span>';

		return $html;

	}

	protected function includeJavascriptFile($file)
	{
		JFactory::getDocument()->addScript($this->getFieldTypeAttachmentURL($file));
	}

	protected function getInputJavascriptInit()
	{
		$id = $this->getInputFieldID(1);

		$javascript = <<<JS
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery('#$id'+'-multipledatespick').datepick({
    multiSelect: 999, monthsToShow: [1,3], monthsOffset: 0, dateFormat: 'yyyy-mm-dd', onSelect: function(dates) { updateInputFieldWithValues(); }});

    	var dates = jQuery('#$id').val().split(',');
    	jQuery('#$id' + '-multipledatespick').datepick('setDate', dates);

	});
	function updateInputFieldWithValues() {
		var dates = jQuery('#$id'+'-multipledatespick').datepick('getDate');
	    var value = '';
	    for (var i = 0; i < dates.length; i++) {
	        value += (i == 0 ? '' : ',') + jQuery.datepick.formatDate('yyyy-mm-dd',dates[i]);
	    }
	    jQuery('#$id').val(value || '');
	}
</script>
JS;
		return $javascript;
	}

	protected function getCSS()
	{
		$css = <<<CSS
	<style scoped>
	a.datepick-cmd:hover {
    	color: inherit;
    	text-decoration: none;
	}
	select.datepick-month-year {
		width:inherit;
	}
	</style>
CSS;
		return $css;
	}
}