<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('getintersection', function(Request $request) {
	$result=[];
	$addcolumnssql = "SELECT tablename, displayname, featurecolumn FROM additional_layers WHERE category='Gestion del Territorio'";
	$addcolumns = DB::select($addcolumnssql,[]);
	foreach ($addcolumns as $table) {
		$tablename=$table->tablename;
		$displayname=$table->displayname;
		$featurecolumn=$table->featurecolumn;

		$featurecolumnsql = "SELECT {$featurecolumn} as val FROM {$tablename} WHERE ST_Intersects(geom,(select geom from udp_puebla_4326 where iden=?))";
		$rawvalue=DB::select($featurecolumnsql,[$request->udpiden]);
		foreach ($rawvalue as $val) {
			$result[]=array('object'=>$displayname, 'name'=>$val->val);
		}
		// if (sizeof($rawvalue)!=0){
		// 	$value = $rawvalue->val;
		// 	$result[]=array('object'=>$displayName, 'name'=>$value);
		// }
		//$result[]=$rawvalue;
		
	}

	return $result;
});


Route::post('getRasterValue', function(Request $request) {
	$result="There was an error";
	$lat =  $request->lat;
	$lng =  $request->lng;
	$sql="SELECT ST_Value(rast, foo.pt_geom) AS rastval FROM temp_85_puebla CROSS JOIN (SELECT ST_SetSRID(ST_MakePoint({$lng},{$lat}), 4326) AS pt_geom) AS foo where st_intersects(rast,foo.pt_geom)";
        $result = DB::select($sql,[]); 
        if (sizeof($result)>0 ){
          	$value=$result[0]->rastval;
        }
	$final = json_encode($value);
	return $final;
  });
  


Route::post('getinffeatures', function(Request $request) {
  $result="There was an error";
  $north =  $request->north;
  $east =  $request->east;
  $south =  $request->south;
  $west =  $request->west;
  $udpiden = $request->udpiden;


  $sqludplineainf =   
    "SELECT gid, nombre 
    FROM infra_linea 
    where ST_Intersects(infra_linea.geom,
    (select geom from udp_puebla_4326 where iden={$udpiden}))";
    //udp and water points
  $sqludppuntoinf =   
    "SELECT gid, nombre 
    FROM infra_punto 
    where ST_Intersects(infra_punto.geom,
    (select geom from udp_puebla_4326 where iden={$udpiden}))";
  $sqludpmuni =   
    "SELECT nombre 
    FROM municipio
    where ST_Intersects(ST_SetSRID(municipio.geom, 4326),
    (select geom from udp_puebla_4326 where iden={$udpiden}))";
      
  $resultudplineainf = DB::select($sqludplineainf,[]);
  $resultudppuntoinf = DB::select($sqludppuntoinf,[]);
  $resultudpmuni = DB::select($sqludpmuni,[]);
  $munilist = [];
  foreach ($resultudpmuni as $munirow) {
    $munilist[] = $munirow->nombre;
  }

//Get length of inf lines

$infClasses=['BORDO','CALLE','CAMINO','CARRETERA','LINEA DE TRANSMISION'];
$newrow = new stdClass();
foreach ($infClasses as $infClass) {
  $inflength = 0;
  foreach($resultudplineainf AS $row2) {
    $infolinegid = $row2->gid;
    $lengthsql="SELECT ST_Length(ST_Transform(ST_INTERSECTION((select geom from infra_linea where gid = :gid and geografico = :infClass), (select geom from  udp_puebla_4326 where iden=:udpiden)),3857))";
    $lengthresult = DB::select($lengthsql,['gid'=>$infolinegid, 'infClass'=>$infClass, 'udpiden'=>$udpiden]);
    $inflength = $inflength + (float)($lengthresult[0]->st_length);
  }
  $lowerinfClass=strtolower($infClass);
  $newrow->$lowerinfClass=$inflength;
}
  
  $newrow->infCount=sizeof($resultudppuntoinf);
  $jsonnewrow = json_encode($newrow);

  return [
    json_encode($newrow),
    json_encode($munilist)
  ];
});


  






Route::post('getsuefeatures', function(Request $request) {
    $result="There was an error";
    $north =  $request->north;
    $east =  $request->east;
    $south =  $request->south;
    $west =  $request->west;
    $udpiden = $request->udpiden;

    $totalsql = "SELECT ST_Area((select geom from udp_puebla_4326 where iden={$udpiden}))";
    $totalarea=DB::select($totalsql,[])[0]->st_area;
    //Bounding box and soils 
    $sql =   
      "SELECT distinct descripcio, color
      FROM usos_de_suelo4 
      where ST_Intersects(usos_de_suelo4.geom,                        
      ST_GeomFromText('POLYGON(({$east} {$north},{$east} {$south},{$west} {$south},{$west} {$north},{$east} {$north}))',4326))";
    //udp and soils 
    $sqludp =   
      "SELECT gid, descripcio, color, 2 as aislado, {$totalarea} as totalarea, false as ismulti
      FROM usos_de_suelo4 
      where ST_Intersects(usos_de_suelo4.geom,
      (select geom from udp_puebla_4326 where iden={$udpiden}))";
    //udp and water lines 
    $sqludplineaagua =   
      "SELECT gid, nombre 
      FROM agua_lineas 
      where ST_Intersects(agua_lineas.geom,
      (select geom from udp_puebla_4326 where iden={$udpiden}))";
      //udp and water points
    $sqludppuntoagua =   
      "SELECT gid, nombre 
      FROM agua_puntos 
      where ST_Intersects(agua_puntos.geom,
      (select geom from udp_puebla_4326 where iden={$udpiden}))";

      //udp and water poli
    $sqludppoliagua =   
    "SELECT gid, nombre 
    FROM agua_poligonos 
    where ST_Intersects(agua_poligonos.geom,
    (select geom from udp_puebla_4326 where iden={$udpiden}))";

    //which munis interect with udp
    $sqludpmuni =   
    "SELECT nombre 
    FROM municipio
    where ST_Intersects(ST_SetSRID(municipio.geom, 4326),
    (select geom from udp_puebla_4326 where iden={$udpiden}))";
      
    if (is_numeric($north) && is_numeric($east) && is_numeric($south) && is_numeric($west)){
      $result = DB::select($sql,[]);
    }

    $resultudp = DB::select($sqludp,[]);

    $resultudplineaagua = DB::select($sqludplineaagua,[]);
    $resultudppuntoagua = DB::select($sqludppuntoagua,[]);
    $resultudppoliagua = DB::select($sqludppoliagua,[]);
    $resultudpmuni = DB::select($sqludpmuni,[]);

    $munilist = [];
    foreach ($resultudpmuni as $munirow) {
      $munilist[] = $munirow->nombre;
    }

    //Get length of water lines
    $agualength =0;
    foreach($resultudplineaagua AS $row2) {
      $agualinegid = $row2->gid;
 
      //get length of water lines in udp

      $lengthsql="SELECT ST_Length(ST_Transform(ST_INTERSECTION((select geom from agua_lineas where gid = ?), (select geom from  udp_puebla_4326 where iden=?)),3857))";
      $lengthresult = DB::select($lengthsql,[$agualinegid,$udpiden]);
      $agualength= $agualength + (float)($lengthresult[0]->st_length);
    }
    $aguaarea = 0;
    foreach($resultudppoliagua AS $row3) {
      $aguapoligid = $row3->gid;
      //get length of water lines in udp
      $areasql="SELECT ST_Area(ST_Transform(ST_INTERSECTION((select geom from agua_poligonos where gid = ?), (select geom from  udp_puebla_4326 where iden=?)),3857))";
      $arearesult = DB::select($areasql,[$aguapoligid,$udpiden]);
      $aguaarea= $aguaarea + (float)($arearesult[0]->st_area);
    }

    $multisql =" SELECT (ST_DUMP(ST_INTERSECTION((select geom from usos_de_suelo4 where gid = ?), 
          (select geom from udp_puebla_4326 where iden=?)
          ))).geom::geometry(Polygon,4326)";

    $newrows=[];
  
    foreach($resultudp AS $row) {
      $gid = $row->gid;
      //If soil is completely within, within set to 1. Unassigned is 2
      $withinsql ="SELECT ST_Within(usos_de_suelo4.geom, (select geom from udp_puebla_4326 where iden=?)) from usos_de_suelo4 where gid = ?";
      $withinresult = DB::select($withinsql,[$udpiden,$gid]);
      if ($withinresult[0]->st_within){
        $row->aislado=1;
      }else{
        $row->aislado=0;
      }

      
      $multiresult = DB::select($multisql,[$gid,$udpiden]);
      foreach($multiresult AS $patchrow) {
        $areasql ="SELECT ST_Area(?)";
        $arearesult = DB::select($areasql,[$patchrow->geom]);
        $newrow = new stdClass();
        $newrow->area=(float)($arearesult[0]->st_area);
        
        $newrow->descripcio=$row->descripcio;
        $newrow->totalarea=$row->totalarea;
        $newrow->color=$row->color;
        $newrow->aislado=$row->aislado;
      

        //Data that free-rides on resultudp
        $newrow->agualength=$agualength;
        $newrow->aguacount=sizeof($resultudppuntoagua)/2;
        $newrow->aguaarea=$aguaarea;
        $newrows[]=$newrow;
      }
    }
    
    

/////////////AGUA///////////
    class layer
    {
        public $tableName;
        public $displayName;
        public $color;
        public $fillColor;
        public $opacity;
        public $weight;
        public $fillOpacity;
    }

    $layer2 = new layer();
    $layer2->tableName = 'agua_lineas';
    $layer2->displayName = 'Agua Lineas';
    $layer2->featureColumn = 'nombre';
    $layer2->color = 'blue';
    $layer2->fillColor = 'blue';
    $layer2->opacity = 1;
    $layer2->weight = 1;
    $layer2->fillOpacity = 1;
    $layer2->sql = "SELECT nombre, ST_AsGeoJSON(geom, 5) AS geojson FROM agua_lineas
      where ST_Intersects(agua_lineas.geom,                        
      ST_GeomFromText('POLYGON(({$east} {$north},{$east} {$south},{$west} {$south},{$west} {$north},{$east} {$north}))',4326))";

    $layer3 = new layer();
    $layer3->tableName = 'agua_poligonos';
    $layer3->displayName = 'Agua Poligonos';
    $layer3->featureColumn = 'nombre';
    $layer3->color = 'black';
    $layer3->fillColor = 'blue';
    $layer3->opacity = 1;
    $layer3->weight = 1;
    $layer3->fillOpacity = 1;
    $layer3->sql = "SELECT nombre, ST_AsGeoJSON(geom, 5) AS geojson FROM agua_poligonos
      where ST_Intersects(agua_poligonos.geom,                        
      ST_GeomFromText('POLYGON(({$east} {$north},{$east} {$south},{$west} {$south},{$west} {$north},{$east} {$north}))',4326))";
      
    $layer4 = new layer();
    $layer4->tableName = 'agua_puntos';
    $layer4->displayName = 'Agua Puntos';
    $layer4->featureColumn = 'nombre';
    $layer4->color = 'black';
    $layer4->fillColor = 'black';
    $layer4->opacity = 1;
    $layer4->weight = 1;
    $layer4->fillOpacity = 1;
    $layer4->sql = "SELECT nombre, ST_AsGeoJSON(geom, 5) AS geojson FROM agua_puntos
      where ST_Intersects(agua_puntos.geom,                        
      ST_GeomFromText('POLYGON(({$east} {$north},{$east} {$south},{$west} {$south},{$west} {$north},{$east} {$north}))',4326))";

    $layersArray = array($layer2,$layer3,$layer4);
    foreach ($layersArray as $layer) {
      $features=[];
      $result2 = DB::select($layer->sql,[]);
        if (isset($result[0])){
          foreach($result2 AS $row2){
            if (isset($row2->geom)){
              unset($row2->geom);
            }
            $geometry=$row2->geojson=json_decode($row2->geojson);
            unset($row2->geojson);

            $row2->name=$layer->tableName;
            $row2->displayName=$layer->displayName;
            $row2->featureColumn=$layer->featureColumn;
            
            $feature=["type"=>"Feature", "geometry"=>$geometry, "properties"=>$row2];

            array_push($features, $feature);
            $featureCollection=["type"=>"FeatureCollection", "features"=>$features];
          }   
          if (isset($featureCollection)){
            $layer->geom=$featureCollection;
            unset($features);
            unset($featureCollection);
          }
        }
      
    }
/////////////////////////////////
    
    return [
      json_encode($result),
      json_encode($newrows),
      json_encode($layersArray[0]),
      json_encode($layersArray[1]),
      json_encode($layersArray[2]),
      json_encode($munilist)


    ];
});










Route::post('getudp', function(Request $request) {
    $lineamtp = $request->lineamtp;
    $medicion = $request->medicion;
    $observacion = $request->observacion;
    $punto = $request->punto;
    $transecto = $request->transecto;
    $useremail = $request->useremail;
    $lifeform=explode('_',$observacion)[1];

    //get linea ID
    $sql = "SELECT iden FROM linea_mtp WHERE nombre_iden=:lineamtp";
    $result = DB::select($sql, [':lineamtp'=>$lineamtp]);
    $lineanumber=$result[0]->iden;
    //get medicion ID
    $sql = "SELECT iden FROM medicion WHERE iden_linea_mtp=:lineanumber and iden_nombre=:medicion";
    $result = DB::select($sql, [':lineanumber'=>$lineanumber,':medicion'=>$medicion]);
    $medicionnumber=$result[0]->iden;
    //get medicion ID
    $sql = "SELECT iden FROM medicion WHERE iden_linea_mtp=:lineanumber and iden_nombre=:medicion";
    $result = DB::select($sql, [':lineanumber'=>$lineanumber,':medicion'=>$medicion]);
    $medicionnumber=$result[0]->iden;
    $locationinfo='non'; 
    $obresult=[];
    
    if ($lifeform=='ave' || $lifeform=='mamifero'){
        $sql = "SELECT * FROM punto_{$lifeform} WHERE iden_medicion=:medicionnumber and iden_sampling_unit=:punto and iden_email=:useremail";
        $result = DB::select($sql, [':medicionnumber'=>$medicionnumber,':punto'=>$punto,':useremail'=>$useremail]);
        if ($result){  
            $sql = "SELECT * FROM observacion_{$lifeform} JOIN especie_{$lifeform} ON observacion_{$lifeform}.iden_especie = especie_{$lifeform}.iden WHERE observacion_{$lifeform}.iden_punto=:punto and observacion_{$lifeform}.iden_email=:useremail";
            $obresult = DB::select($sql, [':punto'=>$result[0]->iden,':useremail'=>$useremail]);
        }
    }
    if ($lifeform=='hierba' || $lifeform=='herpetofauna'){
        $sql = "SELECT * FROM transecto_{$lifeform} WHERE iden_medicion=:medicionnumber and iden_sampling_unit=:transecto and iden_email=:useremail";
        $result = DB::select($sql, [':medicionnumber'=>$medicionnumber,':transecto'=>$transecto,':useremail'=>$useremail]);
        if ($result){ 
            $sql = "SELECT * FROM observacion_{$lifeform} JOIN especie_{$lifeform} ON observacion_{$lifeform}.iden_especie = especie_{$lifeform}.iden WHERE observacion_{$lifeform}.iden_transecto=:transecto and observacion_{$lifeform}.iden_email=:useremail";
            $obresult = DB::select($sql, [':transecto'=>$result[0]->iden,':useremail'=>$useremail]);
        }
    }
    if ($lifeform=='arbol' || $lifeform=='arbusto'){
        $sql = "SELECT * FROM punto_{$lifeform} WHERE iden_medicion=:medicionnumber and iden_sampling_unit=:transecto and iden_numero_punto62=:punto and iden_email=:useremail";
        $result = DB::select($sql, [':medicionnumber'=>$medicionnumber,':transecto'=>$transecto,':punto'=>$punto,':useremail'=>$useremail]);
        if ($result){ 
            $sql = "SELECT * FROM observacion_{$lifeform} JOIN especie_{$lifeform} ON observacion_{$lifeform}.iden_especie = especie_{$lifeform}.iden WHERE observacion_{$lifeform}.iden_punto=:punto and observacion_{$lifeform}.iden_email=:useremail";
            $obresult = DB::select($sql, [':punto'=>$result[0]->iden,':useremail'=>$useremail]);
        }
    }

    //$sql = "SELECT udp_puebla_4326.iden FROM udp_puebla_4326 WHERE ST_Intersects(udp_puebla_4326.geom,  ST_GeomFromText('POINT({$mylng} {$mylat})',4326))";
    //$result = DB::select($sql, []);
    //$result=[$lineamtp,$medicion,$observacion,$punto,$transecto];
    //return json_encode($request->lng);
    //return json_encode($result[0]->iden);
     
    $finalresults=[$result,$obresult];
    return json_encode($finalresults);
});

Route::post('getList', function(Request $request) {
    $table = $request->table;
	$column = $request->column;

	$wheresql='';
	if(isset($request->where)){
		$wheresql="WHERE {$request->where} LIKE '{$request->wherevalue}'";
	}

	$sql = "SELECT {$column} FROM {$table} {$wheresql} ";
	$result = DB::select($sql, []);
    return json_encode($result);
});

Route::post('getColumns', function(Request $request) {
    $table = $request->table;

	$sql = "SELECT column_name
		FROM information_schema.columns
		WHERE table_schema = 'public'
		AND table_name   = ?";

	$result = DB::select($sql, [$table]);
	
    return json_encode($result);
});




Route::post('getspecies', function(Request $request) {
    $lifeform = $request->lifeform;
    $idtype = $request->idtype;
    $idnumber= $request->idnumber;
    $useremail = $request->useremail;
    
    $adminsql = "SELECT administrador FROM usuario WHERE email=:useremail";
    $adminresult = DB::select($adminsql, [':useremail'=>$useremail]);
    $admin=$adminresult[0]->administrador;
    $useremailval=$useremail;
    if ($admin=="true"){
        $useremailval='%';
    }
    
    $transpunto='punto';
    if ($lifeform=='hierba'||$lifeform=='herpetofauna'){
        $transpunto='transecto';
    }
    $lifeform_riesgo=$lifeform;
    if ($lifeform=='hierba'||$lifeform=='arbol' ||$lifeform=='arbusto'){
        $lifeform_riesgo='planta';
    }
    $lineaextra='';
    if ($idtype=="linea_mtp"){
        $lineaextra="JOIN medicion ON {$transpunto}_{$lifeform}.iden_medicion = medicion.iden";
    }
    $arbolarbustoextra='';
    if ($lifeform=="arbusto" || $lifeform=="arbol" ){
        $arbolarbustoextra="AVG(pi() * (((observacion_{$lifeform}.dn)::real) / 2 ) ^2 )as ab,
		sum((observacion_{$lifeform}.distancia)::real) as distancia,
        AVG((observacion_{$lifeform}.dn)::real) as dn_raw,
        AVG((observacion_{$lifeform}.altura)::real) as altura_raw,
        ";
    }
    $hierbaextra='';
    if ($lifeform=="hierba"){
        $hierbaextra="SUM((observacion_hierba.i)::real) as sumi,
		SUM(1/(NULLIF(observacion_hierba.m::real,0))::real) as summ,
		count(lower(especie_hierba.cientifico)) AS intervalo,
        count(DISTINCT(observacion_hierba.iden_especie, observacion_hierba.iden_transecto,observacion_hierba.ind)) as cientifico_hierba,
        ";
    }
    $sql= "SELECT
        lower(especie_{$lifeform}.comun) as comun,
        lower(especie_{$lifeform}.cientifico) as cientifico ,
        especie_{$lifeform}.invasor,
        riesgo_{$lifeform_riesgo}.categoria,
        riesgo_{$lifeform_riesgo}.distribution,
        riesgo_{$lifeform_riesgo}.subespecie,
        count(DISTINCT(observacion_{$lifeform}.iden_{$transpunto})) as sitios,
        {$arbolarbustoextra}
        {$hierbaextra}
        count(lower(especie_{$lifeform}.cientifico)) AS total_cientifico
        FROM especie_{$lifeform}
            JOIN
        observacion_{$lifeform} ON especie_{$lifeform}.iden = observacion_{$lifeform}.iden_especie
            JOIN
            {$transpunto}_{$lifeform} ON observacion_{$lifeform}.iden_{$transpunto} = {$transpunto}_{$lifeform}.iden
            {$lineaextra}
            left JOIN
        riesgo_{$lifeform_riesgo} ON trim(lower(especie_{$lifeform}.cientifico)) = lower(CONCAT(trim(riesgo_{$lifeform_riesgo}.genero),' ',trim(riesgo_{$lifeform_riesgo}.especie)))
        where iden_{$idtype}={$idnumber} and observacion_{$lifeform}.iden_email like '{$useremailval}' and especie_{$lifeform}.cientifico!='0000' and especie_{$lifeform}.cientifico!='000' and especie_{$lifeform}.cientifico!='00'
        GROUP BY lower(especie_{$lifeform}.cientifico),lower(especie_{$lifeform}.comun),riesgo_{$lifeform_riesgo}.categoria, riesgo_{$lifeform_riesgo}.distribution,especie_{$lifeform}.invasor, riesgo_{$lifeform_riesgo}.subespecie";
 
    $obresult = DB::select($sql, []);

    $transpuntosql="SELECT
    observacion_{$lifeform}.iden_{$transpunto}
    FROM observacion_{$lifeform}
        JOIN
        {$transpunto}_{$lifeform} ON observacion_{$lifeform}.iden_{$transpunto} = {$transpunto}_{$lifeform}.iden
        {$lineaextra}
    where iden_{$idtype}={$idnumber} and observacion_{$lifeform}.iden_email like '%' and observacion_{$lifeform}.iden_especie!=1 
    GROUP BY observacion_{$lifeform}.iden_{$transpunto}
    ";


    $distsum=0;
    $numeroindiviudos=0;
    foreach ($obresult as $row7){
        $numeroindiviudos+=$row7->total_cientifico;
    } 
    foreach ($obresult as $row8){
      $row8->abundancia=$row8->total_cientifico;
      $row8->abundancia_relativa=round(100*($row8->total_cientifico)/$numeroindiviudos,2).'%';
      $transpuntoresult = DB::select($transpuntosql, []);
      $pointtotal = sizeof($transpuntoresult);
	  $row8->frequencia= (100*$row8->sitios)/$pointtotal;
      if (!($lifeform=="arbusto" || $lifeform=="arbol")) {
		  $row8->dominancia= round(pow(($row8->total_cientifico)/$numeroindiviudos,2),4);
      }

  } 

    

    if ($lifeform=="arbusto" || $lifeform=="arbol" ){
        $distsum=0;
        $numeroindiviudos=0;
        foreach ($obresult as $row){
          $row->dn= round(($row->dn_raw),4);
          $row->altura= round(($row->altura_raw),4);
          $distsum+=$row->distancia;
          $numeroindiviudos+=$row->total_cientifico;
        } 
        if ($numeroindiviudos>0){
			$distsum= 3406.223;
            $sumivi=0;
			$distanciamedia=$distsum/$numeroindiviudos;
			$area_deseada = 10000;
			$densidad_total= $area_deseada /($distanciamedia*$distanciamedia);
			
            
            
            $sumdensidad=0;
            $sumfrequencia=0;
            $sumdominancia=0;
            foreach ($obresult as $row2){
				//$distanciamedia=$row2->distancia/$row2->total_cientifico;
				$row2->dominancia= ($row2->ab)*$row2->total_cientifico;
                $row2->densidad= ($row2->total_cientifico / $numeroindiviudos) * $densidad_total;
                //$row2->frequencia= ($row2->sitios)/$pointtotal;
                $sumdensidad += $row2->densidad;
                $sumfrequencia += $row2->frequencia;
                $sumdominancia += $row2->dominancia;
            }
            foreach ($obresult as $row3){
				$row3->densidad_relativa = round(100*($row3->densidad )/$sumdensidad ,2).'%';
				$row3->densidad_total = round($densidad_total,2);

				//$row3->densidad_relativa = $row3->densidad;
                $row3->ivi= ($row3->densidad*100)/$sumdensidad + ($row3->frequencia*100)/$sumfrequencia + ($row3->dominancia*100)/$sumdominancia;
                $sumivi += $row3->ivi;
            } 
        }
    }

    if ($lifeform=="hierba"){
        $sumdelong=0;
		$numeroindiviudos=0;
		$numerointervalos=0;
        foreach ($obresult as $row){
			$numeroindiviudos+=$row->cientifico_hierba;
			$numerointervalos+=$row->intervalo;
            //$sumdelong+=$row->sumi;
        } 
        if ($numeroindiviudos>0){
            
            $transpuntoresult = DB::select($transpuntosql, []);
			$pointtotal = sizeof($transpuntoresult);
			$meterstotal=15;
			$area_deseada = 10000;
			$sumdelong=$pointtotal * $meterstotal;

            $sumdensidad=0;
            $sumdominancia=0;
            $sumfrequencia=0;
            foreach ($obresult as $row2){
                $row2->densidad= ($row2->summ)/(10000/$sumdelong);
                $row2->dominancia= ($row2->sumi)/$sumdelong *100;
                $row2->ponderacion= ($row2->summ)/($row2->cientifico_hierba);
				$row2->frequencia= ($row2->ponderacion)*$row2->intervalo;
				$row2->cobertura =100 * $row2->intervalo /  $numerointervalos;

                $row2->sv= (($sumdelong-$row2->dominancia)/$sumdelong)*100;
                $row2->cv= (($row2->dominancia)/$sumdelong)*100;
                //$row2->frequencia= $row2->summ; 
                $sumdensidad += $row2->densidad;
                $sumdominancia += $row2->dominancia;
                $sumfrequencia += $row2->frequencia;
                
            } 
            $sumivi=0;
            foreach ($obresult as $row3){
				$row3->densidad_relativa= round(100*($row3->densidad)/$sumdensidad,2).'%'; 
                $row3->ivi= ($row3->densidad*100)/$sumdensidad + ($row3->frequencia*100)/$sumfrequencia + ($row3->dominancia*100)/$sumdominancia;
                $sumivi += $row3->ivi; 
            }
        }
    }
    foreach ($obresult as $row4){
		$row4->cientifico= ucwords($row4->cientifico);
		$row4->comun= ucwords($row4->comun);

        if (isset($sumivi)){
			$row4->ivi100= round( ($row4->ivi*100)/$sumivi,4);
			$row4->ivi100= round($row4->ivi100,2) . '%';
            //if ($lifeform=="arbol") $row4->ivi100= $row4->ab;
			
            $row4->dominancia= round($row4->dominancia,4);
			$row4->densidad= round($row4->densidad,4);
            if ($lifeform=="hierba") {
				
				$row4->frequencia= round($row4->frequencia,4);
				$row4->cobertura= round($row4->cobertura,2) . '%'; 
				if($row4->frequencia=='0%'){
					$row4->frequencia='NA';
					$row4->dominancia='NA';
					$row4->densidad='NA';
				}
            }else{
				$row4->frequencia= round($row4->frequencia,2) . '%'; 
			}
        }else{
			$row4->ivi100='';
			$row4->frequencia= round($row4->frequencia,2) . '%'; 
        }
 
    }
    
    return json_encode([$obresult,$sql]);
});






