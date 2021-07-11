<?php
  class UserHandler
  {
    public $ID = null;
    public $Username = null;

    public $Side = null;

    public $Active = null;

    public $Roster = null;
    public $Roster_Hash = null;

    public function __construct
    (
      int $User_ID,
      string $Side
    )
    {
      $this->ID = $User_ID;
      $this->Side = $Side;
    }

    /**
     * Initialize the user and their respective roster.
     */
    public function Initialize()
    {
      global $User_Class;

      $User = $User_Class->FetchUserData($this->ID);
      if ( !$User )
        return false;

      $Roster = new Roster();
      $Roster = $Roster->CreateRoster($this->ID, $this->Side);

      $this->ID = $User['ID'];
      $this->Username = $User['Username'];
      $this->Roster_Hash = $User['Roster_Hash'];
      $this->Active = $Roster[0];
      $this->Roster = $Roster;

      return $this;
    }

    public function GetRosterHash()
    {
      return $this->Roster_Hash;
    }

    public function SetRosterHash()
    {

    }
  }
