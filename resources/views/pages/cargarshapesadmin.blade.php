
@include('inc/php_functions')

<?php 
if (!session('admin')){
    return redirect()->to('/login')->send();
}

    $errorlist=[];
    if ($_SERVER['REQUEST_METHOD']=="POST"){
        $filearray=['shp','shx','dbf','prj'];
        $base = substr($_FILES['prj']["name"],0,-4);
        foreach( $filearray as $filetype) {
            if (!$_FILES[$filetype]["name"]) {
                $errorlist[]= "Faulta {$filetype} ";
            }
            $test = substr($_FILES[$filetype]["name"],0,-4);
            if (substr($_FILES[$filetype]["name"],-4,4) !== ".{$filetype}" ) {
                $errorlist[]= "{$filetype} no tiene extension .{$filetype} ";
            }
            if (substr($_FILES[$filetype]["name"],0,-4) != $base ) {
                $message="Todos los cargars debe de tener lo mismo nombre";
                if (!in_array($message, $errorlist)){
                    $errorlist[]= $message;
                }
            }
        }
        if (!$_POST['shapenombre']) {
            $errorlist[]= "Faulta nombre ";
        }
    }

    if ($_SERVER['REQUEST_METHOD']=="POST" && sizeof($errorlist)==0 && (!session('visitante'))  ){
        uploadshape('shp');
        uploadshape('shx');
        uploadshape('dbf');
        uploadshape('prj');
        $shpfile=$_FILES['shp']["name"];
        $sridshell= shell_exec("ogr2ogr -t_srs EPSG:4326 ../storage/shp/{$shpfile}2 ../storage/shp/{$shpfile}");
        $shapenombre=$_POST['shapenombre'];
       
        if (env("APP_ENV", "somedefaultvalue")=='production'){
        
            //load to temp table 

            $db = env("DB_PASSWORD", "somedefaultvalue");
            $dbname = env("DB_DATABASE", "somedefaultvalue");
            //$loadshp="shp2pgsql -I -s 4326:4326 ../storage/shp/{$shpfile}2 {$shapenombre} | PGPASSWORD='{$db}' psql -U postgres -h localhost -d {$dbname}";
            $loadshp="shp2pgsql -W LATIN1 -I -s 4326:4326 ../storage/shp/{$shpfile}2 {$shapenombre} | PGPASSWORD='{$db}' psql -U plataforma -h localhost -d {$dbname}";
            
            $output= shell_exec($loadshp);
            $output2= shell_exec("rm -rf ../storage/shp/*");
                if (strpos($output, 'ROLLBACK') == false) {
                    $geom= DB::select("select geom from {$shapenombre}", []);
                    if (isset($geom[0])){
                        $arraytopass = [$_POST['shapenombre'],$_POST['displayname'],strtolower($_POST['featurecolumn']),$_POST['lineacolor']];
                        array_push( $arraytopass,$_POST['fillcolor'],$_POST['fillopacidad'],$_POST['lineaopacidad'],$_POST['lineaanchura'],$_POST['category'] );
                        $layerresult= DB::insert("INSERT into additional_layers (tablename, displayname, featurecolumn, color,fillcolor,fillopacity,opacity,weight,category) values (?,?,?,?,?,?,?,?,?)", $arraytopass);
                        return redirect()->to('/thanks')->send();
                    }else{
                        $errorlist[]= "Su shape no tiene polygono";
                    }
            }else{
                $errorlist[]= "Por favor, cambie el nombre de su shape ";
            }
        }
    }
?>

@include('inc/header')
@include('inc/nav')

 <div class="display: flex p-5 m-5" style="text-align:center;">
    <div class=" d-inline-flex flex-column justify-content-center" style='width: 400px'>
    <p class="text-center h2">Cargar Shapes</p>
    <div class=" warnings">
        <?php


                $hint1="A veces no salga correcto si la proyección no es ESPG:4326";
                echo "<p class='text-dark text-center' style='background-color: lightsteelblue;'>{$hint1}</p>";
                
            if (sizeof($errorlist)>0){
                foreach ($errorlist as $msg) {
                    echo "<p class='bg-danger2 text-center'>{$msg}</p>";
                }
            }
        ?>
    </div>
    <form id="login-form"  method="post" role="form" style="display: block;" enctype="multipart/form-data">
        {{ csrf_field() }}
    <div>
        <label for="shapenombre" class="h6 shapenombre">Nombre de tabla</label>
        <input type="text" placeholder="nombre_de_tabla" required name="shapenombre" id="shapenombre">
    </div>
    <div>
        <label for="displayname" class="h6 displayname">Nombre para Mostrar</label>
        <input type="text" placeholder="Nombre de Capa" required name="displayname" id="displayname" >
    </div>
    <div>
        <label for="featurecolumn" class="h6 shapenombre">Click Campo</label>
        <input type="text"  required name="featurecolumn" id="featurecolumn">
    </div>
    <div>
        <label for="fillcolor" class=" h6 shapenombre">Fill Color</label>
        <input type="color" required name="fillcolor" id="fillcolor">
    </div>
    <div>
        <label for="lineacolor" class=" h6 shapenombre">Linea Color</label>
        <input type="color"  required name="lineacolor" id="lineacolor">
    </div>
    <div>
        <label for="fillopacidad" class=" h6 shapenombre">Fill Opacidad</label>
        <input type="number" min=0 max=1 step = 0.1 required name="fillopacidad" id="fillopacidad">
    </div>
    <div>
        <label for="lineaopacidad" class=" h6 shapenombre">Linea Opacidad</label>
        <input type="number" min=0 max=1 step = 0.1 required name="lineaopacidad" id="lineaopacidad">
    </div>
    <div>
        <label for="lineaanchura" class=" h6 shapenombre">Linea Anchura</label>
        <input type="number" min=0 max=5 step = 0.1 required name="lineaanchura" id="lineaanchura">
	</div>
	<div>
		<label for="category" class=" h6 shapenombre">Category</label>
		<select id ='category' name ='category'>
			<option value="Referencial">Referencial</option>
			<option value="Monitoreo Activo">Monitoreo Activo</option>
			<option value="Gestion del Territorio">Gestion del Territorio</option>
		  </select>
	</div>

    <div>
        <label for="shp" class="h6 shapelabel">.shp</label>
        <input type="file" required name="shp" id="shp">
    </div>
    <div>
        <label for="shx" class="h6 shapelabel">.shx</label>
        <input type="file" required name="shx" id="shx">
    </div>
    <div>
        <label for="dbf" class="h6 shapelabel">.dbf</label>
        <input type="file" required name="dbf" id="dbf">
    </div>
    <div>
        <label for="prj" class="h6 shapelabel">.prj</label>
        <input type="file" required name="prj" id="prj">
	</div>
	
    
    
    <div class="row">
        <input type="submit" name="login-submit" id="login-submit" tabindex="4" class="form-control btn btn-success p-15" value="Enviar">
    </div>
    </form>

    </div>
</div>
<br>
<br>

<br>

<br>


@include('inc/footer')

<?php
    session(['resultofquery' => '']);

    ?>