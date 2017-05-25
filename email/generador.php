<?php
/* http://programarenphp.wordpress.com */
/* incluimos primeramente el archivo que contiene la clase fpdf */
include ('fpdf.php');
/* tenemos que generar una instancia de la clase */
        $pdf = new FPDF();
        $pdf->AddPage();

/* seleccionamos el tipo, estilo y tamaño de la letra a utilizar */
        $pdf->SetFont('Arial', 'B', 14);
		$pdf->Write (7,"HOLA ESTOY GENERANDO MI PRIMER PDF  ","http://programarenphp.wordpress.com");
		$pdf->Ln();
		//$pdf->Write (7,$_POST['nombre']);
		$pdf->Ln(); //salto de linea
		//$pdf->Cell(60,7,$_POST['direccion'],1,0,'C');
		$pdf->Ln(15);//ahora salta 15 lineas 
		$pdf->SetTextColor('255','0','0');//para imprimir en rojo 
		//$pdf->Multicell(190,7,$_POST['tel']."\n esta es la prueba del multicell",1,'R');
		$pdf->Line(0,160,300,160);//impresión de linea
        $pdf->Output("prueba.pdf",'F');
		echo "<script language='javascript'>window.open('prueba.pdf','_self','');</script>";//para ver el archivo pdf generado
		exit;
	?>
