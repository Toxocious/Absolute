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

      $this->ID = $Item_Data['id'];
      $this->Item_ID = $Item_Data['Item_ID'];
      $this->Name = $Item_Data['Item_Name'];
      $this->Owner_Current = $Item_Data['Owner_Current'];
      $this->Uses_Left = -1;

      return $this;
    }
  }
