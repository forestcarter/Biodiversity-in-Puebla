
@include('inc/php_functions')

<?php
    if ($_SERVER['REQUEST_METHOD']=="POST") {
        uploadshape('shp');
        uploadshape('shx');
        uploadshape('dbf');
        uploadshape('prj');
        $shpfile=$_FILES['shp']["name"];
        echo `ogrinfo -al -so ./shp/{$shpfile}`;
            $sridshell= shell_exec("ogr2ogr -t_srs EPSG:4326 ./shp/{$shpfile} ./shp/{$shpfile}");
        echo `ogrinfo -al -so ./shp/{$shpfile}`;

        $shapenombre=$_POST['shapenombre'];
        if (env("APP_ENV", "somedefaultvalue")=='production'){
            //load to temp table 
            $db = env("DB_PASSWORD", "somedefaultvalue");
            $loadshp="shp2pgsql -I /var/www/html/lsapp3/public/shp/{$shpfile} {$shapenombre} | PGPASSWORD='{$db}' psql -U postgres -h localhost -d biodiversity3";
            $output= shell_exec($loadshp);
            //insert into geom usertable
            $copyshp="insert into usershapes (nombre, iden_email, geom) values (:nombre, :email, :geom)";
            $geom= DB::select("select geom from {$shapenombre}", []);
            $arraytopass=array(
                ":nombre"=> $shapenombre,
                ":email"=> session('email'),
                ":geom"=> $geom[0]->geom,
            );
            $results = DB::insert($copyshp, $arraytopass);
            echo 'worked';
            //delete temp table 
            if (strpos($output, 'ROLLBACK') == false) {
                DB::statement("drop table {$shapenombre}");
            }else{
            }
        }
    }
?>

@include('inc/header')
@include('inc/nav')

 <div class="display: flex p-5 m-5" style="text-align:center;">
    <div class=" d-inline-flex flex-column justify-content-center" style='width: 350px'>
    <p class="text-center h5">Cargar Shapes</p>
    <form id="login-form"  method="post" role="form" style="display: block;" enctype="multipart/form-data">
        {{ csrf_field() }}
    <div>
        <label for="shp" class="shapelabel">.shp</label>
        <input type="file" name="shp" id="shp">
    </div>
    <div>
        <label for="shx" class="shapelabel">.shx</label>
        <input type="file" name="shx" id="shx">
    </div>
    <div>
        <label for="dbf" class="shapelabel">.dbf</label>
        <input type="file" name="dbf" id="dbf">
    </div>
    <div>
        <label for="prj" class="shapelabel">.prj</label>
        <input type="file" name="prj" id="prj">
    </div>
    
    <div>
        <label for="shapenombre" class="shapenombre">nombre</label>
        <input type="text" name="shapenombre" id="shapenombre">
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
    echo '<script>console.log('.json_encode(session('resultofquery')).')</script>';
    session(['resultofquery' => '']);

    ?>