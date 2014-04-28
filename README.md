craft-recurring-dates
=====================

## Currently doesn't work in the matrix field type

### I could still use help refining logic and error checking. If you find any issues put them the github issues. Thanks

This is a plugin for the Craft CMS to add recurring dates functionality. It adds a new field type called Advanced Date which has the recurring date functions. It stores the date and an rrule in the data base and returns an array of dates to the twig templates. I'm using the [Recurr library](https://github.com/simshaun/recurr) by [simshaun](https://github.com/simshaun) to build and parse the rrule.

###Installation

1. Clone this project into `your_craft_dir/craft/plugins/recurringdate`
2. Install Composer from [getcomposer.org](https://getcomposer.org/doc/00-intro.md#installation-nix) 
3. Run `composer install` or `php composer.phar install` in the `recurringdate` directory to install dependencies
4. Install the plugin through the Craft admin panel

###Usage

* Add a new Advanced Date Field type to one of your sections 
* Output is an array of dates with a start date/time and an end date/time if an end date got set

Example Output Usage
```
  {% for date in entry.advanced_date_field_name.dates %}
	  {{ date.start|date("n/j/Y H:i:s") }}{{ date.end is defined ? ' -- ' ~ date.end|date("n/j/Y H:i:s") }}<br>
  {% endfor %}
```

Properties available for output for each Advanced Date Field
* `dates` - Array of Craft DateTime Objects - Date of recurring events if event recurrs, or single item if not, `dates[i].start` and `dates[i].end`, end can be null if not set by the user
* `startdate` - Craft DateTime Object - Start date set for the event 
* `starttime` - Craft DateTime Object - Same as startdate
* `enddate` - Craft DateTime Object - End date set for the event, can be null if not set 
* `endtime` - Craft DateTime Object - Same as enddate
* `allday` - Boolean - If the event is an allday event
* `repeats` - Boolean - If the event repeats
* `interval` - String - Recurrence frequency, 'daily', 'weekly', 'monthly', 'yearly'
* `every` - Int - Recurrence Interval, integer from 1-31
* `on` - Array - days of the week that the event occurs, undefined if interval not weekly, 'SU', 'MO', 'TU', etc...
* `by` - String - by day of week or day of month, undefined if interval not monthly, 'week', 'month'
* `ends` - String - how the event ends, 'never', 'after', 'until'
* `occurences` - Int - How many times this occurs, undefined if `ends` is not 'after'
* `untildate` - Craft DateTime Object - Date event goes until, undefined if `ends` is not 'until'
