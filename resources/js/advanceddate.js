window.advancedDate = function(id) {

  var advancedDateView = function(namespace) {

    var _namespace = namespace;
    var _root = $('#' + namespace + '-field');

    var _init = function() {
      _allDayToggle();
      _repeatToggle();
      _repeatInterval();
      _repeatEnds();
    };

    var _allDayToggle = function() {

      var startTime = _root.find('.field.starttime .starttime-time');
      var endTime = _root.find('.field.endtime .endtime-time');
      var alldaySwitch = _root.find('.allday-switch .lightswitch').data('lightswitch');

      var changeHandler = function() {

        if (alldaySwitch.on) {

          startTime.hide();
          endTime.hide();

        } else {

          startTime.show();
          endTime.show();
        }

      };

      alldaySwitch.settings.onChange = changeHandler;
      changeHandler();


    };

    var _repeatToggle = function() {

      var repeatHolder = _root.find('.repeat-holder');
      var repeatsSwitch = _root.find('.repeats-switch .lightswitch').data('lightswitch');

      var changeHandler = function() {

        if (repeatsSwitch.on) {
          repeatHolder.show();
        } else {
          repeatHolder.hide();
        }

      };

      repeatsSwitch.settings.onChange = changeHandler;
      changeHandler();

    };

    var _repeatInterval = function() {

      var repeatSelect = _root.find('#' + _namespace + 'repeat-interval');

      var repeatOn = _root.find('.field.on');
      var repeatBy = _root.find('.field.by');

      var repeatEveryUnit = _root.find('.repeat-every-unit');

      var changeHandler = function() {

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

      };

      repeatSelect.on('change', changeHandler);
      repeatSelect.trigger('change');

    };

    var _repeatEnds = function() {

      var repeatEndsSelect = _root.find('#' + _namespace + 'repeat-ends');
      var repeatEndOccurrences = _root.find('.field.occurrences');
      var repeatEndUntil = _root.find('.field.until');

      var changeHandler = function() {
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
      };

      repeatEndsSelect.on('change', changeHandler);
      repeatEndsSelect.trigger('change');

    };

    _init();

  };

  return {
    create: function(id) {
      $('#' + id + '-field').data('ad', new advancedDateView(id));
    }
  };

<<<<<<< HEAD
window.advancedDate = function(namespace) {
  console.log(namespace);
  $('.' + namespace + '-field').each(function() {
    var el = $(this);
    console.log(el);
    var id = el.attr('id');
    el.data('ad', new advancedDateView(id));
  });
};
=======
}();

$(function() {
  window.advancedDate.create('fields-ad1');
});
>>>>>>> FETCH_HEAD
