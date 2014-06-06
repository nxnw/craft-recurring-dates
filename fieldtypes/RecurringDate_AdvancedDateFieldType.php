<?php
namespace Craft;

use Recurr;

class RecurringDate_AdvancedDateFieldType extends BaseFieldType
{
	public function getName()
	{
		return Craft::t('Advanced Date');
	}

	public function defineContentAttribute()
	{
		return false;
	}
	
	/**
	 * Display fieldtype
	 *
	 * @param string $name  Our fieldtype handle
	 * @return string Return our fields input template
	 */
	public function getInputHtml($name, $value)
	{
		$id = craft()->templates->formatInputId($name);

		$namespaceId = craft()->templates->namespaceInputId($id);

		craft()->templates->includeJsResource('recurringdate/js/advanceddate.js');

		if(!empty($value)) {
			$ruleModel = RecurringDate_RuleModel::populateModel($value);
		} else {
			$ruleModel = new RecurringDate_RuleModel;
			$ruleModel->handle = $name;
		}

		$attr = $ruleModel->getAttributes();

		if( strpos($attr['rrule'], 'EXDATE') !== false ){
            $attr['exdates'] = array();

            $exDatesArray = explode('EXDATE=', $attr['rrule']);
            $exDatesString = $exDatesArray[1];
            $exDatesString = rtrim($exDatesString, ";");
            $exDatesArray = explode(',', $exDatesString);
            
            foreach ($exDatesArray as $index => $date) {
            	$attr['exdates'][] = DateTime::createFromFormat('Ymd', $date);
            }
        }
        else{
        	$attr['exdates'] = array();
        }
		
		$attr['namespaceId'] = $namespaceId;

		return craft()->templates->render('recurringdate/fields', $attr);
	}

	//Leaving the db to be displayed
	public function prepValue($value){
		return craft()->recurringDate->getRule($this);
	}

	public function validate($value){
		return craft()->recurringDate->validateRule($this);
	}

	// After saving element, save field to plugin table
    public function onAfterElementSave()
    {
        // Returns true if entry was saved
        return craft()->recurringDate->saveRuleField($this);
    }
}