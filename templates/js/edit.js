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
      var url = "index.php";
      var table = $(this).attr("data-table");
      $formContainer = $afterRecords.parent();

      saveAllForms(allInsertForms($formContainer), url, "saveAll", table);
      saveAllForms(allUpdateForms($formContainer), url, "updateAll", table);

      location.reload();
    });
  });

  function newRecord() {
    var $newRecord = $("#record-template").clone();
    $newRecord.removeClass("hidden");
    $newRecord.attr("id", "");
    return $newRecord;
  }

  function saveAllForms($allForms, url, action, table) {
    var allForms = [];

    $allForms.each(function() {
      var $form = $(this);
      var formData = $form.serializeArray();
      allForms.push(formData);
    });

    if (allForms.length <= 0) {
      return;
    }

    $.post(url,
      { forms: allForms, action: action, table: table },
      function(data) {
        //$("html").html(data);
      });
  }

  function allInsertForms($formContainer) {
    return $($formContainer.find(".mf-form.new"));
  }

  function allUpdateForms($formContainer) {
    return $($formContainer.find(".mf-form").not(".new").not(".hidden"));
  }
})(jQuery);