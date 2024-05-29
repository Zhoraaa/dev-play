$(document).ready(function() {
  $("#search").on("input", function() {
    var filter = $(this).val().toLowerCase();

    $(".searchable").each(function() {
      var label = $(this).find(".criteria").text().toLowerCase();
      if (label.includes(filter)) {
        $(this).removeClass("hidden");
      } else {
        $(this).addClass("hidden");
      }
    });
  });
});