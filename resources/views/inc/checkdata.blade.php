<?php
$errorlist=[];
if ($_SERVER['REQUEST_METHOD']=="POST") {

	if(isset($_POST['row0*actividad*descripcion'])){
		$result = DB::select("SELECT descripcion FROM actividad WHERE descripcion  = :value", [':value'=>$_POST['row0*actividad*descripcion']]);
		if (sizeof($result)>0) {
			$errorlist[]= "Un accion con este descripcion ya existe, por favor cambie el descripcion";
		}
	}

  foreach( $_FILES as $postkey2=> $postvalwithoutname) {
	 	if (sizeof(explode("*" , $postkey2))>1){
			$tablename= explode("*" , $postkey2)[1];
			$postval=$postvalwithoutname['name'];
			if (DB::select("SELECT iden_foto FROM {$tablename} WHERE iden_foto  = :value", [':value'=>$postval])) {
				$errorlist[]= "El foto {$postval} ya existe, por favor cambie el nombre del foto";
			}
		}
    }
    foreach( $_POST as $postkey=> $postval) {
        if ($postval=='notselected') {
            $selectmenuname= substr($postkey, 6);
            $errorlist[]= "Los menus desplegables no deben estar vacios";
        }
        if (substr_count($postkey, '*')==2){

            $columnname= explode("*" , $postkey)[2];
            $tablename= explode("*" , $postkey)[1];
            //Handle empty fields
            if(strlen($postval)==0){
                $hidden=false;
                if ($tablename=='estado'||$tablename=='municipio'||$tablename=='predio'){
                    if ($_POST['selectlinea_mtp']!="Nuevo"){
                        $hidden=true;
                    }else{
                        if (($_POST['selectestado']!="Nuevo") && $tablename=='estado'){
                            $hidden=true;
                        }
                        if (($_POST['selectmunicipio']!="Nuevo") && $tablename=='municipio'){
                            $hidden=true;
                        }
                        if (($_POST['selectpredio']!="Nuevo") && $tablename=='predio'){
                            $hidden=true;
                        }
                    }
                }
                if (!$hidden && $columnname !='notas'){
                    $errorlist[]= "El campo '{$columnname}' de {$tablename} esta vacio";
                }
                //Handle non-empty fields
            }else{
       
                if ($columnname=='clave' && !(strlen($postval)==3 && ctype_alpha($postval))) {
                    $errorlist[]= "El campo 'clave' de {$tablename} tiene que ser 3 letras";
                }
                if ($columnname=='clave' && DB::select("SELECT {$columnname} FROM {$tablename} WHERE {$columnname}  = :value", [':value'=>$postval])) {
                    $errorlist[]= "El clave {$postval} ya existe, ingrese un clave diferente";
                }
                if (($columnname=='superficie') && !(is_numeric($postval))) {
                    $errorlist[]= "El campo 'superficie' de {$tablename} tiene que ser numero";
                }
                if ($columnname=='telefono' && strlen($postval)>0 && !(is_numeric($postval))) {
                    $errorlist[]= "El campo 'telefono' de {$tablename} tiene que ser solo numeros";
                }
                if (strpos($columnname, 'itud') !== false && !(is_numeric($postval))) {
                    $errorlist[]= "El campo '{$columnname}' de {$tablename} tiene que ser numero";
                }
                if (strpos($columnname, 'longitud') !== false && (!($postval<-67) || !($postval>-82))) {
                    $errorlist[]= "El campo '{$columnname}' de {$tablename} tiene que ser entre -67 y -82 grados";
                }
                if (strpos($columnname, 'latitud') !== false && (!($postval>-20) || !($postval<2))) {
                    $errorlist[]= "El campo '{$columnname}' de {$tablename} tiene que ser entre -20 y 2 grados";
                }
                if ($postval=='notselected' && $columnname !='notas') {
                    $errorlist[]= "{$tablename} esta vacio";
                }
               
                $listnumcol=array(
                  'dn'=>'3','m'=>'3','a'=>'0','dc1'=>'3',
                  'dc2'=>'3','acc1'=>'3','acc2'=>'3','acc3'=>'3',
                  'altura'=>'3','distancia'=>'3','azimut'=>'3',
                  'longitud_gps'=>'4', 'latitud_gps'=>'4',
                  'comienzo_longitud'=>'4','comienzo_latitud'=>'4',
                  'fin_longitud'=>'4','fin_latitud'=>'4',
                  'anio_de_camara'=>'0', 'numero_de_photos_totales'=>'0', 
                  'porcentaje_de_bateria'=>'0', 'anio'=>'0'
                );
                foreach ($listnumcol as $numcol=>$precision) {
                  if ($columnname == $numcol) {
                    $exploded = explode("." , $postval);
                    if (!is_numeric($postval)){
                      $errorlist[]= "Hay error en '{$columnname}', hay que ser numero";
                    }else{
                      if($precision==0){
                        if ($postval!=0 && (sizeof($exploded)!=1)){
                          $errorlist[]= "Hay error en '{$columnname}', hay que ser numero con {$precision} numeros despues del punto decimal";
                        }
                      }elseif($postval!=0 && (sizeof($exploded)!=2 || strlen($exploded[1]) != $precision)){
                        $errorlist[]= "Hay error en '{$columnname}', hay que ser numero con {$precision} numeros despues del punto decimal";
                      }
                    }
                  }
                }
        }
    }
  }
    // for($i=0; $i<countrows($_POST,'observacion_ave'); $i++){
    //   $micrototal=
    //     $_POST["row{$i}*observacion_ave*fo_arbol"] + 
    //     $_POST["row{$i}*observacion_ave*fo_arbusto"] +
    //     $_POST["row{$i}*observacion_ave*tr_arbol"] +
    //     $_POST["row{$i}*observacion_ave*tr_arbusto"] +
    //     $_POST["row{$i}*observacion_ave*ro"] +
    //     $_POST["row{$i}*observacion_ave*su"];
    //   if ($micrototal != 0 && $micrototal != 1){
    //     $realrow=$i+1;
    //     $errorlist[]= "Hay error en observacion {$realrow}, hay que entrar 1 para solo un lugar, en 0 por los demas";
    //   } 
    // }





  }
session(['error' => $errorlist]);
?>