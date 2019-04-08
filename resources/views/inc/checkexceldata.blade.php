<?php

  use PhpOffice\PhpSpreadsheet\IOFactory;
  $errorlist=[];
  session(['error' => []]);

  if ($_SERVER['REQUEST_METHOD']=="POST" && isset($_POST['ingresarexcel'])) {
      if ($_FILES['excelFromUser']['name']=='') {
        $errorlist[]= "No hay excel";
      }
      if ($_POST['selectlinea_mtp']=='notselected') {
        $errorlist[]= "Los menus desplegables no deben estar vacios";
      }
      //Upload fotos

      if(sizeof($errorlist)==0){
        $target_dir = "../storage/shp/";
        $target_file = $target_dir . basename($_FILES['excelFromUser']["name"]);
        $inputFileName = $_FILES['excelFromUser'];
        move_uploaded_file($_FILES['excelFromUser']["tmp_name"], $target_file);
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        $worksheetNames = $reader->listWorksheetNames($target_file);
		$spreadsheet = $reader->load($target_file);
		$sheetnames = $spreadsheet->getSheetNames();
		$medicionexists=false;
		foreach ($sheetnames as  $name) {
			if ($name=='MEDICION'){
				$medicionexists=true;
			}else{
				$exploded = explode("_" , $name);
				if(sizeof($exploded)==3){
					if (!in_array($exploded[0],['AVE','ARBO','ARBU','HERP','MAMI','HIER'])){
						$errorlist[]="{$name} no tiene nombre correcto.";
					}
					if (!in_array($exploded[1],['LOC','OBS'])){
						$errorlist[]="{$name} no tiene nombre correcto.";
					}
					if (!is_numeric($exploded[2])){
						$errorlist[]="{$name} no tiene nombre correcto.";
					}
				}else{
					$errorlist[]="{$name} no tiene nombre correcto.";
				}

			}
		}
		if(!$medicionexists){
			$errorlist[]="No existe MEDICION";

		}

		if(sizeof($errorlist)==0){
			$medicionpost = array('selectlinea_mtp' => $_POST['selectlinea_mtp']);
			$medicionpost['selectmedicion']='Nuevo';
			$day =  $spreadsheet->getSheetByName('MEDICION')->getCell('A3')->getValue();
			$month =  $spreadsheet->getSheetByName('MEDICION')->getCell('B3')->getValue();
			$year =  $spreadsheet->getSheetByName('MEDICION')->getCell('C3')->getValue();

			list($newdatevalue,$dateerror) = formatdate("{$day}-{$month}-{$year}", 'MEDICION', 'A-C', '');
			if($dateerror==''){
				$medicionpost['row0*medicion*fecha']=$newdatevalue;
			}else{
				$errorlist[]=$dateerror;
			}		
		}


        $brigadarownumber=3;
        while (true && sizeof($errorlist)==0){
          $materno =  $spreadsheet->getSheetByName('MEDICION')->getCell("D{$brigadarownumber}")->getValue();
          $paterno =  $spreadsheet->getSheetByName('MEDICION')->getCell("E{$brigadarownumber}")->getValue();
          $nombre =  $spreadsheet->getSheetByName('MEDICION')->getCell("F{$brigadarownumber}")->getValue();
          if ($materno==NULL && $paterno==NULL && $nombre==NULL){
            break;
          }else{
            $rownum=$brigadarownumber-3;
            $medicionpost["row{$rownum}*personas*apellido_materno"]=$materno;
            $medicionpost["row{$rownum}*personas*apellido_paterno"]=$paterno;
            $medicionpost["row{$rownum}*personas*apellido_nombre"]=$nombre;
          }
          $brigadarownumber++;
        }

        $gpsrownumber=3;
        while (true){
          $anio =  $spreadsheet->getSheetByName('MEDICION')->getCell("H{$gpsrownumber}")->getValue();
          $marca =  $spreadsheet->getSheetByName('MEDICION')->getCell("G{$gpsrownumber}")->getValue();
          $modelo =  $spreadsheet->getSheetByName('MEDICION')->getCell("I{$gpsrownumber}")->getValue();
          $numero_de_serie =  $spreadsheet->getSheetByName('MEDICION')->getCell("J{$gpsrownumber}")->getValue();
          if ($anio==NULL && $marca==NULL && $modelo==NULL && $numero_de_serie==NULL){
            break;
          }else{
            $rownum=$gpsrownumber-3;
            $medicionpost["row{$rownum}*gps*anio"]=$anio;
            $medicionpost["row{$rownum}*gps*marca"]=$marca;
            $medicionpost["row{$rownum}*gps*modelo"]=$modelo;
            $medicionpost["row{$rownum}*gps*numero_de_serie"]=$numero_de_serie;
          }
          $gpsrownumber++;
        }

        $camararownumber=3;
        while (true){
          $anio =  $spreadsheet->getSheetByName('MEDICION')->getCell("H{$camararownumber}")->getValue();
          $marca =  $spreadsheet->getSheetByName('MEDICION')->getCell("G{$camararownumber}")->getValue();
          $modelo =  $spreadsheet->getSheetByName('MEDICION')->getCell("I{$camararownumber}")->getValue();
          $numero_de_serie =  $spreadsheet->getSheetByName('MEDICION')->getCell("J{$camararownumber}")->getValue();
          if ($anio==NULL && $marca==NULL && $modelo==NULL && $numero_de_serie==NULL){
            break;
          }else{
            $rownum=$camararownumber-3;
            $medicionpost["row{$rownum}*camara*anio"]=$anio;
            $medicionpost["row{$rownum}*camara*marca"]=$marca;
            $medicionpost["row{$rownum}*camara*modelo"]=$modelo;
            $medicionpost["row{$rownum}*camara*numero_de_serie"]=$numero_de_serie;
          }
          $camararownumber++;
        }


        $obspostarray = [];
        $uploadfotoarray = [];
        //Begin saving observations
        foreach ($worksheetNames as $sheet) {
          //LOC
          if (strpos($sheet, 'LOC') !== false){
			$emptylocationsheet=false;
            $obspost = array('selectlinea_mtp' => $_POST['selectlinea_mtp']);
            $obspost['mode']='Datos Nuevos';
            $obspost['submit']='submit';
            $lifeformraw = explode("_" , $sheet)[0];
            if ($lifeformraw=="AVE") $lifeform='ave';
            if ($lifeformraw=="ARBO") $lifeform='arbol';
            if ($lifeformraw=="ARBU") $lifeform='arbusto';
            if ($lifeformraw=="HIER") $lifeform='hierba';
            if ($lifeformraw=="MAMI") $lifeform='mamifero';
            if ($lifeformraw=="HERP") $lifeform='herpetofauna';

            $transpunto="punto";
            if ($lifeform=='hierba' || $lifeform=='herpetofauna'){
              $transpunto="transecto";
            }

            $transpuntoup=ucfirst($transpunto);
            $obspost['selectobservaciones'] = $lifeform;
            $given_number = explode("_" , $sheet)[2];
            if ($lifeform=='arbol'||$lifeform=='arbusto'){
              $obspost["selectTransecto"] = ceil($given_number/8);
              $obspost["select{$transpuntoup}"]=$given_number - 8*($obspost["selectTransecto"]-1);
            }else{
              $obspost["select{$transpuntoup}"] = $given_number;
            }

            //get loc values
            $letter = 'A';
            while(true){
              $loccolvalue = strtolower(trim($spreadsheet->getSheetByName($sheet)->getCell("{$letter}1")->getValue()));
              if ($loccolvalue==NULL){
                break;
              }else{
                $locvalue = trim($spreadsheet->getSheetByName($sheet)->getCell("{$letter}2")->getValue());
                if($locvalue==NULL && $letter=='A'){
					$emptylocationsheet=true;
				}

				if ($locvalue==NULL && $letter!='A'){
					$errorlist[]="No hay datos en {$letter}2 en {$sheet} ";
				}

				if (strpos($loccolvalue, 'fecha') !== false){
					list($newdatevalue,$dateerror) = formatdate($locvalue,  $sheet, $letter, 2);
					if($dateerror==''){
						$locvalue=$newdatevalue;
					}else{
						$errorlist[]=$dateerror;
					}
				}


                $obspost["row0*{$transpunto}_{$lifeform}*{$loccolvalue}"] = $locvalue;
                
                $letter = ++$letter;
              }
            }
        
            $sheetobs= str_replace("LOC","OBS",$sheet);
            $obscolumnarray=[];
            $letter = 'A';
            //scan columns to get column names
            while(true){
					if ($spreadsheet->getSheetByName($sheetobs)==null){
						$errorlist[]="No existe {$sheetobs}.";
						$value2=NULL;
					}else{
						$value2 = $spreadsheet->getSheetByName($sheetobs)->getCell("{$letter}1")->getValue();
					}
              if ($value2 == NULL){
                break;
              }else{
                $obscolumnarray[] = strtolower(trim($value2));
                $letter = ++$letter;
              }
            }
            //scan rows of observacions
            $row_number=2;
            $true_row=0;

            while (true && sizeof($errorlist)==0){
              if ($spreadsheet->getSheetByName($sheetobs)->getCell("B{$row_number}")->getValue()==NULL){
                break;
              }else{
                  $letter = 'A';
                  //scan across columns
                  foreach ($obscolumnarray as $obscolumn) {
					  
					$newobscolumn = $obscolumn; 
					$obsvalue = trim($spreadsheet->getSheetByName($sheetobs)->getCell("{$letter}{$row_number}")->getValue());
					if ($obsvalue==NULL && $newobscolumn!='notas' && $newobscolumn!='iden_foto' ){
						$errorlist[]="No hay datos en {$letter}{$row_number} en {$sheetobs}.";
					}
                    if (strpos($newobscolumn, 'iden_foto') !== false){
                      if($obsvalue==NULL || $obsvalue==""  || $obsvalue=="00" || $obsvalue=="000" || $obsvalue=="0000"){
                        $obsvalue = "No Presentado";
                      }else{
                        $uploadfotoarray[$obsvalue]="observacion_{$lifeform}";
                        $obsvalue="observacion_{$lifeform}_{$obsvalue}";
                      }
					}
					
                    if($obscolumn=='cientifico'){
                      if ($obsvalue==NULL){
                        break;
                      }else{
                        $cientifico = $spreadsheet->getSheetByName($sheetobs)->getCell("{$letter}{$row_number}")->getValue();
                        $obspost["row{$true_row}*observacion_{$lifeform}*species"]="Nuevo";
                        if(sizeof(DB::select("SELECT cientifico FROM especie_{$lifeform} WHERE cientifico=:value", [':value'=>$cientifico]))>0){
                          $obspost["row{$true_row}*observacion_{$lifeform}*species"]=$cientifico;
                        }
                      }
					}
					//Handle Date
					if (strpos($obscolumn, 'fecha') !== false){
						list($newdatevalue,$dateerror) = formatdate($obsvalue, $sheetobs, $letter, $row_number);
						if($dateerror==''){
							$obsvalue=$newdatevalue;
						}else{
							$errorlist[]=$dateerror;
						}
					}

					//Handle Invador
					if($obscolumn=='invasor'){
						$invasor= strtolower($obsvalue);
						if($invasor=='true' || $invasor=='si' || $invasor=='verdadero' ){
							$obsvalue='true';
						}else{
							$obsvalue='false';
						}
					}
					//Handle Radio
					if($obscolumn=='radio_0_30m'){
						$newobscolumn = 'cantidad';
						$obspost["row{$true_row}*observacion_{$lifeform}*radio"] = 'menos de 30m';
					}
					if($obscolumn=='radio_30m_o_mas'){
						$newobscolumn = 'cantidad';
						$obspost["row{$true_row}*observacion_{$lifeform}*radio"] = 'mas de 30m';
					}

					$oldmicrositio = [ 'fo_arbol','fo_arbusto','tr_arbol','tr_arbusto','ro','su'] ; 
					if(in_array($obscolumn,$oldmicrositio)){
						$newobscolumn = 'micrositio';
						$obsvalue=$obscolumn;
					}

					if($newobscolumn=='numero_de_individulos_capturados'){
						$newobscolumn = 'cantidad';
					}

					if($lifeform=='ave'){
						$obspost["row{$true_row}*observacion_{$lifeform}*especie_cactus"] = '000';
					}
					
					
                    $obspost["row{$true_row}*observacion_{$lifeform}*{$newobscolumn}"] = $obsvalue;
                    
                    $letter = ++$letter;
                  }
                  $true_row=$true_row+1;
                
                $row_number=$row_number+1;
                }
			}//end scan rows of observacions
			if (!$emptylocationsheet){
            	$obspostarray[]=$obspost;
			}
        }//end if LOC
      }//end looping through sheets

      //look at fotos
      $newpost=["submit"=>"true"];

      foreach ($uploadfotoarray as $fotonameexcel => $fotolifeform) {
        $foundfoto=false;
        $fotonum=0;
        foreach ($_FILES['photosFromUser']["name"] as $fotonamefile) {
          if ($fotonameexcel==$fotonamefile){
            $foundfoto=true;
            $iden_foto_result = uploadfoto($newpost,$fotonamefile, $_FILES["photosFromUser"]["tmp_name"][$fotonum], $_FILES["photosFromUser"]["size"][$fotonum], $fotolifeform);
            if ($iden_foto_result != $fotolifeform ."_". $fotonamefile){
              $errorlist[]=$iden_foto_result;
            }
          }
        $fotonum++;
        }
        if (!$foundfoto){
          $errorlist[]="{$fotonameexcel} no fue encontrado. Hay que subir fotos con los mismos nombres de los que estan en excel";
        }
      }
	  //Check if Medicion already exist
		if(sizeof($errorlist)==0){
			$spanishdate = substr($medicionpost['row0*medicion*fecha'], 3, 2) .'-'.  substr($medicionpost['row0*medicion*fecha'], 0, 2) .'-'. substr($medicionpost['row0*medicion*fecha'], 6);
			$checkold =trim(explode("(" , $medicionpost['selectlinea_mtp'])[0]).'*'.$spanishdate;
			if (sizeof(DB::select("Select iden from medicion where iden_nombre=?", [$checkold]))>0){
				$errorlist[]="Ya existe una medicion para esta linea y fecha.";
			}
		}
	  //save all if no errors

      if(sizeof($errorlist)==0){
		$newmedicion = savedata($medicionpost,$_FILES, $useremail,true);
        foreach ($obspostarray as $currentobspost) {
		  $currentobspost['selectmedicion'] = $newmedicion;
			
		  $saveworked = savedata($currentobspost,$useremail,true);
		  if ($saveworked=='false'){
			$errorlist[]="Sus datos no fueron guardados.";
		  }
          
        }
      }
  }//end if no errors

  if(sizeof($errorlist)==0 && sizeof(session('resultofquery'))>0){
	redirect()->to('/thanks')->send();
  }else{
    $errorlist[]="Sus datos no fueron guardados.";
  }

}
  session(['error' => $errorlist]);
?>