<?php

require 'vendor/autoload.php';

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
$pathVideo = 'datos\prueba.mp4';
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
    ->save('datos/frame.jpg');

/* Extrear coordenadas */
$reader = Reader::create($logger);

$metadatas = $reader->files($pathVideo)->first();
$gpsLocation = '';
foreach ($metadatas as $metadata) {
    if($metadata->getTag() == 'Composite:GPSPosition')
        $gpsLocation = $metadata->getValue()->asString();
}

$latAndLon = explode(' ',$gpsLocation);

var_dump($latAndLon);

