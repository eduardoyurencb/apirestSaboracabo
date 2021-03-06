<?php

class EmaillHandler {
	/**
 		* This example shows settings to use when sending via Google's Gmail servers.
 	*/

	//SMTP needs accurate times, and the PHP time zone MUST be set
	//This should be done in your php.ini, but this is how to do it if you don't have access to that
	
	private $mail;
	
	function __construct() {
		date_default_timezone_set('Etc/UTC');
		require 'PHPMailerAutoload.php';
	}

	function constructEmail($nombre, $email, $idCompra) {
		//Create a new PHPMailer instance
		$mail = new PHPMailer;
		
		//Tell PHPMailer to use SMTP
		$mail->isSMTP();

		//Enable SMTP debugging
		// 0 = off (for production use)
		// 1 = client messages
		// 2 = client and server messages
		$mail->SMTPDebug = 0;

		//Ask for HTML-friendly debug output
		//$mail->Debugoutput = 'html';

		//Set the hostname of the mail server
		$mail->Host = 'quuos.com';
		// use
		// $mail->Host = gethostbyname('smtp.gmail.com');
		// if your network does not support SMTP over IPv6

		//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
		$mail->Port = 25;

		//Set the encryption system to use - ssl (deprecated) or tls
		$mail->SMTPSecure = 'tls';

		//Whether to use SMTP authentication
		$mail->SMTPAuth = true;

		$mail->SMTPOptions = array(
    	'ssl' => array(
        	'verify_peer' => false,
        	'verify_peer_name' => false,
        	'allow_self_signed' => true
    	)
);

		//Username to use for SMTP authentication - use full email address for gmail
		$mail->Username = "prueba1@quuos.com";

		//Password to use for SMTP authentication
		$mail->Password = "loscabos2017";

		//Set who the message is to be sent from
		$mail->setFrom('prueba1@quuos.com', 'SaborACabo');

		//Set an alternative reply-to address
		//$mail->addReplyTo('eduardoyurencb@gmail.com', 'First Last');

		//Set who the message is to be sent to
		$mail->addAddress($email, $nombre);

		//Set the subject line
		$mail->Subject = 'Entradas SaborACabo2017';

		//Read an HTML message body from an external file, convert referenced images to embedded,
		//convert HTML into a basic plain-text alternative body
		$mail->msgHTML(file_get_contents(dirname(__FILE__).'/contents.html'), dirname(__FILE__));

		//Replace the plain text body with one created manually
		$mail->AltBody = 'Confirmación de boletos';

		//Attach an image file
		//$mail->addAttachment(dirname(__FILE__).'/phpmailer_mini.png', 'My uploaded file');
		// /Applications/XAMPP/xamppfiles/htdocs/ws/include/email/gmail.php
		$mail->addAttachment(dirname(__FILE__).'/../../facturas/archivo_facturas/'.$idCompra.'.pdf', 'Entradas Sabor a Cabo');
		//$mail->addAttachment('/Applications/XAMPP/xamppfiles/htdocs/ws/include/email/../../facturas/archivo_facturas/6.pdf', 'Entradas Sabor a Cabo');

		//send the message, check for errors
	
		if (!$mail->send()) {
		    //echo "Mailer Error: " . $mail->ErrorInfo;
		} else {
		    //echo "Message sent!";
		}
	}
}





