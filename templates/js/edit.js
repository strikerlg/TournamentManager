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
  });

  function newRecord() {
    var $newRecord = $("#record-template").clone();
    $newRecord.removeClass("hidden");
    $newRecord.find(".save-record").on("click", function() {
      var $form = $(this).parents("form");
      executeForm($form, "save");
    });
    return $newRecord;
  }

  function executeForm($form, action) {
    $.post($form.attr("action"),
      $.extend({}, $form.serialize(), { action: action, table: $form.attr("data-table") }),
      function(data) {
        alert("success!");
        $("html").html(data);
      }
    );
  }
})(jQuery);