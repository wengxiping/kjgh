<?php

$number_of_ls_columns = $this->config->getTemParam('numOfListingColumnsInSummaryView',1);

$span = 'span'.round(12/$number_of_ls_columns);

if( ($number_of_ls_columns >= 2 && ($i % $number_of_ls_columns) == 1) || $number_of_ls_columns == 1 ) {
	echo '<div class="lsrow row-fluid">';
}

include $this->loadTemplate('sub_listingSummaryStyle'.$this->config->getTemParam('summaryStyle',1).'.tpl.php');

if( ($number_of_ls_columns > 1 && ($i % $number_of_ls_columns == 0 || $i == count($this->links))) || $number_of_ls_columns == 1 ) {
	echo '</div>';
}
