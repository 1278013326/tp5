<?php


namespace app\peng\controller;


class Pdf extends Base
{
    /**
     * 有错误
     */
    public function index() {

        import('tcpdf/tcpdf', EXTEND_PATH,'.php');
//        $pdf = new \Tcpdf(PDF_PAGE_ORIENTATION, "pt", "A4", true, 'UTF-8', false);


        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('sunnier');
        $pdf->SetTitle('123');
        $pdf->SetSubject('123');
        $pdf->SetKeywords('sunnier');

// set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

// set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
        global $l;
        $pdf->setLanguageArray($l);

// ---------------------------------------------------------

// set font
//        $pdf->SetFont('simfang', '', 10);
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// Print a table

// add a page
        $pdf->AddPage();

// 随便写HTML
        $html = 'adsf';

// output the HTML content
        $pdf->writeHTML($html, true, false, true, false, '');

// reset pointer to the last page
        $pdf->lastPage();
        $pdf->Output('doc.pdf', 'I');
    }
}