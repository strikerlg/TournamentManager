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
      var table = $("body").attr("data-table");
      $formContainer = $afterRecords.parent();

      saveAllForms(allInsertForms($formContainer), HANDLER_URL, "saveAll", table, function(data) {
        console.log(data);
      });

      saveAllForms(allUpdateForms($formContainer), HANDLER_URL, "updateAll", table,
      function(data) {
        console.log(data);
        location.reload();
      });
    });
  });

  function onDeleteClick($sender) {
    var $form = $($sender.parents("form"));

    if($form.hasClass("new")) {
      $form.remove();
    }
    else {
      if ($form.hasClass("marked-delete")) {
        $form.removeClass("marked-delete");
        $form.find(".delete-record").attr("value", "Löschen");
      }
      else {
        $form.addClass("marked-delete");
        $form.find(".delete-record").attr("value", "Nicht löschen");
      }
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

  function saveAllForms($allForms, url, action, table, completeHandler) {
    var allForms = [];

    $toDeleteForms = $allForms.filter(".marked-delete");
    if ($toDeleteForms.length > 0) {
      if (confirm("Wollen Sie " + $toDeleteForms.length + " Datensatz(e) wirklich endgültig löschen?")) {
        $toDeleteForms.each(function() { deleteForm($(this)); });
      }
      else {
        if (!confirm("Wollen Sie mit dem aktuallisieren der restlichen Datensätze fortfahren?")) {
          return;
        }
      }
    }

    $allForms.each(function() {
      var $form = $(this);
      var formData = $form.serializeArray();
      allForms.push(formData);
    });

    if(allForms.length <= 0) {
      return;
    }

    $.post(url,
      {forms: allForms, action: action, table: table},
    completeHandler || function(){});
  }

  function deleteForm($form) {
    var id = $form.find(".field-id").val();
    $.post(HANDLER_URL,
      {id: id, table: $("body").attr("data-table"), action: "deleteRecord"},
    function() {
      $form.remove();
    });
  }

  function allInsertForms($formContainer) {
    return $($formContainer.find(".mf-form.new"));
  }

  function allUpdateForms($formContainer) {
    return $($formContainer.find(".mf-form").not(".new").not(".hidden"));
  }
})(jQuery);