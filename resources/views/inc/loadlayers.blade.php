<?php
class layer
{
    public $tableName;
    public $displayName;
    public $color;
    public $fillColor;
    public $opacity;
    public $weight;
	public $fillOpacity;
	public $category;
}
$email=session('email');
$dbemail = session('admin') == 1 ? "%" : $email;
$layer1 = new layer();
$layer1->tableName = 'peru';
$layer1->displayName = 'Peru';
$layer1->featureColumn = 'gid';
$layer1->color = 'black';
$layer1->fillColor = 'red';
$layer1->opacity = 1;
$layer1->weight = 0.3;
$layer1->fillOpacity = 0.3;
$layer1->sql ="SELECT *, ST_AsGeoJSON(geom, 5)  AS geojson FROM peru"
;
$layer1->category='Referencial';

$layer2 = new layer();
$layer2->tableName = 'linea_mtp';
$layer2->displayName = 'Linea MTP';
$layer2->featureColumn = 'nombre_iden';
$layer2->color = 'blue';
$layer2->fillColor = 'black';
$layer2->opacity = 1;
$layer2->weight = 5;
$layer2->fillOpacity = 1;
$layer2->sql = "SELECT *, linea_mtp.nombre_iden, ST_AsGeoJSON(geom, 5) AS geojson FROM geom_count62_linea
LEFT JOIN linea_mtp ON geom_count62_linea.iden = linea_mtp.iden
where geom_count62_linea.iden_email like '{$dbemail}'";
$layer2->category='Monitoreo Activo';


$layer3 = new layer();
$layer3->tableName = 'usershapes';
$layer3->displayName = 'Predios';
$layer3->featureColumn = 'nombre';
$layer3->color = 'black';
$layer3->fillColor = 'orange';
$layer3->opacity = 0.5;
$layer3->weight = 1;
$layer3->fillOpacity = 0.5;
$layer3->sql = "SELECT nombre, ST_AsGeoJSON(geom, 5) AS geojson FROM usershapes where iden_email like '{$dbemail}'";
$layer3->category='Monitoreo Activo';


$layer4 = new layer();
$layer4->tableName = 'municipio';
$layer4->displayName = 'Municipios';
$layer4->featureColumn = 'nomgeo';
$layer4->color = 'black';
$layer4->fillColor = 'purple';
$layer4->opacity = 0.5;
$layer4->weight = 1;
$layer4->fillOpacity = 0.5;
$layer4->sql = "SELECT geometry_id,nomgeo, ST_AsGeoJSON(level_3, 5) AS geojson FROM muni_geometries_simplified";
$layer4->category='Referencial';

$layer5 = new layer();
$layer5->tableName = 'actividad';
$layer5->displayName = 'Acciones_punto';
$layer5->featureColumn = 'descripcion';
$layer5->color = 'black';
$layer5->fillColor = 'yellow';
$layer5->opacity = 0.9;
$layer5->weight = 1;
$layer5->fillOpacity = 0.5;
$layer5->sql = "SELECT descripcion,iden_geom, ST_AsGeoJSON(iden_geom, 5) AS geojson FROM actividad where tipo_geom='punto'";
$layer5->category='Referencial';

$layer6 = new layer();
$layer6->tableName = 'actividad';
$layer6->displayName = 'Acciones_poli';
$layer6->featureColumn = 'descripcion';
$layer6->color = 'black';
$layer6->fillColor = 'yellow';
$layer6->opacity = 0.9;
$layer6->weight = 1;
$layer6->fillOpacity = 0.5;
$layer6->sql = "SELECT descripcion,iden_geom, ST_AsGeoJSON(iden_geom, 5) AS geojson FROM actividad where tipo_geom='poligono'";
$layer6->category='Referencial';

//$layersArray = array($layer1, $layer2, $layer3, $layer4, $layer5, $layer6);
$layersArray = array($layer1, $layer2);

$addlayers = DB::select("SELECT * FROM additional_layers",[]);
$addlayers=[];
foreach($addlayers as $singlerow) {
    $templayer = new layer();
    $templayer->tableName = $singlerow->tablename;
    $templayer->displayName = $singlerow->displayname;
    $templayer->featureColumn = $singlerow->featurecolumn;
    $templayer->color = $singlerow->color;
    $templayer->fillColor = $singlerow->fillcolor;
    $templayer->opacity =$singlerow->opacity;
    $templayer->weight =$singlerow->weight;
    $templayer->fillOpacity = $singlerow->fillopacity;
    $templayer->sql = "SELECT {$singlerow->featurecolumn}, ST_AsGeoJSON(geom, 5) AS geojson FROM {$singlerow->tablename}";
	$templayer->category = $singlerow->category;
    $lowername=strtolower($templayer->tableName);
    $lowercolumn=strtolower($templayer->featureColumn);
    $checkfeaturesql= "SELECT * 
      FROM information_schema.columns 
      WHERE table_name='{$lowername}' and column_name='{$lowercolumn}'";
    if (sizeof(DB::select($checkfeaturesql,[]))>0 || $templayer->tableName == 'suelo_geometries_simplified' ){
      $layersArray[]=$templayer;
    }
}
$udpprocessed=[];
$lineaprocessed=[];
$incompletelines['test']=false;
 
foreach ($layersArray as $layer) {

    $features=[];
    $dslist=[];
    $tolist=[];
    $dsmax=[];
    $tomax=[];
    if (session('admin') && $layer->tableName=='usershapes'){
        $layer->sql = "SELECT nombre, ST_AsGeoJSON(geom, 5) AS geojson FROM usershapes";
    }
	$result = DB::select($layer->sql,[]);
	$categorylist=['ave','arbol', 'arbusto', 'mamifero', 'herpetofauna', 'hierba'];
	$minpuntos=['ave'=>5,'arbol'=>32, 'arbusto'=>32, 'mamifero'=>5, 'herpetofauna'=>4, 'hierba'=>4];

	$displayList=['total_observaciones', 'distinct_species', 'shannon', 'dominancia'];
	foreach($categorylist as $cat){
		$dslist[]="distinct_species_{$cat}";
		$tolist[]="total_observaciones_{$cat}";
		$defaultmax["total_observaciones_{$cat}"]=0;
		$defaultmax["distinct_species_{$cat}"]=0;
	}
	if (isset($result[0])){
		foreach($result AS $row) {
			if (isset($row->geom)){
				unset($row->geom);
			}
			$geometry=$row->geojson=json_decode($row->geojson);
			unset($row->geojson);
			$row->name=$layer->tableName;
			$row->displayName=$layer->displayName;
			$row->featureColumn=$layer->featureColumn;
			if ($layer->tableName=='udp_puebla_4326'){
				foreach($dslist as $ds){
					if($defaultmax[$ds]<$row->$ds){
					$defaultmax[$ds]=$row->$ds;
					}
				}
				foreach($tolist as $to){
					if($defaultmax[$to]<$row->$to){
						$defaultmax[$to]=$row->$to;
					}
				}
			}

			if ($layer->tableName=='udp_puebla_4326' && !in_array($row->iden, $udpprocessed)){
				foreach ($categorylist as $empty_lifeform) {


					$complete_life="complete_{$empty_lifeform}";
					$row->$complete_life='true'; 
					$row->complete_life='true'; 



					$emptysql="SELECT count(iden) from empty_medida_{$empty_lifeform} where iden=?";
					$emptyresult= DB::select($emptysql,[$row->iden]);
					$distinct_species="distinct_species_{$empty_lifeform}"; 
					if(intval($row->$distinct_species)==0 && $emptyresult[0]->count==0){ 
						foreach ($displayList as $displaycolumn) {
							$column_to_change= "{$displaycolumn}_{$empty_lifeform}";
							$row->$column_to_change='NM';
						}
					}
				}
				$udpprocessed[]=$row->iden;
			}

			if ($layer->tableName=='linea_mtp' && !in_array($row->iden, $lineaprocessed)){
				foreach ($categorylist as $empty_lifeform) {
					$emptysql="SELECT count(iden) from empty_medida_linea_{$empty_lifeform} where iden=?";
					$emptyresult= DB::select($emptysql,[$row->iden]);
					$distinct_species="distinct_species_{$empty_lifeform}"; 
					if(intval($row->$distinct_species)==0 && $emptyresult[0]->count==0){ 
						foreach ($displayList as $displaycolumn) {
							$column_to_change= "{$displaycolumn}_{$empty_lifeform}";
							$row->$column_to_change='NM';
						}
					}
					$complete_life="complete_{$empty_lifeform}";
					$row->$complete_life='false';

					$transpunto='punto';
					if ($empty_lifeform=='hierba'||$empty_lifeform=='herpetofauna'){
						$transpunto='transecto';
					}
					$arbolextra="";

					if($empty_lifeform=='arbol' ||$empty_lifeform=='arbusto'){
						$arbolextra=",punto_{$empty_lifeform}.iden_numero_punto62";
					}
					
					$iscompletesql= "SELECT 
					count(DISTINCT(observacion_{$empty_lifeform}.iden_{$transpunto}{$arbolextra}))
					FROM especie_{$empty_lifeform}
						JOIN
					observacion_{$empty_lifeform} ON especie_{$empty_lifeform}.iden = observacion_{$empty_lifeform}.iden_especie
						JOIN
						{$transpunto}_{$empty_lifeform} ON observacion_{$empty_lifeform}.iden_{$transpunto} = {$transpunto}_{$empty_lifeform}.iden
						JOIN medicion ON {$transpunto}_{$empty_lifeform}.iden_medicion = medicion.iden
					where iden_linea_mtp=? and especie_{$empty_lifeform}.cientifico!='0000'";

					$emptyresult= DB::select($iscompletesql,[$row->iden]);
					
					if(sizeof($emptyresult)>0 && $emptyresult[0]->count >= $minpuntos[$empty_lifeform]){
						$row->$complete_life='true'; 
					}else{
						$row->$complete_life='false'; 
						$udpsql = "SELECT udp_puebla_4326.iden, udp_puebla_4326.geom FROM udp_puebla_4326 WHERE 
						ST_Intersects(udp_puebla_4326.geom, (SELECT iden_geom from linea_mtp where iden = ?))";
						$udpresult= DB::select($udpsql,[$row->iden]);
						foreach ($udpresult as $udp) {
							$incompletelines[$udp->iden] = "false";
						}

					}
				}
				$lineaprocessed[]=$row->iden;
			}

			$feature=["type"=>"Feature", "geometry"=>$geometry, "properties"=>$row];
			array_push($features, $feature);
			$featureCollection=["type"=>"FeatureCollection", "features"=>$features];
		}   
	$layer->geom=$featureCollection;
	unset($features);
	unset($featureCollection);
	$defaultmaxjson[$layer->tableName]=json_encode($defaultmax);
	}


	
		

	
}


foreach($layersArray[0]->geom['features'] as $row4) {
	$currentiden = $row4['properties']->iden;
	if (isset($incompletelines[$currentiden])){
		$row4['properties']->complete_life='false';
	}
}

	

$geojson=json_encode($layersArray);

?>
<script>
    var something = {!! $geojson !!};
    var defaultmax = 100;
</script>



