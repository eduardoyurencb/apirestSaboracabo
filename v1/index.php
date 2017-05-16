<?php
 
require_once '../include/DbHandler.php';
//require_once '../include/PassHash.php';
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

$app->put('/asientos/reservar', function() use($app) {
            $cuerpo = file_get_contents('php://input');
            $jsonRequest = json_decode($cuerpo);
            
            /*
                foreach ($jsonRequest as &$valor) {
                    $idAsiento = $valor->idAsiento;
                    $idMesa    = $valor->idMesa;
                }
            */

            $db = new DbHandler();
            $response = array();
 
            $result = $db->reservarAsientos($jsonRequest);
            
            if ($result) {
                $response["error"] = false;
                $response["mensaje"] = "Asientos reservados correctamente";
            } else {
                $response["error"] = true;
                $response["mensaje"] = "Ha ocurrido un error al momento de reservar los asientos";
            }

            echoRespnse(200, $response);
            
});

$app->put('/asientos/comprar', function() use($app) {
            $cuerpo = file_get_contents('php://input');
            $jsonRequest = json_decode($cuerpo);

            $db = new DbHandler();
            $response = array();
 
            $result = $db->comprarAsientos($jsonRequest);
            
            if ($result) {
                $response["error"] = false;
                $response["mensaje"] = "Asientos comprados correctamente";
            } else {
                $response["error"] = true;
                $response["mensaje"] = "Ha ocurrido un error al momento de comprar los asientos";
            }
            echoRespnse(200, $response);
});

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

$app->post('/compra/registrar', function() use ($app) {
            // check for required params
            //verifyRequiredParams(array('nombre', 'apellidos'));
 
            $response = array();
            $cuerpo = file_get_contents('php://input');
            $jsonRequest = json_decode($cuerpo);
            print_r($jsonRequest);

            $nombre    = $jsonRequest->nombre;
            $apellido  = $jsonRequest->apellido;
            $email     = $jsonRequest->email;

          
            // validating email address
            //validateEmail($email);
 
            $db = new DbHandler();
            
            $result = $db->registrarCompra($nombre, $apellido, $email);
            $result = $result->fetch_assoc();


            if($result["codigo_respuesta"] == '0'){
        
                $idCompra = $result["id_compra"];
                print_r("idcompraaaa" . $idCompra);
                $asientosComprados = $jsonRequest->asientos;
                print_r($asientosComprados);
                $result = $db->comprarAsientos($asientosComprados);
                /**
                * Cambiar esto cuando se haga el store procedure
                */
                if ($result) {
                    $result = $db->registrarDetalleCompra($asientosComprados, $idCompra);
                    $response["error"] = false;
                    $response["mensaje"] = "Asientos comprados correctamente";
                } else {
                    $response["error"] = true;
                    $response["mensaje"] = "Ha ocurrido un error al momento de comprar los asientos";
                }
                    echoRespnse(200, $response);
            }else{
                $response["error"] = true;
                $response["mensaje"] = $result["mensaje_respuesta"];
                $response["codigo_respuesta"] =  $result["codigo_respuesta"];
                echoRespnse(200, $response);   
            }   
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