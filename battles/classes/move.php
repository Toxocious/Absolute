<?php
  class Move
  {
    public $Name = null;
    public $Type = null;
    public $Category = null;
    public $Power = null;
    public $Priority = null;
    public $Effect = null;
    public $Effect_Chance = null;
    public $Stat_Mod = null;
    public $Stat_Mod_Chance = null;
    public $Max_PP = null;
    public $Current_PP = null;
    public $Description = null;
    public $Accuracy = null;
    public $Success = null;

    public function __construct($Move)
    {
      global $PDO;

      try
      {
        $Fetch_Move = $PDO->prepare("
          SELECT `name`, `type`, `category`, `power`, `accuracy`, `priority`, `crit`, `effect`, `effect_percent`, `stat_modifier`, `modifier_chance`, `pp`, `desc`, `programmed`
          FROM `moves`
          WHERE `id` = ?
          LIMIT 1
        ");
        $Fetch_Move->execute([ $Move ]);
        $Fetch_Move->setFetchMode(PDO::FETCH_ASSOC);
        $Move_Data = $Fetch_Move->fetch();
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      if ( !$Move_Data )
        return false;

      $this->Name = $Move_Data['name'];
      $this->Type = $Move_Data['type'];
      $this->Category = $Move_Data['category'];
      $this->Power = $Move_Data['power'];
      $this->Accuracy = $Move_Data['accuracy'];
      $this->Priority = $Move_Data['priority'];
      $this->Effect = $Move_Data['effect'];
      $this->Effect_Chance = $Move_Data['effect_percent'];
      $this->Stat_Mod = $Move_Data['stat_modifier'];
      $this->Stat_Mod_Chance = $Move_Data['modifier_chance'];
      $this->Max_PP = $Move_Data['pp'];
      $this->Current_PP = $Move_Data['pp'];
      $this->Description = $Move_Data['desc'];

      return $this;
    }
  }