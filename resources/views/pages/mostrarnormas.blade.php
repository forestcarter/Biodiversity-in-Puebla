<?php
if (!session('email')){
    return redirect()->to('/login')->send();
}
$geojsonidennum=json_encode($idenudp);
$geojsoninfotype=json_encode($infotype);
$myheader= 'Especies y Normas 059 de UDP '.$idenudp;
if ($infotype=='ae'){
  $myheader= 'Attributos Ecologicos de UDP '.$idenudp;
} 
?>
  <script>
    var idennum = {!! $geojsonidennum !!};
    var infotype = {!! $geojsoninfotype !!};
  </script>

  @include('inc/header')
  @include('inc/nav')

  <div class="container">
    <h2 class="text-center">
      <?php echo $myheader ?>
    </h2>
    <p></p>
  </div>

  <div id="app"></div>
  <script src="{{ asset('/leaflet_assets/normasindex.js') }}"></script>
  @include('inc/footer')