<?php

require 'vendor/autoload.php';
include 'enviroment.php';
use Monolog\Logger;
use PHPExiftool\Reader;
use PHPExiftool\Driver\Value\ValueInterface;
use PHPExiftool\Writer;
use PHPExiftool\Driver\Metadata\Metadata;
use PHPExiftool\Driver\Metadata\MetadataBag;
use PHPExiftool\Driver\Tag\IPTC\ObjectName;
use PHPExiftool\Driver\Tag\GPS\GPSLongitude;
use PHPExiftool\Driver\Tag\GPS\GPSLatitude;
use PHPExiftool\Driver\Tag\GPS\GPSLatitudeRef;
use PHPExiftool\Driver\Tag\GPS\GPSLongitudeRef;
use PHPExiftool\Driver\Value\Mono;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\TimeCode;

/* Cargar video y obtener duracion */
$logger = new Logger('exiftool');
$pathVideo = 'datos//prueba.mp4';
$pathFrame = 'datos//frame.jpg';
/* Extrear coordenadas */
$reader = Reader::create($logger);
$metadatas = $reader->files($pathVideo)->first();
$gpsLocation = '';
foreach ($metadatas as $metadata) {
    if($metadata->getTag() == 'Composite:GPSPosition')
        $gpsLocation = $metadata->getValue()->asString();
}

$latAndLon = explode(' ',$gpsLocation);


$ffprobe = FFProbe::create();
$duration = $ffprobe->format($pathVideo)->get('duration'); 

/* Obtener tiempo random para extraer el frame*/
$min = 0;
$max = $duration;
$secondRand = rand($min,$max);

/* Obtener frame para manipular */
$ffmpeg = FFMpeg::create();
$video = $ffmpeg->open($pathVideo);
$video->filters()
    ->resize(new Dimension(320, 240))
    ->synchronize();
$video
    ->frame(TimeCode::fromSeconds($secondRand))
    ->save($pathFrame);

/*$bag = new MetadataBag();

$bag->add(new Metadata(new GPSLongitude(),new Mono($latAndLon[1])));
$bag->add(new Metadata(new GPSLongitudeRef(),new Mono('W')));
$bag->add(new Metadata(new GPSLatitude(),new Mono($latAndLon[0])));
$bag->add(new Metadata(new GPSLatitudeRef(),new Mono('S')));

$writer = Writer::create($logger);
$writer->write($pathFrame, $bag);
*/

$metadatas = $reader->files($pathFrame)->first();
$gpsLocation = '';
foreach ($metadatas as $metadata) {
    if($metadata->getTag() == 'Composite:GPSPosition')
        $gpsLocation = $metadata->getValue()->asString();
}
$latAndLon = explode(' ',$gpsLocation);

?>

<!DOCTYPE html>
<html>
  <head>
  <style type="text/css">
      html { height: 100% }
      body { height: 100%; margin: 0; padding: 0 }
      #mapa_div { height: 100% }
    </style>
    <script type="text/javascript"
      src="http://maps.googleapis.com/maps/api/js?key=<?php echo $key;?>">
    </script>
    <script type="text/javascript">
      function inicializar_mapa() {
        var mapOptions = {
          center: new google.maps.LatLng(<?php echo $latAndLon[0]; ?>,<?php echo $latAndLon[1]; ?> ),
          zoom: 10,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        var map = new google.maps.Map(document.getElementById("mapa_div"),
            mapOptions);

        var marker = new google.maps.Marker({
          position: {lat: <?php echo $latAndLon[0]; ?>, lng: <?php echo $latAndLon[1]; ?>},
          map: map,
	      title: 'Vista'
        });
        console.log('<?php echo $pathFrame; ?>')
        var contentString = "<img src='<?php echo $pathFrame; ?>' style='width:320px;height:240px'>"
        const infowindow = new google.maps.InfoWindow({
            content: contentString,
        });

        marker.addListener("click", () => {
            infowindow.open({
                anchor: marker,
                map,
                shouldFocus: false,
            });
        });
      }
    </script>
  </head>
  <body onload="inicializar_mapa()">
  <div id="mapa_div" style="width:100%; height:100%"></div>
  </body>
</html>


