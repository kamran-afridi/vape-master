// $(function () {
//     "use strict";

//     /**
//      * Generating PDF from HTML using jQuery
//      */
//     $(document).on("click", "#invoice_download_btn", function () {
//         var contentWidth = $("#invoice_wrapper").width();
//         var contentHeight = $("#invoice_wrapper").height();
//         var topLeftMargin = 20;
//         var pdfWidth = contentWidth + topLeftMargin * 2;
//         var pdfHeight = pdfWidth * 1.5 + topLeftMargin * 2;
//         var canvasImageWidth = contentWidth;
//         var canvasImageHeight = contentHeight;
//         var totalPDFPages = Math.ceil(contentHeight / pdfHeight) - 1;
//         const dateNow = new Date().toLocaleString().split(",")[0];

//         html2canvas($("#invoice_wrapper")[0], { allowTaint: true }).then(
//             function (canvas) {
//                 canvas.getContext("2d");
//                 var imgData = canvas.toDataURL("image/jpeg", 1.0);
//                 var pdf = new jsPDF("p", "pt", [pdfWidth, pdfHeight]);
//                 pdf.addImage(
//                     imgData,
//                     "JPG",
//                     topLeftMargin,
//                     topLeftMargin,
//                     canvasImageWidth,
//                     canvasImageHeight
//                 );
//                 for (var i = 1; i <= totalPDFPages; i++) {
//                     pdf.addPage(pdfWidth, pdfHeight);
//                     pdf.addImage(
//                         imgData,
//                         "JPG",
//                         topLeftMargin,
//                         -(pdfHeight * i) + topLeftMargin * 4,
//                         canvasImageWidth,
//                         canvasImageHeight
//                     );
//                 }
//                 pdf.save(`invoice-${dateNow}.pdf`);
//             }
//         );
//     });
// });

$(function () {
    "use strict";

    /**
     * Generating PDF from HTML using jQuery without adding extra pages
     */
    // $(document).on("click", "#invoice_download_btn", function () {
    //     var contentWidth = $("#invoice_wrapper").width();
    //     var contentHeight = $("#invoice_wrapper").height();
    //     var topLeftMargin = 20;
    //     var pdfWidth = contentWidth + topLeftMargin * 2;
    //     var pdfHeight = contentHeight + topLeftMargin * 2; // Adjusted to fit content height
    //     const dateNow = new Date().toLocaleString().split(",")[0];

    //     html2canvas($("#invoice_wrapper")[0], { allowTaint: true }).then(
    //         function (canvas) {
    //             canvas.getContext("2d");
    //             var imgData = canvas.toDataURL("image/jpeg", 1.0);
    //             var pdf = new jsPDF("p", "pt", [pdfWidth, pdfHeight]);
    //             pdf.addImage(
    //                 imgData,
    //                 "JPG",
    //                 topLeftMargin,
    //                 topLeftMargin,
    //                 contentWidth,
    //                 contentHeight
    //             );
    //             pdf.save(`invoice-${dateNow}.pdf`);
    //         });
    // });
    $(document).on("click", "#invoice_download_btn", function () {
    const invoice = document.getElementById("invoice_wrapper");
    const dateNow = new Date().toLocaleDateString();

    html2canvas(invoice, { scale: 2, useCORS: true }).then(function (canvas) {
        const imgData = canvas.toDataURL("image/jpeg", 1.0);

        const pdf = new jsPDF("p", "pt", "a4");

        // Support both old and new versions of jsPDF
        const pdfWidth = pdf.internal.pageSize.getWidth
            ? pdf.internal.pageSize.getWidth()
            : pdf.internal.pageSize.width;
        const pdfHeight = pdf.internal.pageSize.getHeight
            ? pdf.internal.pageSize.getHeight()
            : pdf.internal.pageSize.height;

        const imgWidth = canvas.width;
        const imgHeight = canvas.height;
        const ratio = pdfWidth / imgWidth;
        const scaledHeight = imgHeight * ratio;

        let heightLeft = scaledHeight;
        let position = 0;

        // First page
        pdf.addImage(imgData, "JPEG", 0, position, pdfWidth, scaledHeight);
        heightLeft -= pdfHeight;

        // Extra pages if needed
        while (heightLeft > 0) {
            position = heightLeft - scaledHeight;
            pdf.addPage();
            pdf.addImage(imgData, "JPEG", 0, position, pdfWidth, scaledHeight);
            heightLeft -= pdfHeight;
        }

        pdf.save(`Invoice-${dateNow}.pdf`);
    });
});


});