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
        while (($datos = fgetcsv($gestor, 100000, ",")) !== FALSE) {
            $numero = count($datos);
            
            echo utf8_encode($datos[0])."<br>".$fila."<br>";
            $datos[0] = utf8_encode($datos[0]);
            $datos[1] = utf8_encode($datos[1]);
            if($fila>1 && $datos[5] != ""){ 
                
                $dat = explode('/',$datos[5]);
                $strFecha = '20'.$dat[2].'/'.$dat[1].'/'.$dat[0];
                
                $fecha1 = strtotime("-3 year");
                $fecha2 = strtotime($strFecha);
           
                
                if($fecha2 >= $fecha1){
                    $datos[2] = substr($datos[2],0,1);
                    $datos[7] = str_replace('$','',$datos[7]);
                    $datos[0] = str_replace("'",'´',$datos[0]);
                    
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
            $fila++;
        }
        echo $fila;
        
    }
?>