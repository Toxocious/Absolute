<?php


$strJson = file_get_contents('pkmn.json');

$pkmn = json_decode($strJson);

foreach ($pkmn as $PokeID => $P) {
  foreach ($P->icons as $I => $Icon) {
    if (file_exists("C:\\wamp\\www\\pokesprite-master\\icons\\pokemon\\shiny\\female\\".$P->slug->eng.".png")) {
      if ($I == '.') {
        rename("C:\\wamp\\www\\pokesprite-master\\icons\\pokemon\\shiny\\female\\".$P->slug->eng.".png", "C:\\wamp\\www\\pokesprite-master\\icons\\pokemon\\shiny\\female\\".$PokeID.".png");
      } elseif ($I == 'mega') {
        rename("C:\\wamp\\www\\pokesprite-master\\icons\\pokemon\\shiny\\female\\".$P->slug->eng."-mega.png", "C:\\wamp\\www\\pokesprite-master\\icons\\pokemon\\shiny\\female\\".$PokeID."mega.png");
      } elseif ($I == 'mega-x') {
        rename("C:\\wamp\\www\\pokesprite-master\\icons\\pokemon\\shiny\\female\\".$P->slug->eng."-mega-x.png", "C:\\wamp\\www\\pokesprite-master\\icons\\pokemon\\shiny\\female\\".$PokeID."xmega.png");
      } elseif ($I == 'mega-y') {
        rename("C:\\wamp\\www\\pokesprite-master\\icons\\pokemon\\shiny\\female\\".$P->slug->eng."-mega-y.png", "C:\\wamp\\www\\pokesprite-master\\icons\\pokemon\\shiny\\female\\".$PokeID."ymega.png");
      } else {
        rename("C:\\wamp\\www\\pokesprite-master\\icons\\pokemon\\shiny\\female\\".$P->slug->eng."-".$I.".png", "C:\\wamp\\www\\pokesprite-master\\icons\\pokemon\\shiny\\female\\".$PokeID.$I.".png");
      }
    }
  }
}
