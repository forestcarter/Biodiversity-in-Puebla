
@include('inc/php_functions')

<?php
    $errorlist=[];
    if ($_SERVER['REQUEST_METHOD']=="POST"&& (!session('visitante'))){
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
            $loadshp="shp2pgsql -I -s 4326:4326 ../storage/shp/{$shpfile}2 {$shapenombre} | PGPASSWORD='{$db}' psql -U plataforma -h localhost -d {$dbname}";
            
            $output= shell_exec($loadshp);
            $output2= shell_exec("rm -rf ../storage/shp/*");

                if (strpos($output, 'ROLLBACK') == false) {
                    //insert into geom usertable
                    $copyshp="insert into usershapes (nombre, iden_email, geom) values (:nombre, :email, :geom)";
                    $geom= DB::select("select geom from {$shapenombre}", []);
                    if (isset($geom[0])){
                        foreach($geom as $ge){
                            $arraytopass=array(
                                ":nombre"=> $shapenombre,
                                ":email"=> session('email'),
                                ":geom"=> $ge->geom,
                            );
                            $results = DB::insert($copyshp, $arraytopass);
                        }
                    
                    DB::statement("drop table {$shapenombre}");
                    return redirect()->to('/thanks')->send();
                }else{
                    $errorlist[]= "Su shape no tiene polygono";
                }
            }else{
                $errorlist[]= "Por favor, cambie el nombre de su shape ";
            }
        }else{
			echo "This is not production";
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
        <label for="shapenombre" class=" h4 shapenombre">nombre</label>
        <input type="text" name="shapenombre" id="shapenombre">
    </div>
    <div>
        <label for="shp" class="h4 shapelabel">.shp</label>
        <input type="file" name="shp" id="shp">
    </div>
    <div>
        <label for="shx" class="h4 shapelabel">.shx</label>
        <input type="file" name="shx" id="shx">
    </div>
    <div>
        <label for="dbf" class="h4 shapelabel">.dbf</label>
        <input type="file" name="dbf" id="dbf">
    </div>
    <div>
        <label for="prj" class="h4 shapelabel">.prj</label>
        <input type="file" name="prj" id="prj">
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