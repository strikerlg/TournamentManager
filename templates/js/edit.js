(function() {
  $(document).ready(function() {
    var $afterRecords = $(".after-records");
    var $addRecord = $(".add-record");

    $addRecord.on("click", function() {
      var $newRecord = newRecord();
      $newRecord.insertBefore($afterRecords);
    });
  });

  function newRecord() {
    var $newRecord = $("#record-template").clone();
    $newRecord.removeClass("hidden");
    return $newRecord;
  }
})(jQuery);