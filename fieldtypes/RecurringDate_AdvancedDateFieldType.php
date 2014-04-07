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

	public function validate($value){
		$postContent = $this->element->getContentFromPost();
		$value = $postContent[$this->model->handle];

		$startDate = $value['startdate']['date'];
		$startTime = $value['starttime']['time'];
		$endDate = $value['enddate']['date'];
		$endTime = $value['endtime']['time'];

		$errors = array();

		if( empty($startDate) ){
			$errors[] = Craft::t('There must be a valid Start Date');
		}

		//Checking Dates
		if( !empty($endDate) && !empty($startDate) && empty($endTime) && empty($startTime) ){
			if( strtotime($endDate) < strtotime($startDate) ){
				$errors[] = Craft::t('End Date must be after the Start Date');
			}
		}

		//Checking Times
		if( !empty($endDate) && !empty($startDate) && !empty($endTime) && !empty($startTime) ){
			if( strtotime($endDate . ' ' . $endTime) < strtotime($startDate . ' ' . $startTime) ){
				$errors[] = Craft::t('End Date must be after the Start Date');
			}
		}

		if($errors){
			return $errors;
		}
		else{
			return true;
		}
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
			$rule->setInterval($interval);

			if($ends == 'until'){
				$rule->setEndDate(new \DateTime($untilDate));
			}
			else if($ends == 'after'){
				$rule->setCount($count);
			}

			switch ($frequency) {
				case 'daily':
						$rule->setFreq(Recurr\RecurrenceRule::FREQ_DAILY);
					break;

				case 'weekly':
						$rule->setFreq(Recurr\RecurrenceRule::FREQ_WEEKLY);
						if( empty($weekDays) ){
							//If weekdays empty set monday by default
							$rule->setByDay(array('MO'));
						}
						else{
							$rule->setByDay($weekDays);
						}
					break;

				case 'monthly':
						$rule->setFreq(Recurr\RecurrenceRule::FREQ_MONTHLY);
						if( $repeatBy == 'month' ){
							$dayOfMonth = date('j', strtotime($startDate));
							$rule->setByMonthDay(array($dayOfMonth));
						}
						else if( $repeatBy == 'week' ){
							$uStartDate = strtotime($startDate);
							$dayOfWeek = strtoupper( substr( date( 'D', $uStartDate ), 0, -1) );
							$numberOfWeek = ceil( date( 'j', $uStartDate ) / 7 );
							$rule->setByDay(array('+'.$numberOfWeek . $dayOfWeek));
						}
					break;

				case 'yearly':
						$rule->setFreq(Recurr\RecurrenceRule::FREQ_YEARLY);
					break;
			}

			if($startTime){
				$time = strtotime($startDate . ' ' . $startTime);
				return 'DTSTART=' . date('Ymd\THis', $time) . ';' . $rule->getString();
			}
			else{
				$time = strtotime($startDate);
				return 'DTSTART=' . date('Ymd', $time) . ';' . $rule->getString();
			}
		}
		else{
			if($startTime){
				$time = strtotime($startDate . ' ' . $startTime);
				return 'DTSTART=' . date('Ymd\THis', $time) . ';';
			}
			else{
				$time = strtotime($startDate);
				return 'DTSTART=' . date('Ymd', $time) . ';';
			}		
		}
	}
}