<?php
  use BattleHandler\Battle;

  class Trainer extends Battle
  {
    public $Earn_Exp = true;
    public $Earn_Money = true;
    public $Earn_AbsoCoins = true;
    public $Earn_ClanExp = true;

    public $Roster_Limit = 6;
    public $Level_Limit = null;
    public $Items_Allowed = true;
    public $Switch_Allowed = true;

    public $Ally_ID;
    public $Foe_ID;

    public function __construct
    (
      int $Ally_ID,
      int $Foe_ID
    )
    {
      $this->Ally_ID = $Ally_ID;
      $this->Foe_ID = $Foe_ID;

      $this->Started = true;
    }

    public function CreateBattle()
    {
      global $User_Class;

      if
      (
        !$User_Class->FetchUserData($this->Ally_ID) ||
        !$User_Class->FetchUserData($this->Foe_ID)
      )
        return false;

      $Ally = new UserHandler($this->Ally_ID, 'Ally');
      $this->Ally = $Ally->Initialize();
      if ( !$this->Ally )
        return false;

      $Foe = new UserHandler($this->Foe_ID, 'Foe');
      $this->Foe = $Foe->Initialize();
      if ( !$this->Foe )
        return false;

      $this->Battle_Type = 'Trainer';
      $this->Battle_ID = bin2hex(random_bytes(10));
      $this->Time_Started = time();
      $this->Started = true;

      $_SESSION['Battle']['Battle_ID'] = $this->Battle_ID;
      $_SESSION['Battle']['Battle_Type'] = $this->Battle_Type;
      $_SESSION['Battle']['Time_Started'] = $this->Time_Started;
      $_SESSION['Battle']['Started'] = $this->Started;
      $_SESSION['Battle']['Turn_ID'] = 1;
      $_SESSION['Battle']['Ally'] = $this->Ally;
      $_SESSION['Battle']['Ally_ID'] = $this->Ally_ID;
      $_SESSION['Battle']['Foe'] = $this->Foe;
      $_SESSION['Battle']['Foe_ID'] = $this->Foe_ID;

      $Creation_Dialogue = '';
      foreach(['Ally', 'Foe'] as $Side)
      {
        if ( $Side === 'Ally' )
        {
          $Attacker = $this->Ally->Active;
          $Defender = $this->Foe->Active;
        }
        else
        {
          $Attacker = $this->Foe->Active;
          $Defender = $this->Ally->Active;
        }

        $Creation_Dialogue .= "<br /><br />{$this->$Side->Username} sent out {$Attacker->Display_Name}!";
        $Ability_Proc_Text = $Attacker->AbilityProcsOnEntry($Attacker, $Defender);

        if ( !empty($Ability_Proc_Text) )
          $Creation_Dialogue .= "<br />{$Ability_Proc_Text}";
      }

      if ( $Creation_Dialogue == '' )
      {
        $_SESSION['Battle']['Dialogue'] = [
          'Type' => 'Success',
          'Text' => 'The battle has begun.',
        ];
      }
      else
      {
        $_SESSION['Battle']['Dialogue'] = [
          'Type' => 'Success',
          'Text' => 'The battle has begun' . $Creation_Dialogue,
        ];
      }

      return true;
    }
  }
