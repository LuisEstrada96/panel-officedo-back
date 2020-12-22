<?php

require('includes/class.phpmailer.php');
require('includes/class.smtp.php');
require 'vendor/autoload.php';

$app = new \Slim\Slim();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Acess-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
define('METHOD','AES-256-CBC');
define('SECRET_KEY','$GDLVAN@2007');
define('SECRET_IV','101712');
$method = $_SERVER['REQUEST_METHOD'];
if($method == "OPTIONS"){
	die();
}

$dbhost = 'localhost';
/*
$dbuser = 'root';
$dbpass = 'root';
$dbname = 'officedo';
*/

$dbuser = 'officedo';
$dbpass = 'Fbxr@800';
$dbname = 'officedo_tienda';

$conn = new mysqli($dbhost, $dbuser, $dbpass,$dbname);
if($conn->connect_error) {
	die('Could not connect1: ' . mysqli_error());
}   
$conn->set_charset('utf8');
function encriptar($string){
	$output=FALSE;
	$key=hash('sha256', SECRET_KEY);
	$iv=substr(hash('sha256', SECRET_IV), 0, 16);
	$output=openssl_encrypt($string, METHOD, $key, 0, $iv);
	$output=base64_encode($output);
	return $output;
}
function desencriptar($string){
	$key=hash('sha256', SECRET_KEY);
	$iv=substr(hash('sha256', SECRET_IV), 0, 16);
	$output=openssl_decrypt(base64_decode($string), METHOD, $key, 0, $iv);
	return $output;
}

function enviarCorreo($emailEnvio, $asunto, $message) {
	
	/*
    $mail = new PHPMailer();
    $mail->Host = 'localhost';
    $mail->From = 'tienda.officedo@gmail.com';
    $mail->FromName = 'OFFICE DO';
    $mail->Subject = $asunto;
    $mail->Body = $message;
    $mail->IsHTML(true);
    $mail->AddAddress($emailEnvio);
    $var = $mail->Send();
*/
    
    $mail = new PHPMailer;
	$mail->From = "tienda.officedo@gmail.com";
	$mail->FromName = "OFFICE DO"; 
	$mail->Subject = $asunto;
	$mail->addAddress($emailEnvio);
	$mail->IsHTML(true);
	$mail->Body = $message;
	$mail->IsSMTP(); 
	$mail->Host = 'ssl://smtp.gmail.com'; 
	$mail->Port = 465; 
	$mail->SMTPAuth = true; 
	$mail->Username = 'tienda.officedo@gmail.com'; 
	$mail->Password = 'officedo2018'; 
	$mail->CharSet = 'UTF-8';
    $var = $mail->Send();
    
    return $var;
    
}


/*---------------------- Pedidos --------------------*/
$app->get('/getOrders/:min/:cant', function ($min,$cant) use($app,$conn) {
	$min = intval($min);
    $sql = "SELECT * FROM pedidos INNER JOIN users ON pedidos.fidUser = users.id  ORDER BY pedidos.id DESC LIMIT $min,$cant";
	$result = $conn->query($sql);
	$info = [];
	while($response = $result->fetch_array()){
		$response['total'] = $response['total'] + ($response['total']*.16);
		$info[] = $response;
	}
    echo json_encode($info);
});

$app->get('/getOrders2', function () use($app,$conn) {
    $sql = "SELECT num_pedido,fecha,total,estatus,nombre_f,visto,cancelado,pedidos.id FROM pedidos INNER JOIN users ON pedidos.fidUser = users.id  ORDER BY pedidos.id DESC";
	$result = $conn->query($sql);
	$info = [];
	while($response = $result->fetch_array()){
		$response[2] = $response[2] + ($response[2]*.16);
		$response[2] = number_format($response[2],2);
		$response[5] = ['visto'=>$response[5],'cancelado'=>$response[6],'id'=>$response[7]];
		unset($response[6]);
		unset($response[7]);
		$info[] = $response;
	}
    echo json_encode($info);
});

$app->post('/getOrdersQuery', function () use($app,$conn) {
	$data = $app->request->post('info');
	$datos = json_decode($data, true);
	$info = [];
	$min = intval($datos['min']);
	$cant = intval($datos['cant']);
    $sql = "SELECT * FROM pedidos INNER JOIN users ON pedidos.fidUser = users.id WHERE num_pedido LIKE '%".$datos['query']."%' OR nombre_f LIKE '%".$datos['query']."%' ORDER BY pedidos.id DESC LIMIT $min,$cant";
	$result = $conn->query($sql);
	
	while($response = $result->fetch_array()){
		$response['total'] = $response['total'] + ($response['total']*.16);
		$info[] = $response;
	}
    echo json_encode($info);
});

$app->get('/getCountOrders', function () use($app,$conn) {
    $sql = "SELECT count(id) FROM pedidos";
	$result = $conn->query($sql);
	$response = $result->fetch_array();
    echo json_encode($response[0]);
});

$app->get('/getCountOrdersQuery/:query', function ($query) use($app,$conn) {
    $sql = "SELECT count(pedidos.id) FROM pedidos INNER JOIN users ON pedidos.fidUser = users.id WHERE num_pedido LIKE '%".$query."%' OR nombre_f LIKE '%".$query."%'";
	$result = $conn->query($sql);
	$response = $result->fetch_array();
    echo json_encode($response[0]);
});

$app->get('/updateVisto/:id', function ($id) use($app,$conn) {
    $sql = "UPDATE pedidos SET visto = 1 WHERE id = '".$id."'";
	$conn->query($sql);
    echo json_encode($id);
});

/*---------------------- Clientes --------------------*/
$app->get('/getClientes/:min/:cant', function ($min,$cant) use($app,$conn) {
	$min = intval($min);
    $sql = "SELECT * FROM users ORDER BY id DESC LIMIT $min,$cant";
	$result = $conn->query($sql);
	$info = [];
	while($response = $result->fetch_array()){
		$info[] = $response;
	}
    echo json_encode($info);
});

$app->get('/getClientes2', function () use($app,$conn) {
    $sql = "SELECT nombre,correo,telefono,nombre_f,RFC,id,colonia FROM users ORDER BY id DESC";
	$result = $conn->query($sql);
	$info = [];
	while($response = $result->fetch_array()){
		$info[] = $response;
	}
    echo json_encode($info);
});

$app->post('/getClientesQuery', function () use($app,$conn) {
	$data = $app->request->post('info');
	$datos = json_decode($data, true);
	$info = [];
	$min = intval($datos['min']);
	$cant = intval($datos['cant']);
    $sql = "SELECT * FROM users WHERE nombre LIKE '%".$datos['query']."%' OR nombre_f LIKE '%".$datos['query']."%' OR RFC LIKE '%".$datos['query']."%' ORDER BY id DESC LIMIT $min,$cant";
	$result = $conn->query($sql);
	
	while($response = $result->fetch_array()){
		$info[] = $response;
	}
    echo json_encode($info);
});

$app->get('/getCountClientes', function () use($app,$conn) {
    $sql = "SELECT count(id) FROM users";
	$result = $conn->query($sql);
	$response = $result->fetch_array();
    echo json_encode($response[0]);
});

$app->get('/getCountClientesQuery/:query', function ($query) use($app,$conn) {
    $sql = "SELECT count(id) FROM users  WHERE nombre LIKE '%".$query."%' OR nombre_f LIKE '%".$query."%' OR RFC LIKE '%".$query."%'";
	$result = $conn->query($sql);
	$response = $result->fetch_array();
    echo json_encode($response[0]);
});

$app->get('/downloadCsv', function () use($app,$conn) {
    $sql = "SELECT nombre,correo,telefono,RFC,nombre_f,direccion,ciudad,estado,pais,telefono_f,correo_f,colonia,descuento FROM users";
	$result = $conn->query($sql);
	/*
	$csv = "data:text/csv;charset=utf-8,";
	$csv .= 'Nombre,Correo,Telefono,RFC,Razon Social,Direccion,Ciudad,Estado,Pais,Telefono,Correo,Colonia,Descuento\r\n';*/
	while($info = $result->fetch_assoc()){
		$data[] = $info;
		/*
		$csv .= $info['nombre'].','.$info['correo'].','.$info['telefono'].','.$info['RFC'].','.$info['nombre_f'].','.$info['direccion'].','.$info['ciudad'].','.$info['estado'].','.$info['pais'].','.$info['telefono_f'].','.$info['correo_f'].','.$info['colonia'].','.$info['descuento'].'\r\n';*/
	}
    echo json_encode($data);
});

/*---------------------- Productos --------------------*/
$app->get('/getProducts/:min/:cant', function ($min,$cant) use($app,$conn) {
	$min = intval($min);
    $sql = "SELECT * FROM productos INNER JOIN categorias ON productos.categoria = categorias.codigo WHERE deleted = 0 ORDER BY visitas DESC LIMIT $min,$cant";
	$result = $conn->query($sql);
	$info = [];
	while($response = $result->fetch_array()){
		$info[] = $response;
	}
    echo json_encode($info);
});
$app->get('/getProducts2', function () use($app,$conn) {
    $sql = "SELECT clave,descripcion,categorias.categoria,precio,visitas FROM productos INNER JOIN categorias ON productos.categoria = categorias.codigo WHERE deleted = 0 ORDER BY visitas DESC";
	$result = $conn->query($sql);
	$info = [];
	while($response = $result->fetch_array()){
		$response[5] = file_exists ('../../assets/productos/'.$response['clave'].'.png');
		$response[3] = number_format($response[3],'2');
		$info[] = $response;

	}
    echo json_encode($info);
});

$app->post('/getProductsQuery', function () use($app,$conn) {
	$data = $app->request->post('info');
	$datos = json_decode($data, true);
	$info = [];
	$min = intval($datos['min']);
	$cant = intval($datos['cant']);
    $sql = "SELECT * FROM productos INNER JOIN categorias ON productos.categoria = categorias.codigo WHERE deleted = 0 AND (clave LIKE '%".$datos['query']."%' OR descripcion LIKE '%".$datos['query']."%') ORDER BY visitas DESC LIMIT $min,$cant";
	$result = $conn->query($sql);
	
	while($response = $result->fetch_array()){
		$info[] = $response;
	}
    echo json_encode($info);
});

$app->get('/getCountProducts', function () use($app,$conn) {
    $sql = "SELECT count(id) FROM productos WHERE deleted = 0";
	$result = $conn->query($sql);
	$response = $result->fetch_array();
    echo json_encode($response[0]);
});

$app->get('/getCountProductsQuery/:query', function ($query) use($app,$conn) {
    $sql = "SELECT count(id) FROM productos WHERE deleted= 0 AND (clave LIKE '%".$query."%' OR descripcion LIKE '%".$query."%') ";
	$result = $conn->query($sql);
	$response = $result->fetch_array();
    echo json_encode($response[0]);
});
$app->post('/updateImage', function () use($app,$conn) {

	$data = $_FILES['imagen'];
	$data2 = $_POST['producto'];
	move_uploaded_file($data['tmp_name'],'../../assets/productos/'.$data2.'.png');
    echo json_encode($data);
});
/*------------------------- Promociones ----------------------*/
$app->get('/getProductsPromos', function () use($app,$conn) {
    $sql = "SELECT clave,descripcion,precio,porcentaje,promo_productos.id FROM promo_productos INNER JOIN productos ON promo_productos.fidClave = productos.clave ORDER BY visitas DESC";
	$result = $conn->query($sql);
	$info = [];
	while($response = $result->fetch_array()){
		$response['precio'] = number_format($response['precio'],'2');
		$info[] = $response;
	}
    echo json_encode($info);
});
$app->get('/getAllProducts', function () use($app,$conn) {
    $sql = "SELECT id,clave FROM productos";
	$result = $conn->query($sql);
	$info = [];
	while($response = $result->fetch_array()){
		$info[] = $response;
	}
    echo json_encode($info);
});

$app->get('/setPromo/:clave/:porcentaje', function ($clave,$porcentaje) use($app,$conn) {
    $sql = "INSERT INTO promo_productos(fidClave,porcentaje) VALUES ('".$clave."','".$porcentaje."')";
	$conn->query($sql);
	$id = mysqli_insert_id($conn);
    $sql = "SELECT clave,descripcion,precio,porcentaje,promo_productos.id FROM promo_productos INNER JOIN productos ON promo_productos.fidClave = productos.clave WHERE promo_productos.id = '".$id."'";
    $result = $conn->query($sql);
    $response = $result->fetch_array();
    echo json_encode($response);
});

$app->get('/deletePromo/:id', function ($id) use($app,$conn) {
    $sql = "DELETE FROM promo_productos WHERE id ='".$id."'";
	$conn->query($sql);
	
    echo json_encode($id);
});
/*------------------------- Importar ----------------------*/
$app->post('/updateList', function () use($app,$conn) {
	$data = $_FILES['excel'];
	move_uploaded_file($data['tmp_name'],'precios/'.$data['name']);
	$fila = 1;
	$cont = 1;
	$fecha = date('y');
	$fecha = $fecha-2;
	$aux =  date('d/m');
	$aux .= '/';

	$aux .= $fecha;
	$fecha = $aux;
	if(($gestor = fopen("precios/".$data['name'], "r")) !== FALSE) {
		$sql = "UPDATE productos SET deleted = 1";
		$conn->query($sql);
	    while (($datos = fgetcsv($gestor, 1000, ",")) !== FALSE) {
            $numero = count($datos);
            $datos[0] = utf8_encode($datos[0]);
            $datos[1] = utf8_encode($datos[1]);
            
            if($fila>1 && $datos[5] != ""){ 
                $dat = explode('/',$datos[5]);
                $strFecha = '20'.$dat[2].'/'.$dat[1].'/'.$dat[0];
                $fecha1 = strtotime("-2 year");
                $fecha2 = strtotime($strFecha);

                if($fecha2 >= $fecha1){
                	if($datos[2]=='MICH'){
                		$datos[2] = 'N';
                	}
                	else if($datos[2]=='TECNO'){
                		$datos[2] = 'O';
                	}
                	else if($datos[2]=='DEPOT'){
                		$datos[2] = 'O';
                	}
                	else if($datos[2]=='QUIMI'){
                		$datos[2] = 'B';
                	}
                	else if($datos[2]=='KRPN'){
                		$datos[2] = 'H';
                	}
                	else if(strpos($datos[2], 'PESPE')!==false){
                		$datos[2] = substr($datos[2], -1); 
                		$sql = "SELECT categoria FROM pespe WHERE clave LIKE '".$datos[0]."'";
                		$result = $conn->query($sql);
	                    if($response = $result->fetch_array()){
	                    	$datos[2] = $response[0];
	                    }
	                    $datos[2] = 'X';  
                	}
                	else{
                		$datos[2] = substr($datos[2],0,1);
                	}
                    
                    $datos[7] = str_replace('$','',$datos[7]);
                    $datos[7] = str_replace(',','',$datos[7]);
                    $datos[0] = str_replace("'",'´',$datos[0]);

                    $datos[7] = floatval($datos[7]);
                    if($datos[7]>0.20){
                    	$sql = "SELECT id FROM productos WHERE clave LIKE '".$datos[0]."'";
	                    $respuesta = 1;
	                    $result = $conn->query($sql);
	                    if($response = $result->fetch_array()){
	                        $sql = "UPDATE productos SET descripcion = '".$datos[1]."',categoria = '".$datos[2]."',unidad = '".$datos[3]."',existencia = '".$datos[6]."', deleted=0, precio ='".$datos[7]."' WHERE id = '".$response[0]."'";
	                        $conn->query($sql);
	                    }else{
	                        $sql = "INSERT INTO productos (clave,descripcion,categoria,unidad,existencia,deleted,precio) VALUES ('".$datos[0]."','".$datos[1]."','".$datos[2]."','".$datos[3]."','".$datos[6]."',0,'".$datos[7]."')";
	                        $conn->query($sql);
	                    }
	                    $cont++;

                    }

                    
                }
                
            }
            $fila++;
        }
	}
	
    echo json_encode($datos);
});

/*------------------------- Detalles pedidos ----------------------*/

$app->get('/getOrder/:id', function ($id) use($app,$conn) {
    $sql = "SELECT * FROM pedidos INNER JOIN users ON pedidos.fidUser = users.id WHERE pedidos.id ='".$id."'";
	$result = $conn->query($sql);
    $response = $result->fetch_array();
    echo json_encode($response);
});

$app->get('/getRelatedProductsOrder/:id', function ($id) use($app,$conn) {
    $sql = "SELECT *,detalle_pedidos.precio AS precio FROM detalle_pedidos INNER JOIN productos ON detalle_pedidos.fidClave = productos.clave WHERE fidPedido ='".$id."'";
	$result = $conn->query($sql);
   $info = [];
	while($response = $result->fetch_array()){
		$info[] = $response;
	}
    echo json_encode($info);
});

$app->get('/cancelarOrder/:id/:motivo', function ($id,$motivo) use($app,$conn) {
    $sql = "UPDATE pedidos SET cancelado = 1, motivo_cancelacion = '".$motivo."' WHERE id='".$id."' ";
    $conn->query($sql);
    echo json_encode($motivo);
});

/*------------------------- Editar pedidos ----------------------*/

$app->get('/getProduct/:clave/:id', function ($clave,$id) use($app,$conn) {
	$sql = "SELECT descuento FROM users WHERE id='".$id."'";
	$result = $conn->query($sql);
	$response = $result->fetch_array();
	$descuento = intval($response[0]);

    $sql = "SELECT * FROM productos  WHERE clave LIKE '".$clave."'";
	$result = $conn->query($sql);
    $response = $result->fetch_array();

    if($descuento > 0){
		$descuento = $descuento * .01;
		$response['precio'] = $response['precio'] - ($response['precio']*$descuento);
	}
    echo json_encode($response);
});
/*
$app->get('/getProducts/:id', function ($id) use($app,$conn) {
	$sql = "SELECT descuento FROM users WHERE id='".$id."'";
	$result = $conn->query($sql);
	$response = $result->fetch_array();
	$descuento = intval($response[0]);

	$sql = "SELECT * FROM productos INNER JOIN categorias ON productos.categoria = categorias.codigo WHERE deleted = 0 ORDER BY visitas";
	$result = $conn->query($sql);
	$info = [];

	if($descuento > 0){
		$descuento = $descuento * .01;
		while($response = $result->fetch_assoc()){
			$response['precio'] = $response['precio'] - ($response['precio']*$descuento);
			$info[] = $response;
		}
	}else{
		while($response = $result->fetch_assoc()){
			$info[] = $response;
		}

	}
	
    echo json_encode($info);
});*/


$app->post('/updateOrder', function () use($app,$conn) {
	$data = $app->request->post('info');
	$datos = json_decode($data, true);
	
	$sql = "DELETE FROM detalle_pedidos WHERE fidPedido = '".$datos[0]['idPedido']."'";
	$conn->query($sql);
	$sql = "UPDATE pedidos SET total = '".$datos[0]['total']."', estatus = '".$datos[0]['estatus']."' WHERE id = '".$datos[0]['idPedido']."'";
	$conn->query($sql);
	$i = 0;
	$info = [];
	while(isset($datos[$i])){
		$info[] = $datos[$i];
		$sql = "INSERT INTO detalle_pedidos (fidClave, fidPedido, cantidad,precio,totalPartida) VALUES ('".$datos[$i]['clave']."','".$datos[0]['idPedido']."','".$datos[$i]['cantidad']."','".$datos[$i]['precio']."','".$datos[$i]['totalPartida']."')";
		$conn->query($sql);
		$i++;
	}
    echo json_encode($sql);
});

/*------------------------- Detalles cliente ----------------------*/
$app->get('/getUser/:id', function ($id) use($app,$conn) {
    $sql = "SELECT * FROM users  WHERE id = '".$id."'";
	$result = $conn->query($sql);
    $response = $result->fetch_array();
    $response['contrasena'] = desencriptar($response['contraseña']);
    echo json_encode($response);
});

$app->post('/updateUser', function () use($app,$conn) {
	$data = $app->request->post('info');
	$datos = json_decode($data, true);
	$puede_comprar = 0;
	if($datos['RFC']&&$datos['nombre_f']&&$datos['direccion']&&$datos['ciudad']&&$datos['estado']&&$datos['pais']&&$datos['telefono_f']&&$datos['correo_f']&&$datos['colonia']){
		$puede_comprar = 1;
	}
	$sql = "UPDATE users SET correo = '".$datos['correo']."', nombre = '".$datos['nombre']."',telefono ='".$datos['telefono']."', RFC = '".$datos['RFC']."', nombre_f = '".$datos['nombre_f']."',direccion = '".$datos['direccion']."',ciudad = '".$datos['ciudad']."', estado = '".$datos['estado']."', pais='".$datos['pais']."', telefono_f = '".$datos['telefono_f']."',correo_f = '".$datos['correo_f']."',descuento = '".$datos['descuento']."', puede_comprar='".$puede_comprar."',colonia='".$datos['colonia']."' WHERE id = '".$datos['id']."'";
	$conn->query($sql);
    echo json_encode($sql);
});
$app->get('/deleteUser/:id', function ($id) use($app,$conn) {
	$sql = "SELECT id FROM pedidos WHERE fidUser = '".$id."'";
	$conn->query($sql);
	$result = $conn->query($sql);
	while($response = $result->fetch_array()){
		$sql2 = "DELETE FROM detalle_pedidos WHERE fidPedido = '".$response['id']."'";
		$conn->query($sql2);
		$sql2 = "DELETE FROM pedidos WHERE id = '".$response['id']."'";
		$conn->query($sql2);
	}
	$sql = "DELETE FROM carrito WHERE fidCliente = '".$id."'";
	$conn->query($sql);
    $sql = "DELETE FROM users WHERE id = '".$id."'";
	$conn->query($sql);
    echo json_encode($id);
});

/*------------------------- Login ----------------------*/

$app->post('/newSession', function () use($app,$conn) {
	$data = $app->request->post('info');
	$datos = json_decode($data, true);
	$sql = "SELECT usuario, contraseña FROM admin_user";
	$conn->query($sql);
	$result = $conn->query($sql);
	$response = $result->fetch_array();
	if($response['usuario']==$datos['user']&&$response['contraseña']==$datos['pass']){
		$respuesta = 1;
	}else{
		$respuesta = 0;
	}
    echo json_encode($respuesta);
});

/*------------------------- Crear Cliente ----------------------*/
$app->get('/generatePass', function () use($app,$conn) {
    echo json_encode(substr( md5(microtime()), 1, 8));
});

$app->post('/createUser', function () use($app,$conn) {
	$data = $app->request->post('info');
	$datos = json_decode($data, true);
	$sql = "SELECT id FROM users WHERE correo LIKE '".$datos['correo']."'";
	$puede_comprar = 0;
	$result = $conn->query($sql);
	if($response = $result->fetch_array()){
		$id = 0;
	}else{
		if(!isset($datos['RFC'])){
			$datos['RFC'] = "";
		}
		if(!isset($datos['nombre_f'])){
			$datos['nombre_f'] = "";
		}
		if(!isset($datos['direccion'])){
			$datos['direccion'] = "";
		}
		if(!isset($datos['ciudad'])){
			$datos['ciudad'] = "";
		}
		if(!isset($datos['estado'])){
			$datos['estado'] = "";
		}
		if(!isset($datos['pais'])){
			$datos['pais'] = "";
		}
		if(!isset($datos['telefono_f'])){
			$datos['telefono_f'] = "";
		}
		if(!isset($datos['correo_f'])){
			$datos['correo_f'] = "";
		}
			if(!isset($datos['colonia'])){
			$datos['colonia'] = "";
		}
		if($datos['RFC']&&$datos['nombre_f']&&$datos['direccion']&&$datos['ciudad']&&$datos['estado']&&$datos['pais']&&$datos['telefono_f']&&$datos['correo_f']&&$datos['colonia']){
			$puede_comprar = 1;
		}
		$sql = "INSERT INTO users (correo,nombre,telefono,contraseña,RFC,nombre_f,direccion,ciudad,estado,pais,telefono_f,correo_f,puede_comprar,colonia) 
			    VALUES ('".$datos['correo']."','".$datos['nombre']."','".$datos['telefono']."','".encriptar($datos['contrasena'])."','".$datos['RFC']."',
			    		'".$datos['nombre_f']."','".$datos['direccion']."','".$datos['ciudad']."','".$datos['estado']."','".$datos['pais']."',
			    		'".$datos['telefono_f']."','".$datos['correo_f']."','".$puede_comprar."','".$datos['colonia']."')";
		$conn->query($sql);
		$id = mysqli_insert_id($conn);
	}
    echo json_encode($id);
});


$app->run();


?>