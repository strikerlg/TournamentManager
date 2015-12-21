(function() {
  $(document).ready(function() {
    var $afterRecords = $(".after-records");
    var $addRecord = $(".add-record");
    var $deleteRecordButtons = $(".delete-record");
    var $saveAllRecords = $(".save-all-records");

    $addRecord.on("click", function() {
      var $newRecord = newRecord();
      $newRecord.addClass("new");
      $newRecord.insertBefore($afterRecords);
    });

    $saveAllRecords.on("click", function() {
      //saveAllForms($afterRecords.parent(), "index.php", "saveAll", $(this).attr("data-table"));

    });
  });

  function newRecord() {
    var $newRecord = $("#record-template").clone();
    $newRecord.removeClass("hidden");
    return $newRecord;
  }

  function saveAllForms($formContainer, url, action, table) {
    var allForms = [];
    var $allForms = $formContainer.find("form");

    $allForms.each(function() {
      var $form = $(this);
      var formData = $form.serializeArray();
      allForms.push(formData);
    });

    $.post(url,
      { forms: allForms, action: action, table: table },
      function(data) {
        $("html").html(data);
      });
  }

  function allInsertForms($formContainer) {
    return $formContainer.find(".mf-form.new");
  }
})(jQuery);