<?php
$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = 'root';
$dbname = 'officedo';
$conn = new mysqli($dbhost, $dbuser, $dbpass,$dbname);
if($conn->connect_error) {
	die('Could not connect1: ' . mysqli_error());
}   
$conn->set_charset('utf8');

    $fila = 1;
    $cont = 1;
    $fecha = date('y');
    $fecha = $fecha-2;
    $aux =  date('d/m');
    $aux .= '/';

    $aux .= $fecha;
    $fecha = $aux;
    if(($gestor = fopen("existencias.csv", "r")) !== FALSE) {
        $sql = "UPDATE productos SET deleted = 1";
        $conn->query($sql);
        while (($datos = fgetcsv($gestor, 1000, ",")) !== FALSE) {
            $numero = count($datos);
            if($fila>1){
                echo $fecha."-".$datos[5]."<br>";
                $fecha1 = strtotime($fecha);
                $fecha2 = strtotime($datos[5]);
                echo $fecha1."-".$fecha2."<br><br>";

                if($fecha2 >= $fecha1){
                    $datos[2] = substr($datos[2],0,1);
                    $datos[7] = str_replace('$','',$datos[7]);
                    $sql = "SELECT id FROM productos WHERE clave LIKE '".utf8_decode($datos[0])."'";
                    $respuesta = 1;
                    $result = $conn->query($sql);
                    if($response = $result->fetch_array()){
                        $sql = "UPDATE productos SET descripcion = '".utf8_decode($datos[1])."',categoria = '".$datos[2]."',unidad = '".$datos[3]."',existencia = '".$datos[6]."', deleted=0, precio ='".$datos[7]."' WHERE id = '".$response[0]."'";
                        $conn->query($sql);
                    }else{
                        $sql = "INSERT INTO productos (clave,descripcion,categoria,unidad,existencia,deleted,precio) VALUES ('".utf8_encode($datos[0])."','".utf8_encode($datos[1])."','".$datos[2]."','".$datos[3]."','".$datos[6]."',0,'".$datos[7]."')";
                        $conn->query($sql);
                    }
                    $cont++;
                }
                
            }
            $fila++;
        }
        fclose($cont);
    }
?>