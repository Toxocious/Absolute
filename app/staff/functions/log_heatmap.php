<?php
  function SetHeatmapColor($Inputs)
  {
    global $Heatmap_Image;

    if ($Inputs < 3)
      return imagecolorallocate($Heatmap_Image, 53, 63, 153);

    if ($Inputs < 6)
      return imagecolorallocate($Heatmap_Image, 100, 150, 200);

    if ($Inputs < 9)
      return imagecolorallocate($Heatmap_Image, 187, 224, 237);

    if ($Inputs < 12)
      return imagecolorallocate($Heatmap_Image, 232, 246, 223);

    if ($Inputs < 15)
      return imagecolorallocate($Heatmap_Image, 250, 232, 138);

    if ($Inputs < 18)
      return imagecolorallocate($Heatmap_Image, 250, 200, 120);

    if ($Inputs < 21)
      return imagecolorallocate($Heatmap_Image, 240, 110, 66);

    if ($Inputs < 21)
      return imagecolorallocate($Heatmap_Image, 209, 47, 32);

    return imagecolorallocate($Heatmap_Image, 180, 9, 39);
  }
