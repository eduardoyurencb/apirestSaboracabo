<?php
 
require_once '../include/DbHandler.php';
require_once '../include/email/gmail.php';
require_once '../facturas/generadorFactura.php';

require '.././libs/Slim/Slim.php';
 
\Slim\Slim::registerAutoloader();
 
$app = new \Slim\Slim();
 
// User id from db - Global Variable
$user_id = NULL;
 

/**
 * Regresa todos los asientos con su respectivo estado(L = libre, R = reservado, C = comprado) y demás parámetros.
 */
$app->get('/asientos', function() {

            $response = array();
            $db = new DbHandler();

            // fetching all user tasks
            $result = $db->getAsientos();

            $response["error"] = false;
            $response["numero_asientos"] = $result->num_rows;
            $response["asientos"] = array();
        
            while ($task = $result->fetch_assoc()) {
                $tmp = array();
                $tmp["idAsiento"] = $task["idAsiento"];
                $tmp["idMesa"] = $task["idMesa"];
                $tmp["precio"] = $task["precio"];
                $tmp["fecha_estatus"] = $task["fecha_estatus"];
                $tmp["estatus"] = $task["estatus"];
                array_push($response["asientos"], $tmp);
            }

            echoRespnse(200, $response);     
});

/**
* Regresa la informacion de una compra.
*/

$app->get('/facturas/:id', function($compra_id) use($app) {

    $response = array();
    $db = new DbHandler();
    $result = $db->consultarCompra($compra_id);
    $contador = 0;

    $response["error"] = false;
    $response["num_asientos"] = $result->num_rows;
    $response["id_compra"]  = $compra_id;
    $response["asientos"] = array();

    while ($row = $result->fetch_assoc()) {
        $tmp = array();

        if($contador == 0){
            $response["codigo_respuesta"]   = $row["codigo_respuesta"];
            $response["mensaje_respuesta"]  = $row["mensaje_respuesta"];
            $contador = 1;
        }

        if($row["codigo_respuesta"] ==  0){
            $tmp["id_asiento"] = $row["idAsiento"];
            $tmp["id_mesa"] = $row["idMesa"];
            $tmp["precio"] = $row["precio"];
            array_push($response["asientos"], $tmp);
        }else{
            break;
        }
    }


    echoRespnse(200, $response);
});

/**
 * Cambia los asientos a estatus reservado, mientras el usuario fianliza su compra.
 */
$app->put('/asientos/reservar', function() use($app) {
            $cuerpo = file_get_contents('php://input');
            $jsonRequest = json_decode($cuerpo);
            
            $db = new DbHandler();
            $response = $db->reservarAsientos($jsonRequest);
            echoRespnse(200, $response);        
});


/**
 * Actualiza el estatus de los asientos a L(Libres) cuando el tiempo de espera a expirado.
 */
$app->put('/asientos/liberar/reservados', function() use($app) {
            $db = new DbHandler();
            $response = array();
 
            $result = $db->liberarAsientosReservados();
            $result = $result->fetch_assoc();

            $response["error"] = false;
            $response["mensaje"] = $result["mensaje_respuesta"];
            $response["codigo_respuesta"] =  $result["codigo_respuesta"];
            echoRespnse(200, $response);
});


/**
 * Actualiza el estatus de los asientos a comprados para que ya no puedan ser vendidos.
 */
$app->put('/asientos/comprar', function() use($app) {
    
    $cuerpo = file_get_contents('php://input');
    $jsonRequest = json_decode($cuerpo);

    $db = new DbHandler();        
    $response = $db->comprarAsientos($jsonRequest);
    echoRespnse(200, $response);        
});




/**
 * Registra la venta
 */
$app->post('/compra/registrar', function() use ($app) {
            // check for required params
            //verifyRequiredParams(array('nombre', 'apellidos'));
 
            $newResponse = array();
            $cuerpo = file_get_contents('php://input');
            $jsonRequest = json_decode($cuerpo);

            $nombre    = $jsonRequest->nombre;
            $apellido  = $jsonRequest->apellido;
            $email     = $jsonRequest->email;

            $asientosComprados = $jsonRequest->asientos;

            $db = new DbHandler();
            $response = $db->comprarAsientos($asientosComprados);
            
            print_r($response["codigo_respuesta"]);
            $codRespuesta = $response["codigo_respuesta"];
            if($codRespuesta == "0"){
                print_r("siguiente paso");
                $result = $db->registrarCompra($nombre, $apellido, $email);
                $result = $result->fetch_assoc();

                if($result["codigo_respuesta"] == '0'){
                    print_r("siguiente paso22");
                    $idCompra = $result["id_compra"];
                    $result = $db->registrarDetalleCompra($asientosComprados, $idCompra);

                    $newResponse["error"] = false;
                    $newResponse["mensaje"] = "Asientos comprados correctamente";
                    $newResponse["codigo_respuesta"] =  '0';

                    $asientosString;
                    foreach ($asientosComprados as &$valor) {
                        $idAsiento = $valor->idAsiento;
                        $idMesa    = $valor->idMesa;
                        $asientosString = $asientosString . $idAsiento . $idMesa . ", ";
                    }
                    
                    $dbFactura = new GeneradorFactura();
                    $dbFactura->generateBill($nombre . " " . $apellido , $email, $idCompra, $asientosString);

                    $dbEmail = new EmaillHandler();
                    $dbEmail->constructEmail($nombre, $email, $idCompra);
                    
                    echoRespnse(200, $newResponse);
                }else{
                    $newResponse["error"] = true;
                    $newResponse["mensaje"] = $result["mensaje_respuesta"];
                    $newResponse["codigo_respuesta"] =  $result["codigo_respuesta"];
                    echoRespnse(200, $newResponse);
                }

            }else{
                echoRespnse(200,$response);
            }

});

/**
 * Registra la venta
 */
$app->post('/boleto/enviarEmail', function() use ($app) {
    $dbEmail = new EmaillHandler();
    $dbEmail->constructEmail("Eduardo Yuren", "eduardoyurencb@gmail.com", "6");
});

/**
 * Generar factura
 */
$app->post('/boleto/generar', function() use ($app) {
    $dbFactura = new GeneradorFactura();
    $dbFactura->generateBill('Itzayaba Cabrera', 'itza@gmail.com', '1', '1A1, 1A2');
});



/**
 * Verifying required params posted or not
 */
function verifyRequiredParams($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }
 
    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoRespnse(400, $response);
        $app->stop();
    }
}
 
/**
 * Validating email address
 */
function validateEmail($email) {
    $app = \Slim\Slim::getInstance();
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response["error"] = true;
        $response["message"] = 'Email address is not valid';
        echoRespnse(400, $response);
        $app->stop();
    }
}
 
/**
 * Echoing json response to client
 * @param String $status_code Http response code
 * @param Int $response Json response
 */
function echoRespnse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);
 
    // setting response content type to json
    $app->contentType('application/json');
 
    echo json_encode($response);
}
 
$app->run();
?>