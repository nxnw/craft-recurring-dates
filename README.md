craft-recurring-dates
=====================

### I could still use help testing and bug fixing. If you find any issues put them in the Github issues. Thanks

This is a plugin for the Craft CMS to add recurring dates functionality. It adds a new field type called Advanced Date which has the recurring date functions. It stores the recurring dates and an rule in the data base and returns an array of dates to the twig templates. I'm using the [Recurr library](https://github.com/simshaun/recurr) by [simshaun](https://github.com/simshaun) to build and parse the rrule.

###Installation

1. Clone this project into `your_craft_dir/craft/plugins/recurringdate`
2. Install Composer from [getcomposer.org](https://getcomposer.org/doc/00-intro.md#installation-nix) 
3. Run `composer install` or `php composer.phar install` in the `recurringdate` directory to install dependencies
4. Install the plugin through the Craft admin panel

###Usage

* Add a new Advanced Date Field type to one of your sections 
* Output is either an array of dates and the entries associated with them or an array of the values used to set the date rule. Output depends on how you query the dates
* Multiple Dates Query can be used to automatically sort and filter corresponding dates. I built this with an event calendar in mind, that would need recurring dates.

####Example Output Usage - Multiple Dates Query

```
{% set query = craft.entries.section('events').relatedTo(craft.categories.slug('your-slug')) %}
{% set events = craft.recurringdate.dates('yourFieldHandle',
	{ 
		'limit': 3, 
		'before': '12/22/2014', 
		'after': 'now +1 months', 
		'criteria': query
	}) 
%}

{% for event in events %}
	  {{ event.date.start|date("n/j/Y") }}{{ event.date.end ? ' -- ' ~ event.date.end|date("n/j/Y") }}<br>
{% endfor %}
```

####Arguments for `craft.recurringdate.dates`
* Field Handle - Handle of your field in the Craft CP

Values in the options array - see above example for usage
* `limit` - limit the number of entries returned
* `order` - 'ASC' or 'DESC' - defaults to 'ASC'
* `group` - null, 'day', 'month', or 'year' - See Grouping Info Below
* `before` - null, or Date string accepted by PHP's [strtotime function](http://www.php.net/manual/en/datetime.formats.php)
* `after` - null, or Date string accepted by PHP's strtotime function 
* `criteria` - ElementCriteriaModel returned by a craft entry query
* `excludes` - if excluded dates should be respected - defaults to true

####Properties available for output for Multiple Dates Query
* `date` - Array containing all the date info for the recurring date
  * `id` - Id of the date, used to query the correct date in a sequence
  * `elementId` - Id of the Craft entry associated with this date 
  * `start` - A string containing the start date
  * `end` - A string containing the end date, if set
  * `start_time` - A string containing the start time, if set
  * `end_time` - A string containing the end time, if set
  * `allday` - If the date lasts all day
  * `repeats` - If the date repeats
  * `rrule` - The RRULE string used to build the date
* `entry` - The entry associated with this date

####Info about using Group for Multiple Dates Query
The Group query will return a different structure than a regular query. It's structure looks like this
```
  array(
	    'grouped_date_string' = array(
		      array('date', 'entry'),
		      array('date', 'entry'),
		      ...
	    ),
	    'grouped_date_string2' = array(
		      array('date', 'entry'),
		      array('date', 'entry')
	    ),
	    ...
  )
```

####Example Output Usage - Single Date Query using Date ID
```
 {% set row = craft.recurringdate.date(id) %}
 {% set date = row.date %}
 {% set entry = row.entry %}
 {{ entry.title }}<br>
 {{ date.start_date|date("n/j/Y H:i:s") }}{{ date.end_date is defined ? ' -- ' ~ date.end_date|date("n/j/Y H:i:s") }}
```

####Properties available for output for Single Date Query
The Single Date Query will only return one array that contains the `date` and the `entry` of the row with the specified ID. The idea is that you would use the multiple date query to list the date's ID and then you can select which specific date you want to display based on that ID. 


####Example Output Usage - Single Entry
```
  {% set date = entry.advanced_date_field_name %}
  {{ date.start_date|date("n/j/Y H:i:s") }}{{ date.end_date is defined ? ' -- ' ~ date.end_date|date("n/j/Y H:i:s") }}
```

####Properties available for output for each Advanced Date Field - Single Entry
* `start_date` - Craft DateTime Object - Start date set for the event 
* `start_time` - Craft DateTime Object - Same as startdate
* `end_date` - Craft DateTime Object - End date set for the event, can be null if not set 
* `end_time` - Craft DateTime Object - Same as enddate
* `allday` - Boolean - If the event is an allday event
* `repeats` - Boolean - If the event repeats
* `frequency` - String - Recurrence frequency, 'daily', 'weekly', 'monthly', 'yearly'
* `interval` - Int - Recurrence Interval, integer from 1-31
* `weekdays` - Array - days of the week that the event occurs, undefined if interval not weekly, 'SU', 'MO', 'TU', etc...
* `repeat_by` - String - by day of week or day of month, undefined if interval not monthly, 'week', 'month'
* `ends` - String - how the event ends, 'never', 'after', 'until'
* `count` - Int - How many times this occurs, undefined if `ends` is not 'after'
* `until` - Craft DateTime Object - Date event goes until, undefined if `ends` is not 'until'
* `rrule` - RRULE string used to build the rule
