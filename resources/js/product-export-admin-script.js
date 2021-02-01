function generateReport() {
    jQuery.ajax({
        type: "POST",
        dataType: "json",
        url: ajaxurl,
        data: {
            action: "generate_report",
            month: new Date(jQuery("#eg_product_export_month").val()).getMonth(),
            year: new Date(jQuery("#eg_product_export_month").val()).getFullYear()
        },
        success: function (data) {
            // console.log(data);
            buildDownloadsTable(data.url)
            // downloadDataUrlFromJavascript(data.filename, data.url);
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

function buildDownloadsTable(urlArray) {
    console.log('ARRAY', urlArray);
    
    // get Month string and year
    const date = new Date(jQuery("#eg_product_export_month").val());
    const monthStr = date.toLocaleString('default', { month: 'long' });
    const year = date.toLocaleString('default', { year: 'numeric' });
    jQuery("h2 span.month_year").text(monthStr + " " + year);
    
    jQuery(".form-table.csv-list").removeClass('hidden');

    urlArray.forEach(file => {
        if (file[2] > 0) {
            jQuery(".form-table.csv-list a.csv_" + file[0])
                .attr('href', file[1])
                .removeAttr('disabled')
                .text('Download CSV');
        } else {
            jQuery(".form-table.csv-list a.csv_" + file[0])
                .attr('disabled', 'disabled')
                .removeAttr('href')
                .text('No entries found');
        }
    });
}

jQuery(document).ready(
    (function ($) {
        $("#generate-report").click(function (e) {
            e.preventDefault();
            generateReport();
        });
    })(jQuery)
);
