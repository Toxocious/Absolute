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

    public function __construct()
    {
      $this->Started = true;
    }

    public function CreateBattle
    (
      int $Ally_ID,
      int $Foe_ID
    )
    {
      global $User_Class;

      if
      (
        !$User_Class->FetchUserData($Ally_ID) ||
        !$User_Class->FetchUserData($Foe_ID)
      )
        return false;

      $Ally = new UserHandler($Ally_ID, 'Ally');
      $this->Ally = $Ally->Initialize();
      if ( !$this->Ally )
        return false;

      $Foe = new UserHandler($Foe_ID, 'Foe');
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
      $_SESSION['Battle']['Turn_ID'] = $this->Turn_ID;
      $_SESSION['Battle']['Ally'] = $this->Ally;
      $_SESSION['Battle']['Foe'] = $this->Foe;

      return true;
    }

    /*
    public function CreateBattle
    (
      $Foe_ID
    )
    {
      global $User_Class;

      $this->Time_Started = time();

      $this->Ally['ID'] = $_SESSION['Battle']['Ally']['ID'];
      if ( !$User_Class->FetchUserData($this->Ally['ID']) )
        return false;

      $Roster_Handler = new Roster();

      $this->Processing_Side = 'Ally';
      $this->Ally['Roster'] = $Roster_Handler->CreateRoster($this->Ally['ID'], 'Ally');
      $this->Ally['Active'] = $this->Ally['Roster'][0];
      $this->Ally['Hash'] = $Roster_Handler->GetRosterHash($this->Ally['ID'], 'Ally');

      $this->Foe['ID'] = $Foe_ID;
      if ( !$User_Class->FetchUserData($this->Foe['ID']) )
        return false;

      $this->Processing_Side = 'Foe';
      $this->Foe['Roster'] = $Roster_Handler->CreateRoster($this->Foe['ID'], 'Foe');
      $this->Foe['Active'] = $this->Foe['Roster'][0];
      $this->Foe['Hash'] = $Roster_Handler->GetRosterHash($this->Foe['ID'], 'Foe');

      $this->Processing_Side = null;
      $this->Battle_Type = 'Trainer';
      $this->Started = true;
      $this->Battle_ID = bin2hex(random_bytes(10));

      $_SESSION['Battle']['Turn_ID'] = $this->Turn_ID;
      $_SESSION['Battle']['Time_Started'] = $this->Time_Started;
      $_SESSION['Battle']['Battle_Type'] = $this->Battle_Type;
      $_SESSION['Battle']['Started'] = $this->Started;
      $_SESSION['Battle']['Battle_ID'] = $this->Battle_ID;
      $_SESSION['Battle']['Ally'] = $this->Ally;
      $_SESSION['Battle']['Foe'] = $this->Foe;

      return true;
    }
    */
  }
