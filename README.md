craft-recurring-dates
=====================

#DO NOT USE - STILL IN DEVELOPMENT!!!!!

This is a plugin for the Craft CMS to add recurring dates functionality. It adds a new field type called Advanced Date which has the recurring date functions. It stores the date and an rrule in the data base and returns an array of dates to the twig templates. I'm using the [Recurr library](https://github.com/simshaun/recurr) by [simshaun](https://github.com/simshaun) to build and parse the rrule.

###Installation

1. Clone this project into `your_craft_dir/craft/plugins/recurringdate`
2. Run `composer install` or `php composer.phar install` in the `recurringdate` directory to install dependencies
3. Install the plugin through the Craft admin panel

###Usage

* Add a new Advanced Date Field type to one of your sections 
