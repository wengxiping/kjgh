<?php
// namespace components\com_jrealtimeanalytics\framework\html\validate;
/**
 *
 * @package JREALTIMEANALYTICS::components::com_jrealtimeanalytics
 * @subpackage framework
 * @subpackage html
 * @subpackage validate
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Form Field for ip address
 *
 * @package JREALTIMEANALYTICS::components::com_jrealtimeanalytics
 * @subpackage framework
 * @subpackage html
 * @subpackage validate
 * @since 2.0
 */
class JFormRuleIpaddress extends JFormRule {
	/**
	 * The regular expression to use in testing a form field value.
	 *
	 * @var string
	 */
	protected $regex = '^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z';
	
	/**
	 * The regular expression modifiers to use when testing a form field value.
	 *
	 * @var string
	 */
	protected $modifiers = 'i';
	
	/**
	 * Method to test the value.
	 *
	 * @param SimpleXMLElement $element
	 *        	The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param mixed $value
	 *        	The form field value to validate.
	 * @param string $group
	 *        	The field name group control value. This acts as as an array container for the field.
	 *        	For example if the field has name="foo" and the group value is set to "bar" then the
	 *        	full field name would end up being "bar[foo]".
	 * @param JRegistry $input
	 *        	An optional JRegistry object with the entire data set to validate against the entire form.
	 * @param JForm $form
	 *        	The form object for which the field is being tested.
	 *        	
	 * @return boolean True if the value is valid, false otherwise.
	 *        
	 * @since 11.1
	 * @throws UnexpectedValueException if rule is invalid.
	 */
	public function test(SimpleXMLElement $element, $value, $group = null, JRegistry $input = null, JForm $form = null) {
		// Test only if value set, not mandatory
		if (! $value) {
			return true;
		}
		// Check for a valid regex.
		if (empty ( $this->regex )) {
			throw new UnexpectedValueException ( sprintf ( '%s has invalid regex.', get_class ( $this ) ) );
		}
		
		// Add unicode property support if available.
		if (JCOMPAT_UNICODE_PROPERTIES) {
			$this->modifiers = (strpos ( $this->modifiers, 'u' ) !== false) ? $this->modifiers : $this->modifiers . 'u';
		}
		
		// Test the value against the regular expression.
		if (preg_match ( chr ( 1 ) . $this->regex . chr ( 1 ) . $this->modifiers, $value )) {
			return true;
		}
		
		return false;
	}
}
