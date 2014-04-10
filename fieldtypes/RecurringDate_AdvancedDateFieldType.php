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

		$value['name'] = $name;
		
		$id = craft()->templates->formatInputId($name);

		return craft()->templates->render('recurringdate/fields', $value);
	}

	public function prepValue($value){

		//If it repeats and has an rrule object
		if( strpos($value, "FREQ") !== false ){
			$rule = new Recurr\RecurrenceRule($value);

			$startDateStr = $rule->getStartDate()->format('Ymd');
			$startDate = \Craft\DateTime::createFromFormat('Ymd', $startDateStr);
			
			//If a time is actually set on the start date
			if( strpos( explode(';', explode( 'DTSTART=', $value)[1])[0], 'T') ) {
				$startTimeStr = $rule->getStartDate()->format('His');
				$startTime = \Craft\DateTime::createFromFormat('His', $startTimeStr);
			}
			else{
				$startTime = null;
			}

			if( strpos($value, "BYMONTHDAY") ){
				$fieldValues['by'] = 'month';
			}
			elseif( strpos($value, "BYDAY") ) {
				$fieldValues['by'] = 'week';
			}

			$fieldValues['repeats'] = true;
			$fieldValues['interval'] = strtolower($rule->getFreqAsText());
			$fieldValues['every'] = $rule->getInterval();
			$fieldValues['on'] = $rule->getByDay();

			$ruleTransformer = new Recurr\RecurrenceRuleTransformer($rule, 300);
			$dates = $ruleTransformer->getComputedArray();

			$fieldValues['dates'] = array();
			foreach ($dates as $date) {
				$dateString = $date->format('Ymd\THis');
				$fieldValues['dates'][] = \Craft\DateTime::createFromFormat('Ymd\THis', $dateString);
	 		}
		}
		elseif( strpos($value, "DTSTART") !== false ){
			$startDateTime = rtrim(explode('DTSTART=', $value)[1], ';');
			$startDate = \Craft\DateTime::createFromFormat('Ymd\THis', $startDateTime);

			if( strpos($startDateTime, 'T') ) {
				$startTimeStr = date('His', strtotime($startDateTime));
				$startTime = \Craft\DateTime::createFromFormat('His', $startTimeStr);
			}
			else{
				$startTime = null;
			}

			$fieldValues['repeats'] = false;
			$fieldValues['dates'][] = $startDate;
		}
		else{
			$startDate = null;
			$startTime = null;
			$fieldValues['repeats'] = false;
		}

		$fieldValues['startdate'] = $startDate;
		$fieldValues['starttime'] = $startTime;

		//If an end date was set
		if( strpos($value, "DTEND") !== false ){
			$endDateTime = explode(';', explode( 'DTEND=', $value)[1])[0];
			$endDate = \Craft\DateTime::createFromFormat('Ymd\THis', $endDateTime);
			
			//If an end time was set
			if( strpos($endDateTime, 'T') ) {
				$endTimeStr = date('His', strtotime($endDateTime));
				$endTime = \Craft\DateTime::createFromFormat('His', $endTimeStr);
			}
			else{
				$endTime = null;
			}

			$fieldValues['enddate'] = $endDate;
			$fieldValues['endtime'] = $endTime;
		}
		else{
			$fieldValues['enddate'] = null;
			$fieldValues['endtime'] = null;
		}

		if( strpos($value, "COUNT") !== false ){
			$fieldValues['ends'] = 'after';
			$fieldValues['occurrences'] = $rule->getCount();
		}
		elseif( strpos($value, "UNTIL") !== false ){
			$fieldValues['ends'] = 'until';
			$untilDateStr = $rule->getUntil()->format('Ymd');
			$untilDate = \Craft\DateTime::createFromFormat('Ymd', $untilDateStr);
			$fieldValues['untildate'] = $untilDate;
		}

		if( strpos($value, "ALLDAY") !== false ){
			$fieldValues['allday'] = true;
		}

		return $fieldValues;
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
		$ends = $value['ends']; //how it ends (never, after, until)
		$count = $value['occurrences']; // if ending occurs amounts
		$untilDate = $value['untildate']['date']; // if ending until date

		$dbString = '';


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
				$dbString .= 'DTSTART=' . date('Ymd\THis', $time) . ';' . $rule->getString();
			}
			else{
				$time = strtotime($startDate);
				$dbString .= 'DTSTART=' . date('Ymd', $time) . ';' . $rule->getString();
			}
		}
		else{
			if($startTime){
				$time = strtotime($startDate . ' ' . $startTime);
				$dbString .= 'DTSTART=' . date('Ymd\THis', $time) . ';';
			}
			else{
				$time = strtotime($startDate);
				$dbString .= 'DTSTART=' . date('Ymd', $time) . ';';
			}		
		}

		if($endDate){
			if($endTime){
				$time = strtotime($endDate . ' ' . $endTime);
				$dbString = 'DTEND=' . date('Ymd\THis', $time) . ';' . $dbString;
			}
			else{
				$time = strtotime($endDate);
				$dbString .= 'DTEND=' . date('Ymd', $time) . ';' . $dbString;
			}
		}

		if($allday){
			$dbString = 'ALLDAY;' . $dbString;
		}

		return $dbString;
	}
}