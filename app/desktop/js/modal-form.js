$(document).ready(function () {
    setTimeout(function () {
        $(".modal").show();
        $("#cr-modal").show();
    }, 600);

    $(".modal, .closeBtn").on("click touchstart touchend", function () {
        $("#cr-modal").hide();
        $(".modal").hide();
    });
});