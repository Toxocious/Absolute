<?php
    /**
     * Found at https://www.spieleprogrammierer.de/19-programmierung-und-informatik/27222-perlin-noise-in-php/
     */
    class PerlinGenerator
    {   
        // Ideally Gridsize should be the number of desired Gridcells + 1
        // Types: 'messy' (default), 'island', 'pond'
        // Types 'island' and 'pond' work best with 2x2 Gridsize
        function generate_gradients(int $gridwidth, int $gridheight, string $type = 'messy')
        {
            if($gridwidth < 2 || $gridheight < 2 )
                {
                // TODO: handle Error
                }
            if($type !== 'messy' && $type !== 'island' && $type !== 'pond')
                {
                // TODO: handle Error
                }
            
            // Generate Gradients
            $randomgradients = array(
                'north'         => array(0.0, -1.0),
                'north-east'    => array(0.7071, -0.7071),
                'east'          => array(1.0, 0.0),
                'south-east'    => array(0.7071, 0.7071),
                'south'         => array(0.0, 1.0),
                'south-west'    => array(-0.7071, 0.7071),
                'west'          => array(-1.0, 0.0),
                'north-west'    => array(-0.7071, -0.7071),
            );

            $gradients = array();
            for($y = 0; $y < $gridheight; ++$y)
            {
            $gradients[] = array();
            for($x = 0; $x < $gridwidth; ++$x)
                {
                $pool = array();
                foreach($randomgradients as $direction => $gradient)
                    {
                    $pool[$direction] = $gradient;
                    }
                if($type == 'island')
                    {
                    if($x == 0)
                        {
                        unset($pool['north-west']);
                        unset($pool['west']);
                        unset($pool['south-west']);
                        }
                    else if($x == $gridwidth - 1)
                        {
                        unset($pool['north-east']);
                        unset($pool['east']);
                        unset($pool['south-east']);
                        }
                    
                    if($y == 0)
                        {
                        unset($pool['north-west']);
                        unset($pool['north']);
                        unset($pool['north-east']);
                        }
                    else if($y == $gridheight - 1)
                        {
                        unset($pool['south-east']);
                        unset($pool['south']);
                        unset($pool['south-west']);
                        }
                    }
                else if($type == 'pond')
                    {
                    if($x == 0)
                        {
                        unset($pool['north-east']);
                        unset($pool['east']);
                        unset($pool['south-east']);
                        }
                    else if($x == $gridwidth - 1)
                        {
                        unset($pool['north-west']);
                        unset($pool['west']);
                        unset($pool['south-west']);
                        }
                    
                    if($y == 0)
                        {
                        unset($pool['south-east']);
                        unset($pool['south']);
                        unset($pool['south-west']);
                        }
                    else if($y == $gridheight - 1)
                        {
                        unset($pool['north-west']);
                        unset($pool['north']);
                        unset($pool['north-east']);
                        }
                    }
                
                $pool = array_values($pool);
                $gradients[$y][] = $pool[mt_rand(0, count($pool) - 1)];
                }
            }
        
            return $gradients;
        }
        
        function generate_map(int $mapwidth, int $mapheight, array $gradients)
        {
            // Calculate Step Sizes
            $xstepwidth = (count($gradients[0]) - 1) / $mapwidth;
            $ystepwidth = (count($gradients) - 1) / $mapheight;
                
            // Calculate Map Tiles
            $map = array();
            for($y = $ystepwidth * 0.5; $y < count($gradients) - 1; $y += $ystepwidth)
                {
                $map[] = array();
                for($x = $xstepwidth * 0.5; $x < count($gradients[$y]) - 1; $x += $xstepwidth)
                    {
                    // Calculate corresponding Grid Cell
                    $gridx = floor($x);
                    $gridy = floor($y);
                    
                    // Calculate Dot Products with Grid Corner Gradients
                    $corners = array(array($gridx, $gridy), array($gridx + 1, $gridy), array($gridx, $gridy + 1), array($gridx + 1, $gridy + 1));
                    $distances = array();
                    $dots = array();
                    foreach($corners as $corner)
                        {
                        $distances[] = $this->subtract_vectors(array($x, $y), $corner);
                        $dots[] = $this->dot_product($distances[count($distances) - 1], $gradients[$corner[1]][$corner[0]]);
                        }
                    
                    // Lerp Results together
                    $weight = abs($distances[0][0]) / (abs($distances[0][0]) + abs($distances[1][0]));
                    $toplerp = $this->lerp($dots[0], $dots[1], $weight);
                    $bottomlerp = $this->lerp($dots[2], $dots[3], $weight);
                                            
                    $weight = abs($distances[0][1]) / (abs($distances[0][1]) + abs($distances[2][1]));
                    $finallerp = $this->lerp($toplerp, $bottomlerp, $weight);
                    
                    // Save Results
                    $map[count($map) - 1][] = $finallerp;
                    }
                }
            
            return $map;
        }
        
        // Subtracts Vector b from Vector a and returns the Result
        function subtract_vectors(array $lho, array $rho)
        {
            return array($lho[0] - $rho[0], $lho[1] - $rho[1]);
        }
            
        // Returns the Dot Product of a and b
        function dot_product(array $lho, array $rho)
        {
            return $lho[0] * $rho[0] + $lho[1] * $rho[1];
        }
        
        // Linearly interpolates between $a and $b using $weight. A Weight of 0 returns $a and a Weight of 1.0 returns $b.
        function lerp(float $a, float $b, float $weight)
        {
            return $a * (1 - $weight) + $b * $weight;
        }
    }

    // GENERATE MAPS
    $perlin = new PerlinGenerator();
    $watermap = $perlin->generate_map(30, 30, $perlin->generate_gradients(3, 2, 'pond'));
    $landmap = $perlin->generate_map(30, 30, $perlin->generate_gradients(6, 4, 'messy'));

    // SET HEIGHTLINES
    $waterlevel = -0.4;
    $mountainlevel = 0.0;
    $hilllevel = -0.2;

    // CALCULATE TERRAIN
    $map = array();
    for($y = 0; $y < count($watermap); ++$y)
    {
        $map[] = array();
        for($x = 0; $x < count($watermap[$y]); ++$x)
        {
            if($watermap[$y][$x] > $waterlevel)
            {
                $tilevalue = $watermap[$y][$x] + ($landmap[$y][$x] * 0.5);
                if($tilevalue > $mountainlevel)
                {
                    $map[$y][$x] = '<span style="color: Gray;">M</span>';
                }
                else if($tilevalue > $hilllevel)
                {
                    $map[$y][$x] = '<span style="color: Brown;">H</span>';
                }
                else
                {
                    $map[$y][$x] = '<span style="color: Green;">P</span>';
                }
            }
            else
            {
                $map[$y][$x] = '<span style="color: Blue;">W</span>'; 
            }
        }
    }

    // PRINT MAP
    echo('<table style="text-align: center">');
    foreach($map as $maprow)
    {
        echo('<tr>');
        foreach($maprow as $tile)
        {
            echo('<td>');
            echo($tile);
            echo('</td>');
        }
        echo('</tr>');
    }
    echo('</table>');