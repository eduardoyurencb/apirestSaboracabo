<?php
if(!defined("SPECIALCONSTANT")) die("Acceso denegado");

$app->get("/mesas/asientos", function() use($app){
	try{
		$connection = getConnection();
		$dbh = $connection->prepare("SELECT * FROM mesa");
		$dbh->execute();
		$queryResult = $dbh->fetchAll();
		$connection = null;

		//echo json_encode($queryResult, true);


/*
		$json = '{"countryId":"84","productId":"1","status":"0","opId":"134"}';
		$json = json_decode($json, true);
		echo $json['countryId'];
		echo $json['productId'];
		echo $json['status'];
		echo $json['opId'];
		*/

		$app->contentType('application/json');
		foreach ($queryResult as &$valor) {
			$mesaJson = json_decode(json_encode($valor), true);
			echo $mesaJson['idMesa'];
		}

		
	
		
		//$app->response->headers->set("Content-type", "application/json");
		//$app->response->status(200);
		
		//$app->response->body(json_encode($books));
/*
		$response = array();
		$response["error"] = false;
		$response["message"] = "Mesas: " . count($mesas);
		//$response["mesas"] = $mesas;
		echoResponse(200, $response);
		*/

	}catch(PDOException $e){
		echo "Error: " . $e->getMessage();
	}
});


/**
* Mostrando la respuesta en formato json al cliente o navegador
* @param String $status_code Http response code
* @param Int $response Json response
*/
function echoResponse($status_code, $response) {
	$app = \Slim\Slim::getInstance();
	// Http response code
	$app->status($status_code);
	// setting response content type to json
	$app->contentType('application/json');
	echo json_encode($response);
}
