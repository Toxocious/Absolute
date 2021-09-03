<?php
  use BattleHandler\Battle;

  class PokemonHandler extends Battle
  {
    public $Pokemon_ID = null;

    public $Display_Name = null;

    public $Side = null;
    public $Active = null;
    public $Slot = null;

    public $Shiny = null;
    public $Gender = null;
    public $Level = null;

    public $HP = null;
    public $Max_HP = null;

    public $Primary_Type = null;
    public $Secondary_Type = null;

    public $Height = null;
    public $Weight = null;

    public $Stats = null;

    public $IVs = null;
    public $EVs = null;

    public $Moves = null;
    public $Ability = null;
    public $Original_Ability = null;

    public $Item = null;

    public $Statuses = null;

    public $Happiness = null;
    public $Last_Move = null;

    public $Can_Attack = true;

    public $Sprite = null;
    public $Icon = null;

    public $Participated = false;
    public $Fainted = false;

    public $Dialogue = null;

    public function __construct
    (
      $Pokemon_ID,
      $Side,
      $Slot
    )
    {
      $this->Pokemon_ID = $Pokemon_ID;
      $this->Side = $Side;
      $this->Slot = $Slot;
      $this->Active = ($Slot == 1 ? true : false);
      $this->Participated = ($Slot == 1 ? true : false);

      $this->SetupPokemon();
    }

    /**
     * Setup the specified Pokemon for the battle.
     * Sets the base data of the Pokemon.
     * Called once per Pokemon per battle.
     */
    public function SetupPokemon()
    {
      global $Poke_Class;

      $Pokemon = $Poke_Class->FetchPokemonData($this->Pokemon_ID);
      if ( !$Pokemon )
        return false;

      $this->Pokedex_ID = $Pokemon['Pokedex_ID'];
      $this->Alt_ID = $Pokemon['Alt_ID'];
      $this->Sprite = $Pokemon['Sprite'];
      $this->Icon = $Pokemon['Icon'];
      $this->Display_Name = $Pokemon['Display_Name'];
      $this->Shiny = ($Pokemon['Type'] == 'Shiny' ? true : false);
      $this->Ability = $Pokemon['Ability'];
      $this->Origina_Ability = $Pokemon['Ability'];
      $this->Gender = $Pokemon['Gender'];
      $this->Level = $Pokemon['Level_Raw'];
      $this->HP = $Pokemon['Stats'][0];
      $this->Max_HP = $Pokemon['Stats'][0];
      $this->Primary_Type = $Pokemon['Type_Primary'];
      $this->Secondary_Type = $Pokemon['Type_Secondary'];
      $this->Exp = $Pokemon['Experience_Raw'];
      $this->Exp_Needed = FetchExpToNextLevel($Pokemon['Experience_Raw'], 'Pokemon', true);
      $this->Exp_Yield = $Pokemon['Exp_Yield'];
      $this->Happiness = $Pokemon['Happiness'];
      $this->Owner_Original = $Pokemon['Owner_Original'];
      $this->Owner_Current = $Pokemon['Owner_Current'];
      $this->Height = $Pokemon['Height'];
      $this->Weight = $Pokemon['Weight'];
      $this->Stats = [
        'Attack' => new Stat('Attack', $Pokemon['Stats'][1]),
        'Defense' => new Stat('Defense', $Pokemon['Stats'][2]),
        'Sp_Attack' => new Stat('Sp_Attack', $Pokemon['Stats'][3]),
        'Sp_Defense' => new Stat('Sp_Defense', $Pokemon['Stats'][4]),
        'Speed' => new Stat('Speed', $Pokemon['Stats'][5]),
        'Accuracy' => new Stat('Accuracy', 100),
        'Evasion' => new Stat('Evasion', 100),
      ];
      $this->IVs = $Pokemon['IVs'];
      $this->EVs = $Pokemon['EVs'];
      $this->Moves = [
        new Move($Pokemon['Move_1'], 0),
        new Move($Pokemon['Move_2'], 1),
        new Move($Pokemon['Move_3'], 2),
        new Move($Pokemon['Move_4'], 3),
      ];
      $this->Item = new HeldItem($Pokemon['Item_ID']);

      return $this;
    }

    /**
     * Perform an attack via the client's active Pokemon.
     * @param $Move
     */
    public function Attack
    (
      $Move
    )
    {
      if ( !isset($Move) )
      {
        return [
          'Type' => 'Error',
          'Text' => 'Select a valid move to attack with.<br />'
        ];
      }

      if ( $this->Fainted )
      {
        return [
          'Type' => 'Error',
          'Text' => "{$this->Display_Name} is fainted and can not attack.<br />"
        ];
      }

      if ( $this->HP == 0 )
      {
        return [
          'Type' => 'Error',
          'Text' => "{$this->Display_Name} is currently fainted, and may not attack.<br />"
        ];
      }

      if ( !$this->Can_Attack )
      {
        return [
          'Type' => 'Error',
          'Text' => ''
        ];
      }

      if ( $this->HasStatus('Bide') && $this->Statuses['Bide']->Turns_Left > 0 )
      {
        return [
          'Type' => 'Success',
          'Text' => "{$this->Display_Name} is storing energy!",
          'Damage' => 0,
          'Healing' => 0,
        ];
      }

      if ( $this->HasStatusFromArray([ 'Move Locked', 'Charging' ]) )
      {
        return $this->Moves[$this->Last_Move['Slot']]->ProcessAttack($this->Side);
      }

      return $Move->ProcessAttack($this->Side);
    }

    /**
     * Switch into the desired Pokemon.
     */
    public function SwitchInto()
    {
      if ( $this->Active )
      {
        return [
          'Type' => 'Error',
          'Text' => 'The Pok&eacute;mon you\'re switching into is already active!'
        ];
      }

      if ( $this->HP == 0 )
      {
        return [
          'Type' => 'Error',
          'Text' => 'The Pok&eacute;mon that you\'re switching into is fainted.',
        ];
      }

      switch ($this->Side)
      {
        case 'Ally':
          $Defender = $_SESSION['Battle']['Foe']->Active;
          break;
        case 'Foe';
          $Defender = $_SESSION['Battle']['Ally']->Active;
          break;
      }

      $Previous_Attacker = $_SESSION['Battle'][$this->Side]->Active;

      if ( $this->Original_Ability != $this->Ability )
        $this->Ability == $this->Original_Ability;

      foreach ($_SESSION['Battle'][$this->Side]->Roster as $Roster_Pokemon)
      {
        $Roster_Pokemon->Active = false;

        if ( $Roster_Pokemon->Pokemon_ID == $this->Pokemon_ID )
          $Roster_Pokemon->Active = true;
      }

      $this->Participated = true;
      $_SESSION['Battle'][$this->Side]->Active = $this;

      $New_Active = $_SESSION['Battle'][$this->Side]->Active;

      if ( $Previous_Attacker->Last_Move['Name'] == 'Baton Pass' )
      {
        $Text = "{$Previous_Attacker->Display_Name} used Baton Pass!<br />";
      }

      $Effect_Text = '';

      switch ($New_Active->Ability)
      {
        case 'Anticipation':
          foreach ($Defender->Moves as $Move)
          {
            if ( $Move->Category == 'Status' )
              continue;

            if
            (
              $Move->Category == 'Ohko' ||
              $Move->MoveEffectiveness($this)['Mult'] > 1
            )
            {
              $Effect_Text .= "{$this->Display_Name} shuddered.<br />";

              break;
            }
          }
          break;

        case 'Dauntless Shield':
          $New_Active->Stats['Defense']->SetValue(1);
          $Effect_Text .= "{$New_Active->Display_Name}'s Dauntless Shield raised its Defense!<br />";
          break;

        case 'Delta Stream':
          if ( isset($this->Weather) )
            unset($this->Weather);

          $Set_Weather = new Weather('Strong Winds', -1);
          if ( $Set_Weather )
          {
            $this->Weather[$Set_Weather->Name] = $Set_Weather;
            $Effect_Text .= $Set_Weather->Dialogue;
          }
          break;

        case 'Desolate Land':
          if ( isset($this->Weather) )
            unset($this->Weather);

          $Set_Weather = new Weather('Desolate Land', -1);
          if ( $Set_Weather )
          {
            $this->Weather[$Set_Weather->Name] = $Set_Weather;
            $Effect_Text .= $Set_Weather->Dialogue;
          }
          break;

        case 'Air Lock':
        case 'Cloud Nine':
          if ( !empty($this->Weather) )
          {
            unset($this->Weather);
            $Effect_Text .= 'The effects of weather disappeared.<br />';
          }
          break;

        case 'Download':
          if ( $Defender->Stats['Defense']->Current_Value > $Defender->Stats['Sp_Defense']->Current_Value )
            $Boosted_Stat = 'Attack';
          else
            $Boosted_Stat = 'Sp_Attack';

          $Stat_Name = str_replace('_', 'ecial ', $Boosted_Stat);
          $New_Active->Stats[$Boosted_Stat]->SetValue(1);
          $Effect_Text .= "{$New_Active->Display_Name}'s Download raised its {$Stat_Name}!<br />";
          break;
      }

      return [
        'Type' => 'Success',
        'Text' => (isset($Text) ? $Text : '') .
                  "{$this->Display_Name} has been sent into battle!" .
                  (isset($Effect_Text) ? $Effect_Text : '')
      ];
    }

    /**
     * Handle what happens when the Pokemon faints.
     * @param {bool} $Weather_Trigger
     */
    public function HandleFaint
    (
      bool $Weather_Trigger = false
    )
    {
      if ( $this->HP > 0 )
        return;

      switch ( $this->Side )
      {
        case 'Ally':
          $Attacker = $_SESSION['Battle']['Ally'];
          $Defender = $_SESSION['Battle']['Foe'];
          break;
        case 'Foe':
          $Attacker = $_SESSION['Battle']['Foe'];
          $Defender = $_SESSION['Battle']['Ally'];
          break;
      }

      $this->Fainted = true;

      if ( !$Weather_Trigger )
      {
        if ( $Defender->Active->HasStatus('Destiny Bond') )
        {
          $Defender->Active->DecreaseHP($Defender->Active->HP);
          $Dialogue = "<br />{$this->Display_Name} took its foe down with it!";
        }
      }

      if ( $this->Earn_Pokemon_Exp && $this->Side !== 'Ally' )
      {
        $Exp_Dialogue = $Defender->Active->IncreaseExp();
      }

      $Continue = false;
      $Restart = false;

      if ( $Attacker->NextPokemon() )
      {
        $Effect_Text = $this->AbilityProcsOnFaint($Attacker, $Defender);

        $this->GeneratePostcode('Continue');
        $Continue = true;
      }
      else
      {
        $this->GeneratePostcode('Restart');
        $Restart = true;
      }

      return [
        'Type' => 'Success',
        'Text' => "{$this->Display_Name} has fainted." .
                  (!empty($Dialogue) ? $Dialogue : '') .
                  (!empty($Effect_Text) ? $Effect_Text : '') .
                  (!empty($Exp_Dialogue) ? "<br /><br />{$Exp_Dialogue['Text']}" : ''),
        'Continue' => $Continue,
        'Restart' => $Restart,
        'Loser' => $this->Side
      ];
    }

    /**
     * Handle abilities that proc when a Pokemon faints.
     */
    public function AbilityProcsOnFaint
    (
      UserHandler $Attacker,
      UserHandler $Defender
    )
    {
      $Effect_Text = '<br />';

      if ( $Defender->Active->HP > 0 )
      {
        switch ( $Defender->Active->Ability )
        {
          case 'Beast Boost':
            $Best_Stat = [
              'Name' => 'Attack',
              'Value' => 0
            ];

            foreach ( $Defender->Active->Stats as $Stat )
            {
              if ( $Stat->Base_Value > $Best_Stat['Value'] )
              {
                $Best_Stat['Name'] = $Stat->Stat_Name;
                $Best_Stat['Value'] = $Stat->Base_Value;
              }
            }

            $Defender->Active->Stats[$Best_Stat['Name']]->SetValue(1);

            $Effect_Text .= "{$Defender->Active->Display_Name}'s Beast Boost raised its {$Best_Stat['Name']}!";
            break;

          case 'Chilling Neigh':
            $Defender->Active->Stats['Attack']->SetValue(1);

            $Effect_Text .= "{$Defender->Active->Display_Name}'s Chilling Neigh raised it's Attack!";
            break;
        }
      }

      return $Effect_Text;
    }

    /**
     * Increase the Pokemon's current Exp.
     */
    public function IncreaseExp()
    {
      global $PDO;

      $Exp_Divisor = 0;
      foreach ( $_SESSION['Battle']['Ally']->Roster as $Pokemon )
      {
        if
        (
          $Pokemon->Participated ||
          $Pokemon->Item->Name == 'Exp Share' &&
          !$Pokemon->Fainted
        )
          $Exp_Divisor++;
      }

      if ( $Exp_Divisor < 1 )
        $Exp_Divisor = 1;

      $Dialogue = [
        'Type' => 'Success',
        'Text' => ''
      ];

      foreach ( $_SESSION['Battle']['Ally']->Roster as $Pokemon )
      {
        if
        (
          $Pokemon->Participated ||
          $Pokemon->Item->Name == 'Exp Share' &&
          !$Pokemon->Fainted
        )
        {
          $Exp = $this->CalcExp($Exp_Divisor);

          try
          {
            $PDO->beginTransaction();

            $Update_Exp = $PDO->prepare("
              UPDATE `pokemon`
              SET `Experience` = `Experience` + ?
              WHERE `ID` = ?
              LIMIT 1
            ");
            $Update_Exp->execute([
              $Exp,
              $Pokemon->Pokemon_ID
            ]);

            $PDO->commit();
          }
          catch ( PDOException $e )
          {
            $PDO->rollback();
            HandleError($e);
          }

          $this->Exp += $Exp;
          $this->Exp_Needed = FetchExpToNextLevel($this->Exp, 'Pokemon', true);

          $Dialogue['Text'] .= "{$Pokemon->Display_Name} has gained " . number_format($Exp) . " experience.<br />";

          $Check_Level = FetchLevel($this->Exp, 'Pokemon');
          if ( $this->Level != $Check_Level )
          {
            $this->Level = $Check_Level;
            $Dialogue['Text'] .= "{$Pokemon->Display_Name} has reached level <b>" . number_format($Check_Level) . "</b>!<br />";
          }
        }
      }

      return $Dialogue;
    }

    /**
     * Calculate how much experience is earned when a foe faints.
     * @param int $Exp_Divisor
     */
    public function CalcExp
    (
      int $Exp_Divisor
    )
    {
      if ( $this->Active )
        $Ally_Active = $this;
      else
        $Ally_Active = $_SESSION['Battle']['Ally']->Active;

      $Foe_Active = $_SESSION['Battle']['Foe']->Active;

      if ( $this->Item->Name == 'Exp Share' )
        $s = 2;
      else
        $s = 1;

      if ( $this->Owner_Original == $this->Owner_Current )
        $t = 1;
      else
        $t = 1.5;

      if ( $this->Item->Name == 'Lucky Egg' )
        $e = 1.5;
      else
        $e = 1;

      return round($Foe_Active->Exp_Yield * $Foe_Active->Level / 5 * $s * pow((2 * $Foe_Active->Level + 10), 2.5) / pow(($Foe_Active->Level + $Ally_Active->Level + 20), 2.5) + 1) * $t * $e / $Exp_Divisor;
    }

    /**
     * Increase the Pokemon's current HP.
     * @param int $Heal
     */
    public function IncreaseHP
    (
      int $Heal
    )
    {
      $this->HP += $Heal;

      if ( $this->HP > $this->Max_HP )
        $this->HP = $this->Max_HP;

      return $this->HP;
    }

    /**
     * Decrease the Pokemon's current HP.
     * @param int $Damage
     */
    public function DecreaseHP
    (
      int $Damage
    )
    {
      $this->HP -= $Damage;

      if ( $this->HP < 0 )
        $this->HP = 0;

      return $this->HP;
    }

    /**
     * Disable the Pokemon's moves from being used.
     */
    public function DisableMoves()
    {
      for ( $i = 0; $i < 4; $i++ )
        $this->Moves[$i]->Disabled = true;
    }

    /**
     * Enable the Pokemon's moves so that they may be used.
     */
    public function EnableMoves()
    {
      for ( $i = 0; $i < 4; $i++ )
        $this->Moves[$i]->Disabled = false;
    }

    /**
     * Fetch a random move from the selected Pokemon.
     */
    public function FetchRandomMove()
    {
      $Fetch_Move = mt_rand(0, (count($this->Moves) - 1));

      return $this->Moves[$Fetch_Move];
    }

    /**
     * Set whether or not the Pokemon may attack this turn.
     * @param bool $Can_Attack
     */
    public function SetCanAttack
    (
      bool $Can_Attack = true
    )
    {
      $this->Can_Attack = $Can_Attack;
    }

    /**
     * Check if the Pokemon has an ability.
     * @param array $Abilities
     */
    public function HasAbility
    (
      array $Abilities
    )
    {
      if ( in_array($this->Ability, $Abilities) )
        return true;

      return false;
    }

    /**
     * Set the Pokemon's Ability.
     * @param string $Ability
     */
    public function SetAbility
    (
      string $Ability
    )
    {
      if ( $this->Ability == $Ability )
        return false;

      return $this->Ability = $Ability;
    }

    /**
     * Set a status on the Pokemon.
     * @param string $Status
     * @param int $Turns
     */
    public function SetStatus
    (
      string $Status,
      int $Turns = null
    )
    {
      /**
       * A Pokémon cannot gain non-volatile status conditions when it is affected by
       *  Safeguard, Leaf Guard, Flower Veil, Shields Down, or Comatose.
       *
       * A Pokémon will cure its status condition when affected by
       *  Refresh, Heal Bell, Aromatherapy, Psycho Shift, Jungle Healing, G-Max Sweetness,
       *  Natural Cure, Shed Skin, Hydration, or Lum Berry.
       *
       * If a Pokémon under a status condition (such as a poisoned Cascoon) evolves, the
       *  condition will be kept, even if the Pokémon gains a new type or Ability that would normally prevent it.
       */

      $Attempt_Status = new Status(
        $this,
        $Status,
        $Turns
      );

      if ( !$Attempt_Status )
        return false;

      $this->Statuses[$Attempt_Status->Name] = $Attempt_Status;

      return $this->Statuses[$Attempt_Status->Name];
    }

    /**
     * Remove a status from the Pokemon.
     * @param string $Status_Name
     */
    public function RemoveStatus
    (
      string $Status_Name
    )
    {
      if ( $this->HasStatus($Status_Name) )
        unset($this->Statuses[$Status_Name]);

      return true;
    }

    /**
     * Remove a statues from the Pokemon.
     * @param array $Statuses
     */
    public function RemoveStatusFromArray
    (
      array $Statuses
    )
    {
      foreach ( $Statuses as $Status )
        if ( $this->HasStatus($Status->Name) )
          unset($this->Statuses[$Status->Name]);

      return true;
    }

    /**
     * Determine if the Pokemon has a given status.
     * @param string $Status
     */
    public function HasStatus
    (
      string $Status
    )
    {
      if ( isset($this->Statuses[$Status]) )
        return $this->Statuses[$Status];

      return false;
    }

    /**
     * Determine if a Pokemon has one of many given statuses.
     * @param array $Checking_For_Statuses
     */
    public function HasStatusFromArray
    (
      array $Checking_For_Statuses
    )
    {
      if ( !$this->Statuses )
        return false;

      foreach ( $this->Statuses as $Status => $Status_Data )
        if ( in_array($Status, $Checking_For_Statuses) )
          return true;

      return false;
    }

    /**
     * Check if a Pokemon is grounded.
     */
    public function IsGrounded()
    {
      if ( $this->IsFieldEffectActive('Global', 'Gravity') )
        return true;

      if ( $this->HasStatusFromArray(['Ingrain', 'Smackdown']) )
        return true;

      if ( $this->HasTyping(['Flying']) && $this->HasStatus('Roost') )
        return true;

      if ( $this->Item->Name == 'Iron Ball' )
        return true;

      if ( $this->HasStatusFromArray(['Magnet Rise', 'Telekinesis']) )
        return false;

      if ( $this->Item->Name == 'Air Baloon' )
        return false;
    }

    /**
     * Reset all stat's back to their base.
     */
    public function ResetStats()
    {
      global $Poke_Class;

      $Fetch_Stats = $Poke_Class->FetchCurrentStats($this->Pokemon_ID, $this->Pokedex_ID, $this->Alt_ID);
      if ( !$Fetch_Stats )
        return false;

      unset($this->Stats);

      $this->Stats = [
        'Attack' => new Stat('Attack', $Fetch_Stats['Stats'][1]),
        'Defense' => new Stat('Defense', $Fetch_Stats['Stats'][2]),
        'Sp_Attack' => new Stat('Sp_Attack', $Fetch_Stats['Stats'][3]),
        'Sp_Defense' => new Stat('Sp_Defense', $Fetch_Stats['Stats'][4]),
        'Speed' => new Stat('Speed', $Fetch_Stats['Stats'][5]),
        'Accuracy' => new Stat('Accuracy', 100),
        'Evasion' => new Stat('Evasion', 100),
      ];
    }

    /**
     * Determine if the Pokemon has a specified typing.
     * @param array $Typings
     */
    public function HasTyping
    (
      array $Typings
    )
    {
      if ( in_array($this->Primary_Type, $Typings) )
        return true;

      if ( in_array($this->Secondary_Type, $Typings) )
        return true;

      return false;
    }
  }
