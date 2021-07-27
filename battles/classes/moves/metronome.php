<?php
  use BattleHandler\Battle;

  class Metronome extends Battle
  {
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
    public $Sp_Attack_Boost = null;
    public $Sp_Defense_Boost = null;
    public $Speed_Boost = null;
    public $Accuracy_Boost = null;
    public $Evasion_Boost = null;

    public $Class_Name = null;

    public function __construct
    (
      Move $Move_Data
    )
    {
      $this->Name = $Move_Data->Name;

      $this->Accuracy = $Move_Data->Accuracy;
      $this->Power = $Move_Data->Power;
      $this->Priority = $Move_Data->Priority;
      $this->Max_PP = $Move_Data->Max_PP;
      $this->Current_PP = $Move_Data->Current_PP;
      $this->Damage_Type = $Move_Data->Damage_Type;
      $this->Move_Type = $Move_Data->Move_Type;

      $this->Flinch_Chance = $Move_Data->Flinch_Chance;
      $this->Crit_Chance = $Move_Data->Crit_Chance;
      $this->Effect_Chance = $Move_Data->Effect_Chance;
      $this->Effect_Short = $Move_Data->Effect_Short;
      $this->Ailment = $Move_Data->Ailment;
      $this->Ailment_Chance = $Move_Data->Ailment_Chance;
      $this->Drain = $Move_Data->Drain;
      $this->Healing = $Move_Data->Healing;
      $this->Max_Hits = $Move_Data->Max_Hits;
      $this->Max_Turns = $Move_Data->Max_Turns;
      $this->Min_Hits = $Move_Data->Min_Hits;
      $this->Min_Turns = $Move_Data->Min_Turns;
      $this->Stat_Chance = $Move_Data->Stat_Chance;

      $this->HP_Boost = $Move_Data->HP_Boost;
      $this->Attack_Boost = $Move_Data->Attack_Boost;
      $this->Defense_Boost = $Move_Data->Defense_Boost;
      $this->Sp_Attack_Boost = $Move_Data->Sp_Attack_Boost;
      $this->Sp_Defense_Boost = $Move_Data->Sp_Defense_Boost;
      $this->Speed_Boost = $Move_Data->Speed_Boost;
      $this->Accuracy_Boost = $Move_Data->Accuracy_Boost;
      $this->Evasion_Boost = $Move_Data->Evasion_Boost;

      $this->Class_Name = $Move_Data->Class_Name;
    }

    public function ProcessMove
    (
      string $Side,
      int $STAB,
      bool $Does_Move_Crit,
      array $Move_Effectiveness
    )
    {
      switch ( $Side )
      {
        case 'Ally':
          $Attacker = $_SESSION['Battle']['Ally']->Active;
          $Defender = $_SESSION['Battle']['Foe']->Active;
          break;
        case 'Foe':
          $Attacker = $_SESSION['Battle']['Foe']->Active;
          $Defender = $_SESSION['Battle']['Ally']->Active;
          break;
      }

      $Fetch_Move = $this->FetchRandomMove();
      if ( isset($Fetch_Move) )
      {
        if ( class_exists($Fetch_Move->Class_Name) )
          $Move_Class = new $Fetch_Move->Class_Name($Fetch_Move);
        else
          $Move_Class = new Move($Fetch_Move->ID, -1);

        if ( method_exists($Move_Class, 'ProcessMove') )
          $Handle_Move = $Move_Class->ProcessMove($Side, $STAB, $Does_Move_Crit, $Move_Effectiveness);
        else
          $Handle_Move = $Move_Class->HandleMove($Side, $STAB, $Does_Move_Crit, $Move_Effectiveness);
      }
      else
      {
        $Handle_Move = [
          'Effect_Text' => 'But it failed.',
        ];
      }

      return [
        'Text' => "{$Attacker->Display_Name} used {$this->Name}.<br />" .
                  (isset($Handle_Move['Text']) ? $Handle_Move['Text'] : ''),
        'Effect_Text' => (isset($Handle_Move['Effect_Text']) ? $Handle_Move['Effect_Text'] : ''),
        'Damage' => (isset($Handle_Move['Damage']) ? $Handle_Move['Damage'] : 0),
        'Healing' => (isset($Handle_Move['Heal']) ? $Handle_Move['Heal'] : 0),
      ];
    }

    /**
     * Pick a random move out of the database.
     */
    public function FetchRandomMove()
    {
      global $PDO;

      try
      {
        $Random_Move = $PDO->prepare("
          SELECT `ID`
          FROM `moves`
          ORDER BY RAND()
          LIMIT 1
        ");
        $Random_Move->execute([ ]);
        $Random_Move->setFetchMode(PDO::FETCH_ASSOC);
        $Move = $Random_Move->fetch();
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      if ( !isset($Move) )
        return false;

      return new Move($Move['ID'], -1);
    }
  }
