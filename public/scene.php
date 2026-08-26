<?php

function gcd($a, $b) {
  return ($b == 0) ? $a : gcd($b, $a % $b);
}

require('../autoloader.php');

use Controllers\PaintingController;

if (!isset($_GET['gallery'])) {
  echo "Nepovdelo se načíst AR";
  exit();
}

$gallery_id = $_GET['gallery'];

$paintings = PaintingController::getAll($gallery_id);

if (empty($paintings)) {
  echo "Nepovdelo se načíst AR";
  exit();
}


$painting = $paintings[0];

$image_file = basename($painting->image_link);

list($width, $height, $type, $attr) = getimagesize('../public/images/' . $image_file);
/*

$gcd = gcd($width, $height);

$painting_ratio_h = $height / $gcd;
$painting_ratio_w = $width / $gcd;
*/


$coefi = $width > $height ? $width / $height : $height / $width;
$painting_ratio_h = 6 / $coefi;
$painting_ratio_w = 6;

/*
echo $painting->name . "<br>";
echo "H: " . $painting_ratio_h . "<br>";
echo "W: " . $painting_ratio_w . "<br>";
*/


function loadAssets($paintings) {
  for ($i=0; $i < count($paintings); $i++) { 
    $image_link = $paintings[$i]->image_link;
    echo "<img id='paint-$i' src='$image_link'>";
    echo "<img id='frame-normal' src='../public/textures/frame-normal.png'>";
  }
} 

function loadObjects($paintings) {
  for ($i=0; $i < count($paintings); $i++) { 
    $barcode_value = $paintings[$i]->marker->barcode_value;

    $image_file = basename($paintings[$i]->image_link);

    list($width, $height, $type, $attr) = getimagesize('../public/images/' . $image_file);

    if ($width >= $height) {
      $coefi = $width / $height;
      $painting_ratio_h = 4 / $coefi;
      $painting_ratio_w = 4;
    }
    else {
      $coefi = $height / $width;
      $painting_ratio_h = 4;
      $painting_ratio_w = 4 / $coefi;
    }

    $frame_thickness = 0.3;
    
    $painting_frame_w = $painting_ratio_w + ($frame_thickness * 2);
    $painting_frame_h = $painting_ratio_h + 0.1;
    
    $painting_top_p = ($painting_ratio_h + $frame_thickness)  / 2;
    $painting_bottom_p = $painting_top_p * -1;
    
    $painting_right_p = ($painting_ratio_w + $frame_thickness)  / 2;
    $painting_left_p = $painting_right_p * -1;
    
    $frame_roughness = 0.4;
    $frame_normal_scale = 2;
    $frame_repeat_w = $painting_frame_w * 0.75;
    $frame_repeat_h = $painting_frame_h * 0.75;
    $frame_color= '#473521';



    echo "<a-marker type='barcode' value='$barcode_value' >";
      echo "<a-entity class='ar-painting'>";
        echo "  <a-image 
                  class='ar-image' 
                  src='#paint-$i'  
                  position='0 0 0' scale='1 1 1' width='$painting_ratio_w' height='$painting_ratio_h' rotation='-90 0 0' >
                </a-image>";
        // echo "<a-plane color='brown' position='0 -0.2 0' color='#CCC' width='$painting_frame_w' height='$painting_frame_h' rotation='-90 0 0'></a-plane>";

        echo "<a-box 
                position='$painting_left_p 0 0' depth='$painting_frame_h' height='0.1' width='$frame_thickness'
                color='$frame_color'
                normal-map='#frame-normal' normal-texture-repeat='$frame_thickness $frame_repeat_h' normal-scale='$frame_normal_scale -1'
                roughness='$frame_roughness'>
              </a-box>";

        echo "<a-box 
                position='$painting_right_p 0 0' depth='$painting_frame_h' height='0.1' width='$frame_thickness'
                color='$frame_color'
                normal-map='#frame-normal' normal-texture-repeat='$frame_thickness $frame_repeat_h' normal-scale='$frame_normal_scale -1'
                roughness='$frame_roughness'>
              </a-box>";     

        echo "<a-box 
                position='0 0 $painting_top_p' depth='$frame_thickness' height='0.1' width='$painting_frame_w'
                color='$frame_color'
                normal-map='#frame-normal' normal-texture-repeat='$frame_repeat_w $frame_thickness' normal-scale='$frame_normal_scale -1'
                roughness='$frame_roughness'>
              </a-box>";

        echo "<a-box
                position='0 0 $painting_bottom_p' depth='$frame_thickness' height='0.1' width='$painting_frame_w'
                color='$frame_color'
                normal-map='#frame-normal' normal-texture-repeat='$frame_repeat_w $frame_thickness' normal-scale='$frame_normal_scale -1'
                roughness='$frame_roughness'>
              </a-box>";



      echo "</a-entity>";
    echo "</a-marker>";
  }
} 

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no," />
    <title>Document</title>
    <script src="https://aframe.io/releases/0.6.0/aframe.min.js"></script>
    <script src="https://jeromeetienne.github.io/AR.js/aframe/build/aframe-ar.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body id="scene" style='margin : 0px; overflow: hidden;'>

  <a-scene embedded arjs="sourceType: webcam; debugUIEnabled: false; detectionMode: mono_and_matrix; matrixCodeType: 3x3;" vr-mode-ui='enabled: false' >
    <a-assets>
      <?php loadAssets($paintings) ?>
    </a-assets>
    <?php loadObjects($paintings) ?>
    <a-entity camera></a-entity>
    <a-light type="spot" position="0 1 0"></a-light>
  </a-scene>

  <!--
  <div class="scale-menu">
    <button class="scale-button" id="scale-up">Scale +</button>
    <button class="scale-button" id="scale-down">Scale -</button>
  </div>
  -->
  <div class="slider-wrapper" id="scale-range-wrapper">
    <!--<img class="button-icon" src="../public/assets/icons/zoom.svg" alt="">-->
    <div class="range-title">Škálování: </div>
    <input type="range" class="slider" id="scale-range" name="scale-range" min="0.2" max="4" value="2" step="0.05" />
  </div>

  <div id="fullscreen-button" class="button">
    <img class="button-icon" src="../public/assets/icons/fullscreen.svg" />
  </div>

  <div id="exit-button" class="button">
    <img class="button-icon" src="../public/assets/icons/cross.svg" />
  </div>

</body>
</html>

<script>

function toggleFullScreen() {
  var docElm = document.documentElement;
  var isInFullScreen = (document.fullscreenElement && document.fullscreenElement !== null) ||
    (document.webkitFullscreenElement && document.webkitFullscreenElement !== null) ||
    (document.mozFullScreenElement && document.mozFullScreenElement !== null) ||
    (document.msFullscreenElement && document.msFullscreenElement !== null);

  if (!isInFullScreen) {
    if (docElm.requestFullscreen) {
      docElm.requestFullscreen();
    } else if (docElm.mozRequestFullScreen) {
      docElm.mozRequestFullScreen();
    } else if (docElm.webkitRequestFullScreen) {
      docElm.webkitRequestFullScreen();
    } else if (docElm.msRequestFullscreen) {
      docElm.msRequestFullscreen();
    }
    screen.orientation.lock("portrait");
  } else {
    if (document.exitFullscreen) {
      document.exitFullscreen();
    } else if (document.webkitExitFullscreen) {
      document.webkitExitFullscreen();
    } else if (document.mozCancelFullScreen) {
      document.mozCancelFullScreen();
    } else if (document.msExitFullscreen) {
      document.msExitFullscreen();
    }
    screen.orientation.unlock();
  }
}


var imagesScale = 1;

function scaleUp() {
  // imagesScale += imagesScale == 10 ? 0 : 0.5;
  imagesScale *= imagesScale >= 10 ? 1 : 1.2;
}

function scaleDown() {
  imagesScale /= imagesScale <= 0.2 ? 1 : 1.2;
}

$(document).ready(function() {

  screen.orientation.lock("portrait"); // This doesnt work
  
  $("#scale-range").on("input", function() {
    var scale = this.value;
    var arImages = $('.ar-painting');
    arImages.attr("scale", `${scale} ${scale} ${scale}`);
  });


  $(".scale-button").on("mousedown", function() {
    if (this.id === "scale-up") {
      scaleUp();
    }
    if (this.id === "scale-down") {
      scaleDown();
    }
    var arImages = $('.ar-painting');
    arImages.attr("scale", `${imagesScale} ${imagesScale} ${imagesScale}`);
  });

  $("#fullscreen-button").on("click", toggleFullScreen);

  $("#exit-button").on("click", function() {
    window.close();
  });
});


</script>

<style>
  .button {
    background-color: red;
    padding: 8px;
    display: flex;
    flex-direction: row;
    border-radius: 8px;
    background-color: white;
    margin: 24px;
  }

  .button-icon {
    height: 24px;
    width: 24px;
  }
  
  #fullscreen-button {
    position: fixed;
    top: 0;
    right: 0;
  }

  #exit-button {
    position: fixed;
    top: 0;
    left: 0;
  }


  .scale-menu {
    position: fixed;
    display: flex;
    flex-direction: column;
    right: 0;
    top: 0;
  }

  .scale-button {
    height: 32px;
    margin: 24px 24px 0 0;
  }

  #scale-range-wrapper {
    display: flex;
    flex-direction: row;
    align-items: center;
    position: fixed;
    left: 0;
    width: calc(100% - 64px);
    margin: 32px;
    bottom: 0;
  }

  #scale-range {
    margin-left: 24px;
  }

  .range-title {
    font-family: 'Nunito Sans';
    font-weight: 600;
    font-size: 16px;
    color: white;
  }

/* Universal CSS for sliders */
.slider {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  width: 100%;
  height: 15px;
  border-radius: 5px;
  background-color: #ddd;
  outline: none;
  padding: 0;
  margin: 0;
  /* Set the slider to be horizontal */
  orientation: horizontal;
}

.slider::-webkit-slider-thumb {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  width: 25px;
  height: 25px;
  border-radius: 50%;
  background-color: #449973;
  cursor: pointer;
}

.slider::-moz-range-thumb {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  width: 25px;
  height: 25px;
  border-radius: 50%;
  background-color: #449973;
  cursor: pointer;
}

.slider::-ms-thumb {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  width: 25px;
  height: 25px;
  border-radius: 50%;
  background-color: #449973;
  cursor: pointer;
}

/* Additional styles for Firefox */
@-moz-document url-prefix() {
  .slider::-moz-range-track {
    background-color: transparent;
  }
}

</style>