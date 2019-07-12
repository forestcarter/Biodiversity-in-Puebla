<nav class="mynavbar navbar navbar-expand-lg">
  <a class="navbar-brand" href="/mostrarmapas">Biodiversidad Peru</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarText" aria-controls="navbarText"
    aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="dropdown">
		<a class="dropbtn">Ingresar Datos 
		  <i class="fa fa-caret-down"></i>
		</a>
		<div class="dropdown-content">
		  <a href="/ingresardatos">Monitoreo de Biodiversidad</a>
		  <a href="/actividad">Monitoreo de Acciones</a>
		  <a href="/cambiar/linea">Linea MTP</a>
		</div>
	  </div>

  <div class="collapse navbar-collapse" id="navbarText">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item">
        <a class="nav-link" href="/descargar">Descargar Datos</a>
      </li> 
      <li class="nav-item">
        <a class="nav-link" href="/mostrarmapas">Mostrar Mapas</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="/ingresarexcel">Ingresar Excel</a>
      </li>
      
    </ul>

    <ul class="navbar-nav ml-auto">
      <?php
            $root_directory = "testdir";
                echo "<li class='nav-item'><a class='nav-link' href='/privacidad'>Privacidad</a></li>";

                if (session('email')) {
                    $useremail=session('email');
                    if (session('admin')){
                      echo "<li class='nav-item'><a class='nav-link' href='/admin'>Admin</a></li>";
                    }
                    echo "<li class='nav-item'><a class='nav-link' id='useremail' >{$useremail}</a></li>";

                    echo "<li class='nav-item'><a class='nav-link' href='/logout'>Logout</a></li>";

                } else {
                    echo "<li class='nav-item'><a class='nav-link' href='/login'>Login</a></li>";
                    echo "<li class='nav-item'><a class='nav-link' href='/register'>Registro</a></li>";
                }
                
        ?>
    </ul>
  </div>
</nav>
<div class='bodycontainer'>