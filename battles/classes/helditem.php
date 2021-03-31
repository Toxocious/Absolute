<?php
  class HeldItem
  {
    public $Name = null;
    public $Effect = null;
    public $Description = null;
    public $Uses_Left = null;
    public $Has_Procced = false;

    public function __construct
    (
      $Item_ID
    )
    {
      global $PDO;

      try
      {
        $Fetch_Item = $PDO->prepare("SELECT * FROM `item_dex` WHERE `Item_ID` = ? LIMIT 1");
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
      
      $this->Name = $Item_Data['Item_Name'];
      $this->Description = $Item_Data['Item_Description'];
      $this->Effect = null;
      $this->Uses_Left = -1;

      return $this;
    }
  }
