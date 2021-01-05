<?php
  class DirectMessage
  {
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
    public function FetchMessageList($User_ID = null)
    {
      global $PDO;

      if ( !$User_ID )
        $User_ID = $this->User['id'];
      
      try
      {
        $Fetch_Messages = $PDO->prepare("SELECT * FROM `direct_messages` WHERE `Participant_ID` = ? GROUP BY `Message_ID` ORDER BY `Timestamp` DESC");
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
     * Fetch an array of message from a specific direct message.
     * @param $Message_ID - ID of a given direct message.
     */
    public function FetchMessage($Message_ID)
    {
      global $PDO;

      if ( !$Message_ID )
        return false;
      
      try
      {
        $Fetch_Conversation = $PDO->prepare("SELECT `Participant_ID`, `Message`, `Timestamp` FROM `direct_messages` WHERE `Message_ID` = ? ORDER BY `Timestamp` ASC");
        $Fetch_Conversation->execute([ $Message_ID ]);
        $Fetch_Conversation->setFetchMode(PDO::FETCH_ASSOC);
        $Conversation = $Fetch_Conversation->fetchAll();
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      if ( !$Conversation )
        return false;

      return $Conversation;
    }
  }