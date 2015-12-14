(function() {
  $(document).ready(function() {
    var $afterRecords = $(".after-records");
    var $addRecord = $(".add-record");
    var $saveRecordButtons = $(".save-record");
    var $deleteRecordButtons = $(".delete-record");

    $addRecord.on("click", function() {
      var $newRecord = newRecord();
      $newRecord.insertBefore($afterRecords);
    });

    $saveRecordButtons.on("click", function() {
      var $form = $(this).parents("form");
      executeForm($form, "save");
    });
  });

  function newRecord() {
    var $newRecord = $("#record-template").clone();
    $newRecord.removeClass("hidden");
    return $newRecord;
  }

  function executeForm($form, action) {
    alert("hi");
    $.post($form.attr("action"),
      $.extend({}, $form.serialize(), { action: action, table: $form.attr("data-table") }),
      function() {
        alert("success!");
      }
    );
  }
})(jQuery);