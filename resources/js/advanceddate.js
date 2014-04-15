$(function() {

  //
  // Toggle the time for the start and end of the event
  //

  var startTime = $('.field.starttime .starttime-time');
  var endTime = $('.field.endtime .endtime-time');

  var alldaySwitch = $('#fields-allday-switch .lightswitch').data('lightswitch');
  alldaySwitch.settings.onChange = function() {
    if (alldaySwitch.on) {

      startTime.hide();
      endTime.hide();

    } else {

      startTime.show();
      endTime.show();
    }

  };
  alldaySwitch.settings.onChange();



  //
  //  Toggle the Repeating of the Events
  //

  var repeatHolder = $('#fields-repeat-holder');

  var repeatsSwitch = $('#fields-repeats-switch .lightswitch').data('lightswitch');
  repeatsSwitch.settings.onChange = function() {

    if (repeatsSwitch.on) {
      repeatHolder.show();
    } else {
      repeatHolder.hide();
    }

  };
  repeatsSwitch.settings.onChange();


  //
  //  Handle Updating the Repeat Interval
  //

  var repeatSelect = $('#fields-repeat-interval');

  var repeatOn = $('.field.on');
  var repeatBy = $('.field.by');

  var repeatEveryUnit = $('.repeat-every-unit');

  repeatSelect.on('change', function() {

    $('#fields-repeat-every').val(1);

    repeatOn.hide();
    repeatBy.hide();

    switch (repeatSelect.val()) {
      case "daily":
        repeatEveryUnit.html('days');
        break;
      case "weekly":
        repeatEveryUnit.html('weeks');
        break;
      case "monthly":
        repeatEveryUnit.html('months');
        break;
      case "yearly":
        repeatEveryUnit.html('years');
        break;
    }

    if (repeatSelect.val() === 'weekly') {
      repeatOn.show();
    } else {
      repeatOn.hide();
    }

    if (repeatSelect.val() === 'monthly') {
      repeatBy.show();
    } else {
      repeatBy.hide();
    }

  });

  repeatSelect.trigger('change');


  //
  //  Handle Updating the Repeat Ends Select
  //

  var repeatEndsSelect = $('#fields-repeat-ends');
  var repeatEndOccurrences = $('.field.occurrences');
  var repeatEndUntil = $('.field.until');


  repeatEndsSelect.on('change', function() {

    if (repeatEndsSelect.val() == 'after') {
      repeatEndOccurrences.show();
    } else {
      repeatEndOccurrences.hide();
    }

    if (repeatEndsSelect.val() == 'until') {
      repeatEndUntil.show();
    } else {
      repeatEndUntil.hide();
    }

  });

  repeatEndsSelect.trigger('change');


});