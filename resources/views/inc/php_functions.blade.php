<?php

   function savenewentry($mytable, $myarray){
        try {
            $placeholder="";
            $columnarray="";
            $sql1="INSERT INTO {$mytable} (";
            $sql3=   ") VALUES (";
            $sql5=   ");";
            foreach($myarray as $mycolumn=>$myval){
                $columnarray.=$mycolumn.',';
                if ($myval=="CURRENT_DATE"){
                    $placeholder.='CURRENT_DATE,';
                }else{
                    $placeholdername=':value'.$mycolumn;
                    $placeholder.="{$placeholdername},";
                    $arraytopass[$placeholdername]=$myval;
                }
            }
            //Add user email function
            
            $useremail=session('email');
            $columnarray.='iden_email,';
            $placeholdername=':value'.'iden_email';
            $placeholder.="{$placeholdername},";
            $arraytopass[$placeholdername]=$useremail;
        
            $sql2=substr_replace($columnarray ,"", -1);
            $sql4=substr_replace($placeholder ,"", -1);
            $completesql=$sql1.$sql2.$sql3.$sql4.$sql5;
            Log::info('savenewentry_attempt:', ['email'=>$useremail,'completesql' => $completesql,'arraytopass'=>$arraytopass ]);
            $results = DB::insert($completesql, $arraytopass);
            return ("{$mytable} ha sido guardado con exito");
        } catch(PDOException $e) {
            Log::info('savenewentry_fail:', ['email'=>$useremail,'completesql' => $completesql,'arraytopass'=>$arraytopass ]);
            return ("{$mytable} failed to save with error ". $e->getMessage());
        }
    }

function askforkey($mytable, $myprimary, $myfield,  $myvalue){
       try {
            $sql="SELECT {$myprimary} FROM {$mytable} WHERE {$myfield}=:value";
            $stmnt= DB::select($sql,[':value'=>$myvalue]);
            return $stmnt[0]->$myprimary; 
       } catch(PDOException $e) {
            return $e->getMessage();
       }
  }

  function getserialmax($tablename){
        $invNum= DB::select("SELECT MAX(iden) AS max_id FROM {$tablename}");
        $max_id = $invNum[0]->max_id;
        if(is_null($max_id)){
            return 0;
        }else{
            return $max_id;
        }    
    }


    function savenewspecies($table,$comun,$cientifico, $invasor, $extra = false ){
        $invasorstring='false';
        if ($invasor){
            $invasorstring='true';
        }
        $extrastring='false';
        if ($extra){
            $extrastring='true';
        }   
        
        $newspecies=array(
            "comun"=> $comun,
            "cientifico"=> $cientifico,
            "comun_cientifico"=> $comun."*".$cientifico,
            "invasor"=> $invasorstring
                );
        if ($table=='especie_arbol'){
          $newspecies["iden_cactus"] = $extrastring;
        }
        if ($table=='especie_herpetofauna'){
          $newspecies["iden_anfibio"] = $extrastring;
        }
        

        $namesmatching = DB::select("SELECT cientifico FROM {$table} WHERE cientifico=:value", [':value'=>$cientifico]);
        if (sizeof($namesmatching)==1){
            return askforkey($table, 'iden', "cientifico",  $cientifico);
        }else{
            $resultofquery = savenewentry($table, $newspecies);
            echo $resultofquery;
            return getserialmax( $table);
        }
    }


   


    function countrows($newpost,$tablename){
        $rownumlist=[];
        foreach($newpost as $key => $value) {
            if (substr_count($key, '*')==2){
                $expoldekey=explode("*" , $key );
                if ($expoldekey[1]==$tablename){
                    if (!(in_array($expoldekey[0],$rownumlist))){
                        array_push($rownumlist,$expoldekey[0]);
                    }
                }
            }
        }
    return(sizeof($rownumlist));
    } 

    function rowmax($newpost,$tablename){
        $myrowmax=0;
        foreach($newpost as $key => $value) {
            if (substr_count($key, '*')==2 && strpos($key, $tablename) !== false ){
                $expoldekey=explode("*" , $key );
                $num=explode("row" , $expoldekey[0] );
                if ($num[1]>$myrowmax){
                    $myrowmax-$num[1];
                }
            }
        }
    return($myrowmax);
    } 


    function buildcolumnsarray($newpost,$tablename, $rowandnum){
        try {
            $sql="SELECT column_name FROM information_schema.columns WHERE table_schema = 'public' AND table_name   = :tablename";
            $result= DB::select($sql,[':tablename'=>$tablename]);
            $colarray=array();
                foreach($result as $colobj){
                    //if (explode("_" , $tablename)[0]!='observacion'){
                    //    $tablename='observacion_'.explode("_" , $tablename)[1];
                    //};

                    $col=$colobj->column_name;
                    if (substr($col,0,4) != 'iden'){
						$colarray[$col]=trim($newpost["{$rowandnum}*{$tablename}*{$col}"]);
						//Check for incorrect datetime formats 
						if (strpos($col, 'hora') !== false && strpos($colarray[$col], ':') == false){
							$colarray[$col]="00:01";
						}
						if (strpos($col, 'fecha') !== false && strpos($colarray[$col], '-') == false){
							$colarray[$col]="01/01/0001";
						}
                    }
                }
            return $colarray;
           
       } catch(PDOException $e) {
            return $e->getMessage();
       }
  }


  function formatdate($locvalue){
	if (strlen($locvalue)<=4){
		return "01-01-1900";
	}
	//Add leading zero to day 
	if (!is_numeric(substr($locvalue, 1, 1))){	
		$locvalue="0".$locvalue;
	}
	//Add leading zero to month 
	if (is_numeric(substr($locvalue, 3, 1)) && !(is_numeric(substr($locvalue, 4, 1)))){	
		$locvalue=substr($locvalue, 0, 3) . "0" . substr($locvalue, 3);
	}
	//Switch day and month
	if (is_numeric(substr($locvalue, 3, 2))){
		$locvalue= substr($locvalue, 3, 3) .substr($locvalue, 0, 3) . substr($locvalue, 6, 4);
	}else{
		$rawmonth=strtolower(explode(substr($locvalue, 2, 1), $locvalue)[1]);
		$newmonth = strpos($rawmonth, 'ene') !== false ? 'jan':
		strpos($rawmonth, 'ene') !== false || strpos($rawmonth, 'jan') !== false  ? 'jan':
		strpos($rawmonth, 'feb') !== false ? 'feb':
		strpos($rawmonth, 'mar') !== false ? 'mar':
		strpos($rawmonth, 'abr') !== false || strpos($rawmonth, 'apr') !== false  ? 'apr':
		strpos($rawmonth, 'may') !== false ? 'may':
		strpos($rawmonth, 'jun') !== false ? 'jun':
		strpos($rawmonth, 'jul') !== false ? 'jul':
		strpos($rawmonth, 'ago') !== false || strpos($rawmonth, 'aug') !== false  ? 'aug':
		strpos($rawmonth, 'sep') !== false ? 'sep':
		strpos($rawmonth, 'oct') !== false ? 'oct':
		strpos($rawmonth, 'nov') !== false ? 'nov':
		strpos($rawmonth, 'dic') !== false || strpos($rawmonth, 'dec') !== false  ? 'dec': 
		'error';
		$locvalue=explode(substr($locvalue, 2, 1), $locvalue)[0] ."-" . $newmonth ."-". explode(substr($locvalue, 2, 1), $locvalue)[2];
	}
		return str_replace("/","-",$locvalue);
  }

    function uploadfoto($newpost,$filesname,$filestmpname,$filessize, $obstype){
      
          if (isset($filesname) && ($filesname)!="" && ($filesname)!="0" && ($filesname)!="00" && ($filesname)!="000"){
            
            $target_dir = "../storage/img/";
            $target_file = $target_dir . $obstype ."_". basename($filesname);
            $uploadOk = 1;
            $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
            // Check if image file is a actual image or fake image
            if(isset($newpost["submit"])) {
                $check = getimagesize($filestmpname);
                if($check !== false) {
                    echo "File is an image - " . $check["mime"] . ".";
                    $uploadOk = 1;
                } else {
                    return "El formato de su photo no es correcto.";
                    $uploadOk = 0;
                }
            }
            // Check if file already exists
            if (file_exists($target_file)) {
                return "Un foto con esa nombre ya existe";
                $uploadOk = 0;
            }
            // Check file size
            if ($filessize > 5000000000) {
                return "El tamano de su foto es demasiado grande";
                $uploadOk = 0;
            }
            // Allow certain file formats
            if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
            && $imageFileType != "gif" ) {
                return "Solo JPG, JPEG, PNG y GIF son permitidos";
                $uploadOk = 0;
            }
            // Check if $uploadOk is set to 0 by an error
            if ($uploadOk == 0) {
                return "Hubo un problema con el cargo de su foto (1)";
            // if everything is ok, try to upload file
            } else {

           
                if (move_uploaded_file($filestmpname, $target_file)) {
                    echo "The file  has been uploaded.";
                    return  $obstype ."_". basename($filesname);
                } else {
                    return "Hubo un problema con el cargo de su foto (2)";
                }
            }
            return "Hubo un problema con el cargo de su foto (3)";
        }
        return ('No Presentado');
    }

    function uploadshape($shpname){
        if (strlen($_FILES[$shpname]["name"])){
            $target_dir = "../storage/shp/";
            $target_file = $target_dir . basename($_FILES[$shpname]["name"]);
            $uploadOk = 1;
            $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
            // Check if image file is a actual image or fake image
            if(isset($_POST["submit"])) {
                $check = getimagesize($_FILES[$shpname]["tmp_name"]);
                if($check !== false) {
                    echo "File is an image - " . $check["mime"] . ".";
                    $uploadOk = 1;
                } else {
                    echo "File is not an image.";
                    //$uploadOk = 0;
                }
            }
            // Check if file already exists
            if (file_exists($target_file)) {
                $uploadOk = 0;
            }
            // Check file size
            if ($_FILES[$shpname]["size"] > 5000000000) {
                $uploadOk = 0;
            }
            // Allow certain file formats
            
            // Check if $uploadOk is set to 0 by an error
            if ($uploadOk == 0) {
            // if everything is ok, try to upload file
            } else {

           
                if (move_uploaded_file($_FILES[$shpname]["tmp_name"], $target_file)) {
                    echo "The file  has been uploaded.";
                    return (basename( $_FILES[$shpname]["name"]));
                } else {
                    
                    echo "Sorry, there was an error uploading your file.";
                    
                }


                
            }
            return ('Failed');
        }
    
        return ('No Presentado');
    }