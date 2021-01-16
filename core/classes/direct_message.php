<?php
  class DirectMessage
  {
    public $Last_Group_ID = 0;

    public function __construct()
    {
      global $PDO, $User_Data;

      $this->PDO = $PDO;
      $this->User = $User_Data;
    }

    /**
     * Fetch an array of all direct messages that a user has participated in.
     * @param $User_ID - ID of the user that we're fetching direct message history for.
     */
    public function FetchMessageList
    (
      int $User_ID = null
    )
    {
      global $PDO;

      if ( !$User_ID )
        $User_ID = $this->User['id'];
      
      try
      {
        $Fetch_Messages = $PDO->prepare("SELECT * FROM `direct_message_groups` WHERE `User_ID` = ? GROUP BY `Group_ID` ORDER BY `Last_Message` DESC");
        $Fetch_Messages->execute([ $User_ID ]);
        $Fetch_Messages->setFetchMode(PDO::FETCH_ASSOC);
        $Messages = $Fetch_Messages->fetchAll();
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      if ( !$Messages )
        return false;

      return $Messages;
    }

    /**
     * Fetch the data of a specific direct message group.
     */
    public function FetchGroup
    (
      int $Group_ID = null,
      int $Clan_ID = null
    )
    {
      global $PDO;

      if ( !$Group_ID && !$Clan_ID )
        return false;

      try
      {
        if ( $Clan_ID )
        {
          $Check_Conversation = $PDO->prepare("SELECT * FROM `direct_message_groups` WHERE `Clan_ID` = ? LIMIT 1");
          $Check_Conversation->execute([ $Clan_ID ]);
        }
        else
        {
          $Check_Conversation = $PDO->prepare("SELECT * FROM `direct_message_groups` WHERE `Group_ID` = ? LIMIT 1");
          $Check_Conversation->execute([ $Group_ID ]);
        }

        $Check_Conversation->setFetchMode(PDO::FETCH_ASSOC);
        $Conversation = $Check_Conversation->fetch();
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      if ( !$Conversation )
        return false;

      return $Conversation;
    }

    /**
     * Fetch an array of messages from a specific direct message.
     * @param $Group_ID - ID of a given direct message.
     * @param $User_ID - ID of the user attempting to load a direct message.
     */
    public function FetchMessage
    (
      int $Group_ID,
      int $User_ID
    )
    {
      global $PDO;

      if ( !$Group_ID || !$User_ID )
        return false;
      
      $Conversation = $this->FetchGroup($Group_ID);
      if ( !$Conversation )
        return false;

      if ( !$this->IsParticipating($Conversation['Group_ID'], $User_ID) )
        return false;

      try
      {
        $Fetch_Messages = $PDO->prepare("SELECT * FROM `direct_messages` WHERE `Group_ID` = ? ORDER BY `Timestamp` ASC");
        $Fetch_Messages->execute([ $Group_ID ]);
        $Fetch_Messages->setFetchMode(PDO::FETCH_ASSOC);
        $Messages = $Fetch_Messages->fetchAll();
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      if ( !$Messages )
        return false;

      return $Messages;
    }

    /**
     * Update the group direct message read db value when the user reads a DM.
     */
    public function ReadDirectMessage
    (
      int $Group_ID,
      int $User_ID
    )
    {
      global $PDO;

      if ( !$Group_ID || !$User_ID )
        return false;
      
      $Conversation = $this->FetchGroup($Group_ID);
      if ( !$Conversation )
        return false;

      if ( !$this->IsParticipating($Conversation['Group_ID'], $User_ID) )
        return false;

      try
      {
        $Fetch_Messages = $PDO->prepare("UPDATE `direct_message_groups` SET `Unread_Messages` = 0 WHERE `Group_ID` = ? AND `User_ID` = ? LIMIT 1");
        $Fetch_Messages->execute([ $Group_ID, $User_ID ]);
        $Fetch_Messages->setFetchMode(PDO::FETCH_ASSOC);
        $Messages = $Fetch_Messages->fetch();
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      if ( !$Messages )
        return false;

      return true;
    }

    /**
     * Update the unread messages count for all users in a direct message.
     */
    public function UpdateReadCount
    (
      int $Group_ID,
      int $User_ID
    )
    {
      global $PDO;

      if ( !$Group_ID || !$User_ID )
        return false;
      
      $Conversation = $this->FetchGroup($Group_ID);
      if ( !$Conversation )
        return false;

      if ( !$this->IsParticipating($Conversation['Group_ID'], $User_ID) )
        return false;

      $Message_Count = 1;
      if ( count($this->FetchMessageList()) == 1 )
        $Message_Count = 0;

      try
      {
        $Fetch_Messages = $PDO->prepare("UPDATE `direct_message_groups` SET `Unread_Messages` = `Unread_Messages` + ? WHERE `Group_ID` = ? AND `User_ID` != ?");
        $Fetch_Messages->execute([ $Message_Count, $Group_ID, $User_ID ]);
        $Fetch_Messages->setFetchMode(PDO::FETCH_ASSOC);
        $Messages = $Fetch_Messages->fetchAll();
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      if ( !$Messages )
        return false;

      return true;
    }

    /**
     * Check to see if a user is a participant in a given direct message.
     * @param $Group_ID - ID of a given direct message.
     * @param $User_ID - ID of the user that we're checking to see if they're a participant in the message.
     */
    public function IsParticipating
    (
      int $Group_ID,
      int $User_ID
    )
    {
      global $PDO;

      if ( !$Group_ID || !$User_ID )
        return false;

      try
      {
        $Check_Participation = $PDO->prepare("SELECT `ID` FROM `direct_message_groups` WHERE `Group_ID` = ? AND `User_ID` = ?");
        $Check_Participation->execute([ $Group_ID, $User_ID ]);
        $Check_Participation->setFetchMode(PDO::FETCH_ASSOC);
        $Participation = $Check_Participation->fetch();
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      if ( !$Participation )
        return false;

      return true;
    }

    /**
     * Fetch the ID of the last direct message group.
     */
    public function FetchGroupID()
    {
      global $PDO;

      try
      {
        $Fetch_Last_Group_ID = $PDO->prepare("
          SELECT `Group_ID`
          FROM `direct_message_groups`
          ORDER BY `Group_ID` DESC
          LIMIT 1
        ");
        $Fetch_Last_Group_ID->execute([ ]);
        $Fetch_Last_Group_ID->setFetchMode(PDO::FETCH_ASSOC);
        $Last_Group_ID = $Fetch_Last_Group_ID->fetch();
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      if ( !$Last_Group_ID || $Last_Group_ID < 1)
        $this->Last_Group_ID = 1;
      else
        $this->Last_Group_ID = $Last_Group_ID['Group_ID'] + 1;

      return $this->Last_Group_ID;
    }

    /**
     * Compose a new direct message.
     */
    public function ComposeMessage
    (
      string $Group_Title,
      string $Message_Text,
      array $Included_Users,
      int $Clan_ID = null
    )
    {
      global $User_Class;

      if ( !$Message_Text || !$Included_Users )
        return false;

      if ( !$Group_Title )
        $Group_Title = 'Untitled Group';
      
      $Group_Title = Purify($Group_Title);
      $Message_Text = Purify($Message_Text);
      $Clan_ID = Purify($Clan_ID);

      $Group_ID = $this->FetchGroupID();

      foreach ( $Included_Users as $User )
      {
        $Fetched_User = $User_Class->FetchUserData($User['User_ID']);
        
        if ( !$Fetched_User )
          return false;
        
        /**
         * If a clan announcement direct message group is already created, do not create another one.
         * Instead, fetch the group's db information, and set $Group_ID as appropriate.
         */
        if ( $this->FetchGroup(0, $Clan_ID) )
        {
          $Group_Data = $this->FetchGroup(0, $Clan_ID);
          $Group_ID = $Group_Data['Group_ID'];
        }
        else
        {
          $Create_Message_Group = $this->CreateMessageGroup($Group_ID, $Group_Title, $Message_Text, $Fetched_User['ID'], $Clan_ID);
          if ( !$Create_Message_Group )
            return false;
        }
      }

      $Message_Creator = $User_Class->FetchUserData($Included_Users[0]['User_ID']);
      
      $Create_Message = $this->CreateMessage($Group_ID, $Message_Text, $Message_Creator['ID'], $Clan_ID);
      if ( !$Create_Message )
        return false;

      return $Group_ID;
    }

    /**
     * Insert a message group into the database.
     */
    public function CreateMessageGroup
    (
      int $Group_ID,
      string $Group_Title,
      string $Message_Text,
      int $User_ID,
      int $Clan_ID = null
    )
    {
      global $PDO, $User_Class, $User_Data;

      if ( !$Group_Title || !$Message_Text || !$Group_ID || !$User_ID )
        return false;

      $Group_Title = Purify($Group_Title);
      $Message_Text = Purify($Message_Text);
      $Group_ID = Purify($Group_ID);
      $User_ID = Purify($User_ID);
      $Clan_ID = Purify($Clan_ID);

      $Fetch_User = $User_Class->FetchUserData($User_ID);
      if ( !$Fetch_User )
        return false;

      $Unread_Messages = 1;
      if ( $User_Data['id'] === $User_ID )
        $Unread_Messages = 0;

      try
      {
        $Create_Message_Group = $PDO->prepare("
          INSERT INTO `direct_message_groups`
          (`Group_ID`, `Group_Name`, `Clan_ID`, `User_ID`, `Unread_Messages`, `Last_Message`)
          VALUES (?, ?, ?, ?, ?, ?)
        ");
        $Create_Message_Group->execute([
          $Group_ID,
          $Group_Title,
          $Clan_ID,
          $User_ID,
          $Unread_Messages,
          time()
        ]);
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      return true;
    }

    /**
     * Insert a message into the appropriate message group in the database.
     */
    public function CreateMessage
    (
      int $Group_ID,
      string $Message_Text,
      int $User_ID,
      int $Clan_ID = null
    )
    {
      global $PDO, $User_Class;

      if ( !$Message_Text || !$Group_ID || !$User_ID )
        return false;

      $Message_Text = Purify($Message_Text);
      $Group_ID = Purify($Group_ID);
      $User_ID = Purify($User_ID);
      $Clan_ID = Purify($Clan_ID);

      if ( !$this->FetchGroup($Group_ID) )
        return false;

      $Fetch_User = $User_Class->FetchUserData($User_ID);
      if ( !$Fetch_User )
        return false;

      if ( !$this->IsParticipating($Group_ID, $User_ID) )
        return false;

      $this->UpdateReadCount($Group_ID, $User_ID);

      try
      {
        $Create_Message = $PDO->prepare("
          INSERT INTO `direct_messages`
          (`Group_ID`, `Clan_ID`, `User_ID`, `Message`, `Timestamp`)
          VALUES (?, ?, ?, ?, ?)
        ");
        $Create_Message->execute([
          $Group_ID,
          $Clan_ID,
          $User_ID,
          $Message_Text,
          time()
        ]);
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      return true;
    }
  }
  