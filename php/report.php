<?php

/**
 * Set content header
 */
//header('Content-type: application/pdf');
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

/**
 * Load Dependecies
 */
require('fpdf17/fpdf.php');
require('gft.php');


/**
 * Get form variables
 */
$neighborhood = $_REQUEST['n'];
$measures = explode(",", urldecode($_REQUEST['m']));

//echo($measures[0]);


/**
 * Load data JSON
 */
$string = file_get_contents("../js/metrics.json");
$json = json_decode($string, true);

 
/**
 * Load neighborhood information from Google Fusion Tables
 */
if (strlen(urldecode($_REQUEST['m'])) > 0) {
    // neighborhood    
    $ft = new googleFusion();
    $gft_neighborhood = $ft->query("select " . $_REQUEST['m'] . " FROM 1844838 WHERE ID = " . $neighborhood);
    
    // county average
    for ($i = 0; $i < count($measures); ++$i) {
        $avg[$i] = "average(" .  $measures[$i] . ") as " . $measures[$i];
    }
    $gft_average = $ft->query("select " . implode(",", $avg) . " FROM 1844838");
    //print_r($gft_average);
}


/**
 * Extend FPDF for header/footer/etc.
 */
class PDF extends FPDF
{

    // Page footer
    function Footer()
    {
        // Position at 1.5 cm from bottom
        $this->SetY(-0.4);
        // Arial italic 8
        $this->SetFont('Arial','I',8);
        $this->SetTextColor(0,0,0); 
        // Page number
        $this->Cell(0,0,'Quality of Life Dashboard - http://maps.co.mecklenburg.nc.us/qoldashboard/',0,0,'C');
    }
}


/**
 * Create PDF
 */
$pdf = new PDF('P','in','Letter');


/************************************************************/
/*                 Cover Page                               */
/************************************************************/
$pdf->AddPage();


// Title page image background
$pdf->Image('report_cover_page.png',0,0,8.5);

// White text on top of title page
$pdf->SetTextColor(255,255,255);

// Title page header
$pdf->SetFont('Arial','B',64);
$pdf->Ln(0.8);
$pdf->Cell(0.3);
$pdf->Cell(0,0, "Neighborhood");

// Title page neighborhood
$pdf->SetFont('Arial','B',180);
$pdf->Ln(1.8);
$pdf->Cell(0.3);
//$pdf->Cell(1.7);
$pdf->Cell(0, 0, $neighborhood); 

// Title page main content
$pdf->Ln(3.8);
$pdf->Cell(1);
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','', 14);
$text = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce elementum tortor vitae tortor dapibus quis porta est fringilla. Etiam vulputate erat id purus elementum scelerisque. Sed id risus nisi, at dapibus nisi. Aliquam in enim eu odio gravida interdum sed sed odio. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Cras ullamcorper ornare augue. Nulla mollis orci quis quam pulvinar semper. Pellentesque interdum libero vitae enim ultrices faucibus. Duis vel enim eget ipsum aliquet sollicitudin in ut massa. In diam lacus, sodales id ornare eu, dictum et sem. Fusce iaculis viverra tortor, et volutpat nulla posuere quis.";
$pdf->MultiCell(5.8, 0.2, $text);


/************************************************************/
/*                 Create Measure Function                           */
/************************************************************/
function createMeasure($x, $y, $themeasure) {

    global $pdf, $json, $gft_neighborhood, $gft_average;
    
    $pdf->SetTextColor(0,0,0);    
    $pdf->SetY($y);
    $pdf->SetX($x);
    $pdf->SetFont('Arial','B',14);    
    $pdf->Write(0, $json[$themeasure][title]);
    $pdf->Ln(0.3);
    $pdf->SetX($x);
    $pdf->SetFont('Arial','',10);
    $pdf->MultiCell(3.5, 0.15, $gft_neighborhood[0][$json[$themeasure]["field"]] . " / " . round($gft_average[0][$json[$themeasure]["field"]]), 0, "L");
    $pdf->Ln(0.2);
    $pdf->SetX($x);
    $pdf->SetFont('Arial','B',10);
    $pdf->Write(0, "What is this Indicator?");
    $pdf->Ln(0.1);
    $pdf->SetX($x);
    $pdf->SetFont('Arial','',10);
    $pdf->MultiCell(3.5, 0.15, $json[$themeasure][description], 0, "L");
    $pdf->Ln(0.2);
    $pdf->SetX($x);
    $pdf->SetFont('Arial','B',10);
    $pdf->Write(0, "Why is this Important?");
    $pdf->Ln(0.1);
    $pdf->SetX($x);
    $pdf->SetFont('Arial','',10);
    $pdf->MultiCell(3.5, 0.15, $json[$themeasure][importance], 0, "L");
    $pdf->Ln(0.2);
    $pdf->SetX($x);
    $pdf->SetFont('Arial','B',10);
    $pdf->Write(0, "Technical Notes");
    $pdf->Ln(0.1);
    $pdf->SetX($x);
    $pdf->SetFont('Arial','',10);
    $pdf->MultiCell(3.5, 0.15, $json[$themeasure][tech_notes], 0, "L");
    $pdf->Ln(0.2);
    $pdf->SetX($x);
    $pdf->SetFont('Arial','B',10);
    $pdf->Write(0, "Data Source");
    $pdf->Ln(0.1);
    $pdf->SetX($x);
    $pdf->SetFont('Arial','',10);
    $pdf->MultiCell(3.5, 0.15, $json[$themeasure][source], 0, "L");
    $pdf->Ln(0.2);
    $pdf->SetX($x);
    $pdf->SetFont('Arial','B',10);
    $pdf->Write(0, "Additional Resources");
    $pdf->Ln(0.1);
    $pdf->SetX($x);
    $pdf->SetFont('Arial','',10);
    $pdf->SetTextColor(0,0,255);
    $pdf->SetFont('','U');
    $pdf->Write(0.2, 'www.fpdf.org','http://www.fpdf.org');
    
}



/************************************************************/
/*                 Data Report                              */
/************************************************************/
// loop for each page - 4 measures per page
if (strlen($measures[0]) > 0) {
    $measureCount = 0;
    for ($i=0; $i < ceil(count($measures) / 4); $i++) {
        // add page    
        $pdf->AddPage();
        
        if ($measures[ $measureCount]) createMeasure(0.5, 0.5, $measures[$measureCount]);
        if ($measures[$measureCount + 1]) createMeasure(4.3, 0.5, $measures[$measureCount + 1]);
        if ($measures[$measureCount + 2]) createMeasure(0.5, 5.8, $measures[$measureCount + 2]);
        if ($measures[$measureCount + 3]) createMeasure(4.3, 5.8, $measures[$measureCount + 3]);
        
        $measureCount = $measureCount + 4;
    }
}



/************************************************************/
/*                 Output PDF Report                        */
/************************************************************/
$pdf->Output();



?>