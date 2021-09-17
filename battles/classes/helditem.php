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
          SELECT `id`, `Item_ID`, `Item_Name`, `Owner_Current`, `Quantity`
          FROM `items`
          WHERE `Item_ID` = ?
          LIMIT 1
        ");
        $Fetch_Item->execute([ $Item_ID ]);
        $Fetch_Item->setFetchMode(PDO::FETCH_ASSOC);
        $Item_Data = $Fetch_Item->fetch();
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      if ( !$Item_Data )
        return false;

      try
      {
        $Fetch_Item_Data = $PDO->prepare("
          SELECT `Natural_Gift_Power`, `Natural_Gift_Type`, `Fling_Power`, `Mega_Evolves`, `Attack_Boost`, `Defense_Boost`, `Sp_Attack_Boost`, `Sp_Defense_Boost`, `Speed_Boost`, `Accuracy_Boost`, `Evasion_Boost`
          FROM `item_dex`
          WHERE `Item_ID` = ?
          LIMIT 1
        ");
        $Fetch_Item_Data->execute([ $Item_ID ]);
        $Fetch_Item_Data->setFetchMode(PDO::FETCH_ASSOC);
        $Item_Dex_Data = $Fetch_Item_Data->fetch();
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      $this->ID = $Item_Data['id'];
      $this->Item_ID = $Item_Data['Item_ID'];
      $this->Name = $Item_Data['Item_Name'];
      $this->Owner_Current = $Item_Data['Owner_Current'];
      $this->Uses_Left = -1;

      $this->Can_Take_Item = $Item_Dex_Data['Can_Take_Item'];
      $this->Natural_Gift_Power = $Item_Dex_Data['Natural_Gift_Power'];
      $this->Natural_Gift_Type = $Item_Dex_Data['Natural_Gift_Type'];
      $this->Fling_Power = $Item_Dex_Data['Fling_Power'];
      $this->Mega_Evolves = $Item_Dex_Data['Mega_Evolves'];
      $this->Attack_Boost = $Item_Dex_Data['Attack_Boost'];
      $this->Defense_Boost = $Item_Dex_Data['Defense_Boost'];
      $this->Sp_Attack_Boost = $Item_Dex_Data['Sp_Attack_Boost'];
      $this->Sp_Defense_Boost = $Item_Dex_Data['Sp_Defense_Boost'];
      $this->Speed_Boost = $Item_Dex_Data['Speed_Boost'];
      $this->Accuracy_Boost = $Item_Dex_Data['Accuracy_Boost'];
      $this->Evasion_Boost = $Item_Dex_Data['Evasion_Boost'];

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
