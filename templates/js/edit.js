(function() {
  var HANDLER_URL = "index.php";

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

    $deleteRecordButtons.on("click", function() {
      onDeleteClick($(this));
    });

    $saveAllRecords.on("click", function() {
      var table = $(this).attr("data-table");
      $formContainer = $afterRecords.parent();

      saveAllForms(allInsertForms($formContainer), HANDLER_URL, "saveAll", table);
      saveAllForms(allUpdateForms($formContainer), HANDLER_URL, "updateAll", table);

      //location.reload();
    });
  });

  function onDeleteClick($sender) {
    var $form = $($sender.parents("form"));

    if($form.hasClass("new")) {
      $form.remove();
    }
    else {
      deleteForm($form);
      $form.remove();
    }
  }

  function newRecord() {
    var $newRecord = $("#record-template.hidden").clone();
    $newRecord.removeClass("hidden");
    $newRecord.attr("id", "");
    $newRecord.find(".delete-record").on("click", function() {
      onDeleteClick($(this));
    });
    return $newRecord;
  }

  function saveAllForms($allForms, url, action, table) {
    var allForms = [];

    $allForms.each(function() {
      var $form = $(this);
      var formData = $form.serializeArray();
      allForms.push(formData);
    });

    if(allForms.length <= 0) {
      return;
    }

    $.post(url,
      {forms: allForms, action: action, table: table});
  }

  function deleteForm($form) {
    var id = $form.find(".field-id").val();
    $.post(HANDLER_URL,
      {id: id, table: $form.attr("data-table"), action: "deleteRecord"});
  }

  function allInsertForms($formContainer) {
    return $($formContainer.find(".mf-form.new"));
  }

  function allUpdateForms($formContainer) {
    return $($formContainer.find(".mf-form").not(".new").not(".hidden"));
  }
})(jQuery);