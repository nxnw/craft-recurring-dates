<?php
namespace Craft;

use When;
use Recurr;

class RecurringDate_AdvancedDateFieldType extends BaseFieldType
{
	public function getName()
	{
		return Craft::t('Advanced Date');
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

		return craft()->templates->render('recurringdate/fields', array(
			'name'  => $name,
			'value' => $value
		));
	}

	public function prepValueFromPost($value){
		// echo '<pre>';
		// var_dump($value);
		// echo '</pre>';

		$allday = $value['allday'];
		$startDate = $value['startdate']['date'];
		$startTime = $value['starttime']['time'];
		$endDate = $value['enddate']['date'];
		$endTime = $value['endtime']['time'];
		$repeats = $value['repeats']; //Does it repeat?
		$frequency = $value['interval']; //Weekly, Daily, Monthly, Yearly
		$interval = $value['every']; // i.e. Every 1-30 Months?
		$weekDays = $value['on']; //Which weekdays
		$repeatBy = $value['by']; //Monthly, by day of week, or day of month
		$repeatStart = $value['startdate']['date']; //May not use this
		$ends = $value['ends']; //how it ends (never, after, until)
		$count = $value['occurrences']; // if ending occurs amounts
		$untilDate = $value['untildate']['date']; // if ending until date


		//Builds RRULE based on UI Elements Input
		if($repeats){
			$rule = new Recurr\RecurrenceRule();
			$rule->setStartDate(new \DateTime($startDate));

			switch ($frequency) {
				case 'daily':
						$rule->setFreq(Recurr\RecurrenceRule::FREQ_DAILY);
						$rule->setInterval($interval);
						if($ends == 'until'){
							$rule->setEndDate(new \DateTime($untilDate));
						}
						else if($ends == 'after'){
							$rule->setCount($count);
						}
					break;

				case 'weekly':
						$rule->setFreq(Recurr\RecurrenceRule::FREQ_WEEKLY);
						$rule->setInterval($interval);
					break;

				case 'monthly':
						$rule->setFreq(Recurr\RecurrenceRule::FREQ_MONTHLY);
					break;

				case 'yearly':
						$rule->setFreq(Recurr\RecurrenceRule::FREQ_YEARLY);
					break;
			}

			return $startDate . ' | ' . $rule->getString();
		}
		else{
			return $startDate;
		}

	}
}