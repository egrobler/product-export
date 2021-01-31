function generateReport() {
    jQuery.ajax({
        type: "POST",
        dataType: "json",
        url: ajaxurl,
        data: {
            action: "generate_report",
            startDate: new Date(jQuery("#eg_product_export_start_date").val()),
            endDate: new Date(jQuery("#eg_product_export_end_date").val())
        },
        success: function (data) {
            console.log(data);
            //downloadDataUrlFromJavascript(data.filename, data.url);
        }
    });
}

function downloadDataUrlFromJavascript(filename, dataUrl) {
    // Construct the 'a' element
    var link = document.createElement("a");
    link.download = filename;
    link.target = "_blank";

    // Construct the URI
    link.href = dataUrl;
    document.body.appendChild(link);
    link.click();

    // Cleanup the DOM
    document.body.removeChild(link);
    delete link;
}

jQuery(document).ready(
    (function ($) {
        $("#generate-report").click(function (e) {
            e.preventDefault();
            generateReport();
        });
    })(jQuery)
);
