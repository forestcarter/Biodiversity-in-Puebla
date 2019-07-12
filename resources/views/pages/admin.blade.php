<?php 
if (!session('admin')){
	return redirect()->to('/login')->send();
}
$allcolumns=[];
$addcolumnssql = "SELECT tablename, displayname FROM additional_layers WHERE tablename!='suelo_geometries_simplified'";
$addcolumns = DB::select($addcolumnssql,[]);
foreach ($addcolumns as $table) {
	$tablename=$table->tablename;
	$displayname=$table->displayname;
	$featurecolumnsql = "SELECT column_name FROM information_schema.columns WHERE table_name=?";
	$allcolumns[$displayname]=DB::select($featurecolumnsql,[strtolower($tablename)]);
}
$allcolumns['Uso de Suelo'] =[['column_name'=>'descripcio']];


$geojson=json_encode($allcolumns);

?>
<script>
	var allcolumns = {!! $geojson !!};
</script>





  @include('inc/saveadmin')
  @include('inc/setuppage')
  @include('inc/header')
  @include('inc/nav')

<img src="{{ asset('img/popo.jpg') }}" alt="Italian Trulli" style="height:250px; width:380px;">
<div>
  <div class="row">
    <div class="warnings">
      <?php
			if (session('adminerror')) {
				$msg=session('adminerror');
				echo "<p class='bg-info text-center'>{$msg}</p>";
				
			}
			session(['adminerror'=>'']);

		?>
    </div>
  </div>

  <div class="wrapper2" id="startMenuDiv">

    <div class='border border-dark p-2 m-2'>
      <form id="measurementform" method="post">
        {{ csrf_field() }}

        <h3 id="measurement3">Cambiar Usuario Activo</h3>
        <table class="mytable">
          <tbody id="measurementTBodySelect">
          </tbody>
        </table>
        <input type="radio" name="admin_option" value="add_admin"> Dar Privilegio Admin<br>
		<input type="radio" name="admin_option" value="remove_admin"> Borrar Privilegio Admin<br>
		<input type="radio" name="admin_option" value="cambio"> Permitir cambio de contraseña (dile que su código es su email)<br>
        <input type="radio" name="admin_option" value="delete_user"> Borrar Ususario
        <br>
        <br>
        <input type="hidden" name="action" value="activo">
        <input type="submit" id="measurementlinea_mtpSubmit" class="mySubmit">

      </form>
    </div>
    <br>
    <br>
    <div class='border border-dark p-2 m-2'>
      <form id="measurementform" method="post">
        {{ csrf_field() }}

        <h3 id="measurement3">Cambiar Emails Permitidos</h3>
        <table class="mytable">
          <tbody id="measurementTBodyMedicion">
          </tbody>
        </table>

        <input type="radio" name="admin_option" value="remove_email"> Borrar Email <br>
        <input type="radio" name="admin_option" value="add_email"> Añadir Email Nuevo
        <input type="text" name="email_input">
        <input type="checkbox" name="visitante" value="1"> *Visitante<br>

        <br>
        <br>
        <input type="hidden" name="action" value="permitido">
        <input type="submit" id="measurementlinea_mtpSubmit" class="mySubmit">
      </form>
	</div>
	
	<div class='border border-dark p-2 m-2'>
		<form id="measurementform" method="post">
		  {{ csrf_field() }}
  
		  <h3 id="measurement3">Borrar Medicion</h3>
		  <table class="mytable">
			<tbody id="measurementTBodyborrarmedicion">
			</tbody>
		  </table>
		 
		  <label  > <span class='dropDownTitles' style="white-space:nowrap">Tipo</span>
		  <select id='borrar_forma_de_vida' name='borrar_forma_de_vida' class='p-2 narrowSelect'>
			<option value="not_selected"></option>
			<option value="medicion_completa">medicion completa</option>
			<option value="ave">ave</option>
			<option value="arbol">arbol</option>
			<option value="arbusto">arbusto</option>
			<option value="heirba">heirba</option>
			<option value="mamifero">mamifero</option>
			<option value="herpetofauna">herpetofauna</option>
		  </select>
		</label>
		

		  <br>
		  <input type="hidden" name="action" value="borrarmedicion">
		  <input type="submit" value= 'Borrar' id="measurementlinea_mtpSubmit" class="mySubmit">
		</form>
	  </div>
    <br>
	<br>

	<div class='border border-dark p-2 m-2'>
		<form id="measurementform" method="post">
		  {{ csrf_field() }}
  
		  <h3 id="measurement3">Cambiar Especie</h3>
		 
		  <label  > <span class='dropDownTitles' style="white-space:nowrap">Tipo</span>
		  <select id='cambiar_especie' name='cambiar_especie' class='p-2 narrowSelect'>
			<option value="not_selected"></option>
			<option value="especie_ave">ave</option>
			<option value="especie_arbol">arbol</option>
			<option value="especie_arbusto">arbusto</option>
			<option value="especie_heirba">heirba</option>
			<option value="especie_mamifero">mamifero</option>
			<option value="especie_herpetofauna">herpetofauna</option>
		  </select>
		</label>
		  <br>
		  <input type="hidden" name="action" value="cambiar_especie">
		  <input type="submit" value= 'cambiar_especie' class="mySubmit">
		</form>
	  </div>
    <br>
	<br>
	

    <div class='border border-dark p-2 m-2'>
      <form id="measurementform" method="post">
        {{ csrf_field() }}
        <h3 id="measurement3">Cambiar Campos</h3>
        <br>

        <br>
        <h5 class="font-weight-bold">Borrar Campo
          <h5>
            <div class="row">
              <div class="form-group col-6 border border-secondary p-1 mx-3">
                <label for="table_option">Eliger Tabla</label>
                <select name='table_option' id='table_option' class='table_option form-control'>
                  <option value="not_selected"></option>
                  <option value="transecto_hierba">transecto_hierba</option>
                  <option value="transecto_herpetofauna">transecto_herpetofauna</option>
                  <option value="punto_arbol">punto_arbol</option>
                  <option value="punto_arbusto">punto_arbusto</option>
                  <option value="punto_mamifero">punto_mamifero</option>
                  <option value="punto_ave">punto_ave</option>
                  <option value="observacion_hierba">observacion_hierba</option>
                  <option value="observacion_herpetofauna">observacion_herpetofauna</option>
                  <option value="observacion_arbol">observacion_arbol</option>
                  <option value="observacion_arbusto">observacion_arbusto</option>
                  <option value="observacion_mamifero">observacion_mamifero</option>
                  <option value="observacion_ave">observacion_ave</option>
                </select>
                <label for="field_option">Eliger Campo</label>
                <select name='field_option' id='field_option' class='field_option form-control'></select>
                <input type="hidden" name="action" value="delete_field">
                <input type="submit" id="deletecampo" class="mySubmit">
              </div>
            </div>
      </form>

      <br>
      <form id="measurementform" method="post">
        {{ csrf_field() }}
        <h5 class="font-weight-bold">Guardar Campo
          <h5>
            <div class="row">
              <div class="form-group col-6 border border-secondary p-1 mx-3">

                <input type="text" name="field_input" placeholder="Nombre del Campo"><br>
                <label for="table_option2">Eliger Tabla</label>
                <select name='table_option2' id='table_option2' class='table_option2 form-control'>
                    <option value="not_selected"></option>
                    <option value="transecto_hierba">transecto_hierba</option>
                    <option value="transecto_herpetofauna">transecto_herpetofauna</option>
                    <option value="punto_arbol">punto_arbol</option>
                    <option value="punto_arbusto">punto_arbusto</option>
                    <option value="punto_mamifero">punto_mamifero</option>
                    <option value="punto_ave">punto_ave</option>
                    <option value="observacion_hierba">observacion_hierba</option>
                    <option value="observacion_herpetofauna">observacion_herpetofauna</option>
                    <option value="observacion_arbol">observacion_arbol</option>
                    <option value="observacion_arbusto">observacion_arbusto</option>
                    <option value="observacion_mamifero">observacion_mamifero</option>
                    <option value="observacion_ave">observacion_ave</option>
                </select>
                <label for="datatype">Eliger Tipo de data</label>
                <select name='datatype' id='datatype' class='table_option form-control'>
                    <option value="not_selected"></option>
                    <option value="varchar">varchar</option>
                </select>
                <input type="hidden" name="action" value="add_field">
                <input type="submit" id="campo" class="mySubmit">
              </div>
            </div>
            <br>
            <br>
      </form>
    </div>

    <div class='border border-dark p-2 m-2'>
      <form id="measurementform" method="post">
        {{ csrf_field() }}

        <h3 id="measurement3">Cambiar Capas</h3>
        <table class="mytable">
          <tbody id="measurementTBodycargar">
          </tbody>
        </table>
        <input type="checkbox" name="deletetable" value="1"> Borrar completamente<br>

        <div>
          <label for="displayname" class=" h6 displayname">Nombre para Mostrar</label>
          <input type="text" required name="displayname" id="displayname">
        </div>
        <div>
          <label for="campoclick" class=" h6 displayname">Campo Click</label>
          <select name="campoclick" id="campoclick"></select>
		</div>
        <div>
          <label for="fillcolor" class=" h6 shapenombre">Fill Color</label>
          <input type="color" required name="fillcolor" id="fillcolor">
        </div>
        <div>
          <label for="lineacolor" class=" h6 shapenombre">Linea Color</label>
          <input type="color" required name="lineacolor" id="lineacolor">
        </div>
        <div>
          <label for="fillopacidad" class=" h6 shapenombre">Fill Opacidad</label>
          <input type="number" min=0 max=1 step=0 .1 required name="fillopacidad" id="fillopacidad">
        </div>
        <div>
          <label for="lineaopacidad" class=" h6 shapenombre">Linea Opacidad</label>
          <input type="number" min=0 max=1 step=0 .1 required name="lineaopacidad" id="lineaopacidad">
        </div>
        <div>
          <label for="lineaanchura" class=" h6 shapenombre">Linea Anchura</label>
          <input type="number" min=0 max=5 step=0 .1 required name="lineaanchura" id="lineaanchura">
		</div>
		<div>
			<label for="category" class=" h6 shapenombre">Category</label>
			<select id ='category' name ='category'>
				<option value="Referencial">Referencial</option>
				<option value="Monitoreo Activo">Monitoreo Activo</option>
				<option value="Gestion del Territorio">Gestion del Territorio</option>
			  </select>
		</div>
		<br>
		

        <input type="hidden" name="action" value="cargarshape">
        <input type="submit" id="measurementlinea_mtpSubmit" class="mySubmit">
      </form>
      
    </div>
	<div style='text-align:center;'>
        <a class="btn btn-primary m-2 btn-sm mapInfoButton" href="/cargarshapesadmin" role="button">Cargar Shapefile</a>
    </div>
  </div>
  <script src="{{ asset('js/jsfunc.js') }}">
  </script>

   <script>
		var mySelection = document.getElementById('measurementadditional_layerscargar')
	   	mySelection.addEventListener("change", function (){
			var tableName = mySelection.value
			if (tableName=='notselected') return
			let frag = document.createDocumentFragment(),elOption;
			for (let i = 0; i<allcolumns[tableName].length; i++){
				elOption = frag.appendChild(document.createElement('option'));
				elOption.value = allcolumns[tableName][i]['column_name'];
				elOption.innerHTML = allcolumns[tableName][i]['column_name'];
			}
			var campoClick = document.getElementById('campoclick')
			while (campoClick.hasChildNodes()) {
				campoClick.removeChild(campoClick.lastChild);
			}
			campoClick.appendChild(frag)
	   })
   </script>

  @include('inc/footer')