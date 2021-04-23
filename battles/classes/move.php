<?php
  use BattleHandler\Battle;

  class Move extends Battle
  {
    public $ID = null;
    public $Name = null;
    public $Accuracy = null;
    public $Power = null;
    public $Priority = null;
    public $Max_PP = null;
    public $Current_PP = null;
    public $Damage_Type = null;
    public $Move_Type = null;

    public $Flinch_Chance = null;
    public $Crit_Chance = null;
    public $Effect_Chance = null;
    public $Effect_Short = null;
    public $Ailment = null;
    public $Ailment_Chance = null;
    public $Drain = null;
    public $Healing = null;
    public $Max_Hits = null;
    public $Max_Turns = null;
    public $Min_Hits = null;
    public $Min_Turns = null;
    public $Stat_Chance = null;

    public $HP_Boost = null;
    public $Attack_Boost = null;
    public $Defense_Boost = null;
    public $SpAttack_Boost = null;
    public $SpDefense_Boost = null;
    public $Speed_Boost = null;
    public $Accuracy_Boost = null;
    public $Evasion_Boost = null;

    public $Success = null;

    public function __construct($Move)
    {
      global $PDO;

      try
      {
        $Fetch_Move = $PDO->prepare("
          SELECT *
          FROM `moves`
          WHERE `ID` = ?
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

      $this->ID = $Move_Data['ID'];
      $this->Name = $Move_Data['Name'];
      $this->Accuracy = $Move_Data['Accuracy'];
      $this->Power = $Move_Data['Power'];
      $this->Priority = $Move_Data['Priority'];
      $this->Max_PP = $Move_Data['PP'];
      $this->Current_PP = $Move_Data['PP'];
      $this->Damage_Type = $Move_Data['Damage_Type'];
      $this->Move_Type = $Move_Data['Move_Type'];

      $this->Flinch_Chance = $Move_Data['Flinch_Chance'];
      $this->Crit_Chance = $Move_Data['Crit_Chance'];
      $this->Effect_Chance = $Move_Data['Effect_Chance'];
      $this->Effect_Short = $Move_Data['Effect_Short'];
      $this->Ailment = $Move_Data['Ailment'];
      $this->Ailment_Chance = $Move_Data['Ailment_Chance'];
      $this->Drain = $Move_Data['Drain'];
      $this->Healing = $Move_Data['Healing'];
      $this->Max_Hits = $Move_Data['Max_Hits'];
      $this->Max_Turns = $Move_Data['Max_Turns'];
      $this->Min_Hits = $Move_Data['Min_Hits'];
      $this->Min_Turns = $Move_Data['Min_Turns'];
      $this->Stat_Chance = $Move_Data['Stat_Chance'];

      $this->HP_Boost = $Move_Data['HP_Boost'];
      $this->Attack_Boost = $Move_Data['Attack_Boost'];
      $this->Defense_Boost = $Move_Data['Defense_Boost'];
      $this->SpAttack_Boost = $Move_Data['SpAttack_Boost'];
      $this->SpDefense_Boost = $Move_Data['SpDefense_Boost'];
      $this->Speed_Boost = $Move_Data['Speed_Boost'];
      $this->Accuracy_Boost = $Move_Data['Accuracy_Boost'];
      $this->Evasion_Boost = $Move_Data['Evasion_Boost'];
      return $this;
    }
    /**
     * Determine if the move will crit.
     */
    public function DoesMoveCrit($Side)
    public function DoesMoveCrit
    (
      string $Side
    )
    {
      switch ( $Side )
      {
        case 'Ally':
          $Ally_Active = $_SESSION['Battle']['Ally']['Active'];
          $Foe_Active = $_SESSION['Battle']['Foe']['Active'];
          break;
        case 'Foe':
          $Ally_Active = $_SESSION['Battle']['Foe']['Active'];
          $Foe_Active = $_SESSION['Battle']['Ally']['Active'];
          break;
      }

      if ( !$this->Crit_Chance )
        return false;

      if ( in_array($Foe_Active->Ability, ['Battle Armor', 'Shell Armor']) )
        return false;

      if ( isset($Foe_Active->Statuses['Lucky Chant']) )
        return false;

      if ( $Ally_Active->Ability == 'Merciless' )
        if ( isset($Foe_Active->Statuses['Poisoned']) )
          return true;

      switch ( $this->Crit_Chance )
      {
        case 0:
          return mt_rand(1, 24) === 1;
        case 1:
          return mt_rand(1, 8) === 1;
        case 2:
          return mt_rand(1, 2) === 1;
        default:
          return true;
      }
    }
  }
