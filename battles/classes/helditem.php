<?php
  class HeldItem
  {
    public $ID = null;
    public $Item_ID = null;
    public $Name = null;
    public $Uses_Left = null;
    public $Owner_Current = null;

    public function __construct
    (
      $Item_ID
    )
    {
      global $PDO;

      try
      {
        $Fetch_Item = $PDO->prepare("
          SELECT *
          FROM `items`
          INNER JOIN `item_dex`
          ON `items`.`Item_ID` = `item_dex`.`Item_ID`
          WHERE `items`.`Item_ID` = ?
          LIMIT 1
        ");
        $Fetch_Item->execute([ 16 ]);
        $Fetch_Item->setFetchMode(PDO::FETCH_ASSOC);
        $Item_Data = $Fetch_Item->fetch();
      }
      catch ( PDOException $e )
      {
        HandleError( $e );
      }

      if ( empty($Item_Data) )
        return false;

      $this->ID = $Item_Data['id'];
      $this->Item_ID = $Item_Data['Item_ID'];
      $this->Name = $Item_Data['Item_Name'];
      $this->Owner_Current = $Item_Data['Owner_Current'];
      $this->Uses_Left = -1;

      $this->Can_Take_Item = $Item_Data['Can_Take_Item'];
      $this->Natural_Gift_Power = $Item_Data['Natural_Gift_Power'];
      $this->Natural_Gift_Type = $Item_Data['Natural_Gift_Type'];
      $this->Fling_Power = $Item_Data['Fling_Power'];
      $this->Mega_Evolves = $Item_Data['Mega_Evolves'];
      $this->Attack_Boost = $Item_Data['Attack_Boost'];
      $this->Defense_Boost = $Item_Data['Defense_Boost'];
      $this->Sp_Attack_Boost = $Item_Data['Sp_Attack_Boost'];
      $this->Sp_Defense_Boost = $Item_Data['Sp_Defense_Boost'];
      $this->Speed_Boost = $Item_Data['Speed_Boost'];
      $this->Accuracy_Boost = $Item_Data['Accuracy_Boost'];
      $this->Evasion_Boost = $Item_Data['Evasion_Boost'];

      return $this;
    }

    /**
     * Process the attempt to take an item (usually via Snatch, Pickpocket, etc.)
     * @param {PokemonHandler} $Holder
     * @param {PokemonHandler} $Taker
     */
    public function TakeItem
    (
      PokemonHandler $Holder,
      PokemonHandler $Taker
    )
    {
      if ( !$this->Can_Take_Item )
        return false;

      if ( $this->Forme_Change )
        if ( $Taker->Pokedex_ID == $this->Forme_Change && $Taker->Alt_ID == 0 )
          return true;

      if ( $this->Mega_Evolves )
        if ( $Taker->Pokedex_ID == $this->Mega_Evolves && $Taker->Alt_ID == 0 )
          return true;

      return false;
    }

    /**
     * Deletes an item on use.
     */
    public function Consume()
    {
      global $PDO;

      if ( $this->Quantity <= 0 )
        return false;

      try
      {
        $PDO->beginTransaction();

        $Update_Item = $PDO->prepare("
          UPDATE `items`
          SET `Quantity` = `Quantity` - 1
          WHERE `Owner_Current` = ? AND `Item_ID` = ?
          LIMIT 1
        ");
        $Update_Item->execute([ $this->Owner_Current, $this->Item_ID ]);

        $PDO->commit();
      }
      catch ( PDOException $e )
      {
        $PDO->rollback();
        HandleError($e);
      }

      return true;
    }
  }
