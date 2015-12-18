(function() {
  $(document).ready(function() {
    var $afterRecords = $(".after-records");
    var $addRecord = $(".add-record");
    var $deleteRecordButtons = $(".delete-record");
    var $saveAllRecords = $(".save-all-records");

    $addRecord.on("click", function() {
      var $newRecord = newRecord();
      $newRecord.insertBefore($afterRecords);
    });

    $saveAllRecords.on("click", function() {
      saveAllForms($afterRecords.parent(), "index.php", "saveAll", $(this).attr("data-table"));
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
      //$.extend({}, $form.serializeArray(), { action: action, table: $form.attr("data-table") }),
      { form: $form.serializeArray(), action: action, table: $form.attr("data-table") },
      function(data) {
        alert("success!");
        $("html").html(data);
      }
    );
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
        alert("success!");
        $("html").html(data);
      });
  }
})(jQuery);