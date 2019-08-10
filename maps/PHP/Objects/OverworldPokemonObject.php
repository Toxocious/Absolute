<?php namespace Objects;

class OverworldPokemonObject extends MapObject
{
  public function isActiveOnMap()
  {
    return ($this->state() == 0);

    //! Need to impliment Celebi
    // if ($this->ObjectData['name'] == 'Celebi') {
    //   return isset($_SESSION['maps']['celebi']);
    // }
  }
  
  public function interact()
  {
    global $Poke_Class;
    $this->requireProperty('poke_id');
    $this->requireProperty('alt_id');
    $this->requireProperty('level');
    $this->requireProperty('type');
    $this->requireProperty('subtype');
    $this->requireProperty('obtained_text');

    if (!$this->isActiveOnMap()) {
      $this->addText("An error has occurred.");
      return false;
    }
    
    $props = $this->Object->properties;
    $Encounter = new \Encounter($this->Map);

    $Encounter->initEncounter(
        $props['poke_id'],
        $props['alt_id'],
        $props['level'],
        $props['type'],
        $props['subtype'],
        $props['obtained_text']
    );

    $Encounter->data['overworld_pokemon'] = true;
    $Encounter->data['map_id'] = $this->Map->getMapID();
    $Encounter->data['object_id'] = $this->Object->properties['object_id'];

    $Encounter->save();

    $PokeData = $Poke_Class->GetPokeData(
        $props['poke_id'],
        $props['alt_id'],
        $props['type'],
        $props['subtype']
    );

    $this->addText("
        <img src='".$PokeData['Sprite']."'><br><br>
        <a href='/battle_create.php?Battle=Wild&Trainer=" . $Encounter->data['post_code'] . "'>
            Fight " . $PokeData['Fullname'] . "
        </a>
    ");

    // $this->Output['cry'] = $this->ObjectData['pokemon']['poke_id'];
  }
}
