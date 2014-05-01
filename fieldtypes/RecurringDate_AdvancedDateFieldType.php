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

		craft()->templates->includeJs("$(function() { window.advancedDate.create('{$namespaceId}'); });");

		if(!empty($value)) {
			$ruleModel = RecurringDate_RuleModel::populateModel($value);
		} else {
			$ruleModel = new RecurringDate_RuleModel;
			$ruleModel->handle = $name;
		}

		return craft()->templates->render('recurringdate/fields', $ruleModel->getAttributes());
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