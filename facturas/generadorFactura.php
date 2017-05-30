<?php

class GeneradorFactura{
	function __construct() {
		include (dirname(__FILE__).'/fpdf.php');	
	}

	function generateBill($nombre, $email, $numeroCompra, $asientos){
		$pdf = new FPDF('P','mm','Letter');
        $pdf->AddPage();
        $pdf->SetXY(16,25);
        $pdf->SetFillColor(224, 224, 224);
        $pdf->Cell(184, 230, '', 0, 0, 'C', True);
        $pdf->Image(dirname(__FILE__).'/saboracabo.png',70,38,-125);

        $pdf->SetXY(26,90);
        $pdf->SetFillColor(204, 0, 102);
        $pdf->Cell(164, 1, '', 0, 0, 'C', True);
        
        $pdf->SetXY(50,100);
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor('204','0','102');
        $pdf->Write (7,"¡ Ya has adquirido con exito tus boletos !");

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetXY(45,110);
     	$pdf->SetTextColor('128','128','128');
        $pdf->Multicell(126, 5, "Esto es una confirmación de compra. Para acceder al evento debes presentar esta confirmación o saber tu número de compra y datos del comprador.",
         0, 'C', False);
        
        $pdf->SetXY(36,130);
        $pdf->SetFillColor(204, 0, 102);
        $pdf->Cell(144, 1, '', 0, 0, 'C', True);

        $pdf->SetXY(50,140);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor('204','0','102');
        $pdf->Cell(40, 5, 'Número de compra:', 0, 0, 'L', False);

        $pdf->SetXY(50,147);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetTextColor('128','128','128'); //para imprimir en gris 
        $pdf->Cell(40, 5, $numeroCompra, 0, 0, 'L', False);


        $pdf->SetXY(36,160);
        $pdf->SetFillColor(204, 0, 102);
        $pdf->Cell(144, 1, '', 0, 0, 'C', True);

        $pdf->SetXY(50,168);
        $pdf->SetFont('Arial', 'B', 13);
        $pdf->SetTextColor('128','128','128'); //para imprimir en gris 
        $pdf->Cell(40, 5, 'Datos de la compra', 0, 0, 'L', False);

        $pdf->SetXY(55,177);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor('128','128','128'); //para imprimir en gris 
        $pdf->Cell(60, 5, '* Nombre : '.$nombre, 0, 0, 'L', False);

        $pdf->SetXY(55,182);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor('128','128','128'); //para imprimir en gris 
        $pdf->Cell(60, 5, '* Email: '.$email, 0, 0, 'L', False);

        $pdf->SetXY(55,187);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor('128','128','128'); //para imprimir en gris 
        $pdf->Cell(60, 5, '* Asientos: '.$asientos, 0, 0, 'L', False);

        $pdf->Output(dirname(__FILE__)."/archivo_facturas/".$numeroCompra.".pdf",'F');
		//echo "<script language='javascript'>window.open('ssssprueba.pdf','_self','');</script>";//para ver el archivo pdf generado
		//exit;
	} 
}        
?>
