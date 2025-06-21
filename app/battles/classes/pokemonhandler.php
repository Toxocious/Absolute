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
    public $Primary_Type_Original = null;
    public $Secondary_Type = null;
    public $Secondary_Type_Original = null;

    public $Height = null;
    public $Weight = null;

    public $Stats = null;

    public $IVs = null;
    public $EVs = null;

    public $Moves = null;
    public $Ability = null;
    public $Ability_Original = null;

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
      $Slot,
      $Pokedex_ID = null,
      $Alt_ID = null,
      $Level = null,
      $Type = null,
      $Gender = null
    )
    {
      $this->Pokemon_ID = $Pokemon_ID;
      $this->Side = $Side;
      $this->Slot = $Slot;
      $this->Active = ($Slot == 1 ? true : false);
      $this->Participated = ($Slot == 1 ? true : false);

      if ( empty($Pokemon_ID) )
        $this->SetupFakePokemon($Pokedex_ID, $Alt_ID, $Level, $Type, $Gender);
      else
        $this->SetupPokemon();
    }

    /**
     * Setup the specified Pokemon for the battle.
     * Sets the base data of the Pokemon.
     * Called once per Pokemon per battle.
     */
    public function SetupPokemon()
    {
      $Pokemon = GetPokemonData($this->Pokemon_ID);
      if ( !$Pokemon )
        return false;

      $this->Pokedex_ID = $Pokemon['Pokedex_ID'];
      $this->Pokedex_ID_Original = $Pokemon['Pokedex_ID'];
      $this->Alt_ID = $Pokemon['Alt_ID'];
      $this->Alt_ID_Original = $Pokemon['Alt_ID'];
      $this->Sprite = $Pokemon['Sprite'];
      $this->Sprite_Original = $Pokemon['Sprite'];
      $this->Icon = $Pokemon['Icon'];
      $this->Icon_Original = $Pokemon['Icon'];
      $this->Display_Name = $Pokemon['Display_Name'];
      $this->Display_Name_Original = $Pokemon['Display_Name'];
      $this->Shiny = ($Pokemon['Type'] == 'Shiny' ? true : false);
      $this->Ability = new Ability($Pokemon['Ability']);
      $this->Ability_Original = new Ability($Pokemon['Ability']);
      $this->Gender = $Pokemon['Gender'];
      $this->Gender_Original = $Pokemon['Gender'];
      $this->Level = $Pokemon['Level_Raw'];
      $this->HP = $Pokemon['Stats'][0];
      $this->Max_HP = $Pokemon['Stats'][0];
      $this->Primary_Type = $Pokemon['Type_Primary'];
      $this->Primary_Type_Original = $Pokemon['Type_Primary'];
      $this->Secondary_Type = $Pokemon['Type_Secondary'];
      $this->Secondary_Type_Original = $Pokemon['Type_Secondary'];
      $this->Exp = $Pokemon['Experience_Raw'];
      $this->Exp_Needed = FetchExpToNextLevel($Pokemon['Experience_Raw'], 'Pokemon', true);
      $this->Exp_Yield = $Pokemon['Exp_Yield'];
      $this->Critical_Hit_Boost = 0;
      $this->Happiness = $Pokemon['Happiness'];
      $this->Owner_Original = $Pokemon['Owner_Original'];
      $this->Owner_Current = $Pokemon['Owner_Current'];
      $this->Height = $Pokemon['Height'];
      $this->Weight = $Pokemon['Weight'];
      $this->Weight_Original = $Pokemon['Weight'];
      $this->Stats = [
        'Attack' => new Stat('Attack', $Pokemon['Stats'][1]),
        'Defense' => new Stat('Defense', $Pokemon['Stats'][2]),
        'Sp_Attack' => new Stat('Sp_Attack', $Pokemon['Stats'][3]),
        'Sp_Defense' => new Stat('Sp_Defense', $Pokemon['Stats'][4]),
        'Speed' => new Stat('Speed', $Pokemon['Stats'][5]),
        'Accuracy' => new Stat('Accuracy', 100),
        'Evasion' => new Stat('Evasion', 100),
      ];
      $this->Stats_Original = [
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
      $this->Moves_Original = [
        new Move($Pokemon['Move_1'], 0),
        new Move($Pokemon['Move_2'], 1),
        new Move($Pokemon['Move_3'], 2),
        new Move($Pokemon['Move_4'], 3),
      ];
      $this->Item = new HeldItem($Pokemon['Item_ID']);

      return $this;
    }

    /**
     * Setup the specified fake Pokemon for the battle.
     *
     * @param {int} $Pokedex_ID
     * @param {int} $Alt_ID
     * @param {int} $Level
     * @param {string} $Type
     * @param {string} $Gender
     */
    public function SetupFakePokemon
    (
      $Pokedex_ID,
      $Alt_ID,
      $Level,
      $Type,
      $Gender
    )
    {
      $Pokedex_Data = GetPokedexData($Pokedex_ID, $Alt_ID, $Type);
      if ( empty($Pokedex_Data) )
        return false;

      $Pokemon_Ability = GenerateAbility($Pokedex_ID, $Alt_ID);
      $Pokemon_Exp = FetchExperience($Level, 'Pokemon');
      $Nature = GenerateNature();
      $IVs = $this->GenerateIVs();
      $EVs = $this->GenerateEVs();

      $this->Pokedex_ID = $Pokedex_Data['Pokedex_ID'];
      $this->Pokedex_ID_Original = $Pokedex_Data['Pokedex_ID'];
      $this->Alt_ID = $Pokedex_Data['Alt_ID'];
      $this->Alt_ID_Original = $Pokedex_Data['Alt_ID'];
      $this->Sprite = $Pokedex_Data['Sprite'];
      $this->Sprite_Original = $Pokedex_Data['Sprite'];
      $this->Icon = $Pokedex_Data['Icon'];
      $this->Icon_Original = $Pokedex_Data['Icon'];
      $this->Display_Name = $Pokedex_Data['Display_Name'];
      $this->Display_Name_Original = $Pokedex_Data['Display_Name'];
      $this->Shiny = ($Type == 'Shiny' ? true : false);
      $this->Ability = new Ability($Pokemon_Ability);
      $this->Ability_Original = new Ability($Pokemon_Ability);
      $this->Gender = $Gender;
      $this->Gender_Original = $Gender;
      $this->Level = $Level;
      $this->Primary_Type = $Pokedex_Data['Type_Primary'];
      $this->Primary_Type_Original = $Pokedex_Data['Type_Primary'];
      $this->Secondary_Type = $Pokedex_Data['Type_Secondary'];
      $this->Secondary_Type_Original = $Pokedex_Data['Type_Secondary'];
      $this->Exp = $Pokemon_Exp;
      $this->Exp_Needed = FetchExpToNextLevel($Pokemon_Exp, 'Pokemon', true);
      $this->Exp_Yield = $Pokedex_Data['Exp_Yield'];
      $this->Critical_Hit_Boost = 0;
      $this->Happiness = mt_rand(70, 150);
      $this->Owner_Original = -1;
      $this->Owner_Current = -1;
      $this->Height = $Pokedex_Data['Height'];
      $this->Weight = $Pokedex_Data['Weight'];
      $this->Weight_Original = $Pokedex_Data['Weight'];
      $this->HP = CalculateStat('HP', $Pokedex_Data['Base_Stats'][0], $Level, $IVs[0], $EVs[0], $Nature);
      $this->Max_HP = CalculateStat('HP', $Pokedex_Data['Base_Stats'][0], $Level, $IVs[0], $EVs[0], $Nature);
      $this->Stats = [
        'Attack' => new Stat('Attack', CalculateStat('Attack', $Pokedex_Data['Base_Stats'][1], $Level, $IVs[1], $EVs[1], $Nature)),
        'Defense' => new Stat('Defense', CalculateStat('Defense', $Pokedex_Data['Base_Stats'][2], $Level, $IVs[2], $EVs[2], $Nature)),
        'Sp_Attack' => new Stat('Sp_Attack', CalculateStat('Sp_Attack', $Pokedex_Data['Base_Stats'][3], $Level, $IVs[3], $EVs[3], $Nature)),
        'Sp_Defense' => new Stat('Sp_Defense', CalculateStat('Sp_Defense', $Pokedex_Data['Base_Stats'][4], $Level, $IVs[4], $EVs[4], $Nature)),
        'Speed' => new Stat('Speed', CalculateStat('Speed', $Pokedex_Data['Base_Stats'][5], $Level, $IVs[5], $EVs[5], $Nature)),
        'Accuracy' => new Stat('Accuracy', 100),
        'Evasion' => new Stat('Evasion', 100),
      ];
      $this->Stats_Original = [
        'Attack' => new Stat('Attack', CalculateStat('Attack', $Pokedex_Data['Base_Stats'][1], $Level, $IVs[1], $EVs[1], $Nature)),
        'Defense' => new Stat('Defense', CalculateStat('Defense', $Pokedex_Data['Base_Stats'][2], $Level, $IVs[2], $EVs[2], $Nature)),
        'Sp_Attack' => new Stat('Sp_Attack', CalculateStat('Sp_Attack', $Pokedex_Data['Base_Stats'][3], $Level, $IVs[3], $EVs[3], $Nature)),
        'Sp_Defense' => new Stat('Sp_Defense', CalculateStat('Sp_Defense', $Pokedex_Data['Base_Stats'][4], $Level, $IVs[4], $EVs[4], $Nature)),
        'Speed' => new Stat('Speed', CalculateStat('Speed', $Pokedex_Data['Base_Stats'][5], $Level, $IVs[5], $EVs[5], $Nature)),
        'Accuracy' => new Stat('Accuracy', 100),
        'Evasion' => new Stat('Evasion', 100),
      ];
      $this->IVs = $IVs;
      $this->EVs = $EVs;
      $this->Moves = [
        new Move($this->GetRandomMoveFromDatabase($Pokedex_Data['Type_Primary'], $Pokedex_Data['Type_Secondary']), 0),
        new Move($this->GetRandomMoveFromDatabase($Pokedex_Data['Type_Primary'], $Pokedex_Data['Type_Secondary']), 1),
        new Move($this->GetRandomMoveFromDatabase($Pokedex_Data['Type_Primary'], $Pokedex_Data['Type_Secondary']), 2),
        new Move($this->GetRandomMoveFromDatabase($Pokedex_Data['Type_Primary'], $Pokedex_Data['Type_Secondary']), 3),
      ];
      $this->Moves_Original = [
        new Move($this->GetRandomMoveFromDatabase($Pokedex_Data['Type_Primary'], $Pokedex_Data['Type_Secondary']), 0),
        new Move($this->GetRandomMoveFromDatabase($Pokedex_Data['Type_Primary'], $Pokedex_Data['Type_Secondary']), 1),
        new Move($this->GetRandomMoveFromDatabase($Pokedex_Data['Type_Primary'], $Pokedex_Data['Type_Secondary']), 2),
        new Move($this->GetRandomMoveFromDatabase($Pokedex_Data['Type_Primary'], $Pokedex_Data['Type_Secondary']), 3),
      ];
      $this->Item = new HeldItem(null);

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
     * @param {bool} $Trace_Proc
     */
    public function SwitchInto
    (
      bool $Trace_Proc = false
    )
    {
      if ( !$Trace_Proc )
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
            $Attacker = $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Active;
            $Defender = $_SESSION['EvoChroniclesRPG']['Battle']['Foe']->Active;
            break;
          case 'Foe';
            $Attacker = $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Active;
            $Defender = $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Active;
            break;
        }

        $Previous_Attacker = $_SESSION['EvoChroniclesRPG']['Battle'][$this->Side]->Active;

        switch ($this->Ability)
        {
          case 'Natural Cure':
            if ( !empty($this->Statuses) )
              $this->Statuses = null;
            break;

          case 'Pure Power':
            $this->Stats['Attack']->Current_Value /= 2;
            break;

          case 'Regenerator':
            $this->IncreaseHP($this->Max_HP / 3);
            break;
        }

        if ( $this->Ability_Original != $this->Ability )
          $this->Ability == $this->Ability_Original;

        foreach ($_SESSION['EvoChroniclesRPG']['Battle'][$this->Side]->Roster as $Roster_Pokemon)
        {
          $Roster_Pokemon->Active = false;

          if ( $Roster_Pokemon->Pokemon_ID == $this->Pokemon_ID )
            $Roster_Pokemon->Active = true;
        }

        if ( $Previous_Attacker->Last_Move['Name'] == 'Baton Pass' )
        {
          $Text = "{$Previous_Attacker->Display_Name} used Baton Pass!<br />";
        }

        $this->Participated = true;
        $_SESSION['EvoChroniclesRPG']['Battle'][$this->Side]->Active = $this;
      }

      $New_Active = $_SESSION['EvoChroniclesRPG']['Battle'][$this->Side]->Active;
      $this->RevertCopy();

      $Effect_Text = '';
      $Effect_Text .= $this->AbilityProcsOnEntry($this, $Defender);
      $Effect_Text .= $this->ItemProcsOnEntry($this);

      if ( !$Trace_Proc )
      {
        if ( !empty($this->Weather) )
        {
          switch ($this->Weather->Name)
          {
            case 'Hail':
              if ( $New_Active->Ability->Name == 'Slush Rush' )
                $New_Active->Stats['Speed']->Current_Value *= 2;
              break;

            case 'Extremely Harsh Sunlight':
            case 'Harsh Sunlight':
              if ( $New_Active->Ability->Name == 'Flower Gift' )
              {
                $New_Active->Stats['Attack'] *= 1.5;
                $New_Active->Stats['Sp_Defense'] *= 1.5;
              }
              break;

            case 'Rain':
            case 'Heavy Rain':
              if ( $New_Active->Ability->Name == 'Swift Swim' )
                $New_Active->Stats['Speed']->Current_Value *= 2;
              break;
          }
        }

        if ( !empty($this->Field_Effects) )
        {
          if ( $this->IsFieldEffectActive($this->Side, 'Pointed Stones') )
          {
            if
            (
              $New_Active->Ability->Name != 'Magic Guard' &&
              $New_Active->Item->Name != 'Heavy Duty Boots'
            )
            {
              $Effectiveness = $New_Active->CheckMoveWeakness('Rock');
              $New_Active->DecreaseHP($New_Active->Max_HP * pow(2, $Effectiveness) / 8);
            }
          }

          if ( $this->IsFieldEffectActive($this->Side, 'Spikes') )
          {
            $Spikes_Field_Data = $this->GetFieldEffectData($this->Side, 'Spikes');
            $Spikes_Stacks = $Spikes_Field_Data['Stacks'];

            if
            (
              $New_Active->IsGrounded() &&
              $New_Active->Ability->Name != 'Magic Guard' &&
              $New_Active->Item->Name != 'Heavy Duty Boots'
            )
            {
              $New_Active->DecreaseHP($New_Active->Max_HP * $Spikes_Stacks / 24);
            }
          }

          if ( $this->IsFieldEffectActive($this->Side, 'Steel Spikes') )
          {
            if
            (
              $New_Active->Ability->Name != 'Magic Guard' &&
              $New_Active->Item->Name != 'Heavy Duty Boots'
            )
            {
              $Effectiveness = $New_Active->CheckMoveWeakness('Steel');
              $New_Active->DecreaseHP($New_Active->Max_HP * pow(2, $Effectiveness) / 8);
            }
          }

          if ( $this->IsFieldEffectActive($this->Side, 'Sticky Web') )
          {
            if
            (
              $New_Active->IsGrounded() &&
              $New_Active->Ability->Name != 'Magic Guard' &&
              $New_Active->Item->Name != 'Heavy Duty Boots'
            )
            {
              $New_Active->Stats['Speed']->SetValue(-1);
            }
          }

          if ( $this->IsFieldEffectActive($this->Side, 'Toxic Spikes') )
          {
            $Toxic_Spikes_Field_Data = $this->GetFieldEffectData($this->Side, 'Toxic Spikes');
            $Toxic_Spikes_Stacks = $Toxic_Spikes_Field_Data['Stacks'];

            if
            (
              $New_Active->IsGrounded() &&
              !$New_Active->HasTyping([ 'Poison', 'Steel' ]) &&
              $New_Active->Ability->Name != 'Magic Guard' &&
              $New_Active->Item->Name != 'Heavy Duty Boots'
            )
            {
              if ( $Toxic_Spikes_Stacks['Stacks'] > 1 )
                $New_Active->SetStatus('Badly Poisoned');
              else
                $New_Active->SetStatus('Poisoned');

              $Effect_Text .= "<br />{$New_Active->Display_Name} was poisoned from the Toxic Spikes.";
            }
          }
        }
      }

      return [
        'Type' => 'Success',
        'Text' => (isset($Text) ? $Text : '') .
                  (isset($Trace_Proc) && !$Trace_Proc ? "{$this->Display_Name} has been sent into battle!" : "$this->Display_Name} Traced {$Defender->Display_Name}'s {$this->Ability->Name}!") .
                  (isset($Effect_Text) ? "<br />{$Effect_Text}" : '')
      ];
    }

    /**
     * Handle what happens when the Pokemon faints.
     * @param {bool} $Weather_Trigger
     */
    public function HandleFaint
    (
      bool $Weather_Trigger = false,
      int $Damage = 0
    )
    {
      if ( $this->HP > 0 )
        return;

      switch ( $this->Side )
      {
        case 'Ally':
          $Attacker = $_SESSION['EvoChroniclesRPG']['Battle']['Ally'];
          $Defender = $_SESSION['EvoChroniclesRPG']['Battle']['Foe'];
          break;
        case 'Foe':
          $Attacker = $_SESSION['EvoChroniclesRPG']['Battle']['Foe'];
          $Defender = $_SESSION['EvoChroniclesRPG']['Battle']['Ally'];
          break;
      }

      $this->Fainted = true;
      $Effect_Text = '';

      if ( $Defender->Active->Ability == 'Moxie' )
      {
        if ( $Defender->Active->Stats['Attack']->Stage < 6 )
        {
          $Defender->Active->Stats['Attack']->SetValue(1);
          $Effect_Text .= "<br />{$Defender->Active->Display_Name}'s Attack rose due to its Moxie!";
        }
      }

      if ( $Attacker->Active->Ability->Name == 'Innards Out' )
      {
        $Defender->Active->DecreaseHP($Damage);
        $Effect_Text .= "<br />{$Defender->Active->Display_Name} took damage from {$Attacker->Active->Display_Name}'s Innards Out!";
      }

      if ( !$Weather_Trigger )
      {
        if ( $Defender->Active->HasStatus('Destiny Bond') )
        {
          $Defender->Active->DecreaseHP($Defender->Active->HP);
          $Effect_Text .= "<br />{$this->Display_Name} took its foe down with it!";
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
        $Effect_Text .= $this->AbilityProcsOnFaint($Attacker, $Defender);

        $Continue = true;
      }
      else
      {
        $Restart = true;
      }

      return [
        'Type' => 'Success',
        'Text' => "{$this->Display_Name} has fainted." .
                  (!empty($Effect_Text) ? $Effect_Text : '') .
                  (!empty($Exp_Dialogue) ? "<br />{$Exp_Dialogue['Text']}" : ''),
        'Continue' => $Continue,
        'Restart' => $Restart,
        'Loser' => $this->Side
      ];
    }

    /**
     * Handle abilities that proc when a Pokemon faints.
     * @param {UserHandler} $Attacker
     * @param {UserHandler} $Defender
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
        switch ( $Defender->Active->Ability->Name )
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
     * Handle abilities that proc when a Pokemon enters battle.
     * @param {PokemonHandler} $Attacker
     * @param {PokemonHandler} $Defender
     */
    public function AbilityProcsOnEntry
    (
      PokemonHandler $Attacker,
      PokemonHandler $Defender
    )
    {
      $Effect_Text = '';

      $New_Active = $this;

      $Attacker_Owner = $_SESSION['EvoChroniclesRPG']['Battle'][$Attacker->Side];
      $Defender_Owner = $_SESSION['EvoChroniclesRPG']['Battle'][$Defender->Side];

      switch ($New_Active->Ability->Name)
      {
        case 'Anticipation':
          foreach ($Defender->Moves as $Move)
          {
            if ( $Move->Category == 'Status' )
              continue;

            if
            (
              $Move->Category == 'Ohko' ||
              $Move->MoveEffectiveness($New_Active, $Defender)['Mult'] > 1
            )
            {
              $Effect_Text .= "{$this->Display_Name} shuddered.<br />";

              break;
            }
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

        case 'Download':
          if ( $Defender->Stats['Defense']->Current_Value > $Defender->Stats['Sp_Defense']->Current_Value )
            $Boosted_Stat = 'Attack';
          else
            $Boosted_Stat = 'Sp_Attack';

          $Stat_Name = str_replace('_', 'ecial ', $Boosted_Stat);
          $New_Active->Stats[$Boosted_Stat]->SetValue(1);
          $Effect_Text .= "{$New_Active->Display_Name}'s Download raised its {$Stat_Name}!<br />";
          break;

        case 'Drizzle':
          $Turn_Count = 5;
          if ( $New_Active->Item->Name == 'Damp Rock' )
            $Turn_Count = 8;

          $Set_Weather = new Weather('Rain', $Turn_Count);
          if ( $Set_Weather )
          {
            $this->Weather[$Set_Weather->Name] = $Set_Weather;
            $Effect_Text .= $Set_Weather->Dialogue;
          }
          break;

        case 'Drought':
          $Turn_Count = 5;
          if ( $New_Active->Item->Name == 'Heat Rock' )
            $Turn_Count = 8;

          $Set_Weather = new Weather('Harsh Sunlight', $Turn_Count);
          if ( $Set_Weather )
          {
            $this->Weather[$Set_Weather->Name] = $Set_Weather;
            $Effect_Text .= $Set_Weather->Dialogue;
          }
          break;

        case 'Electric Surge':
          if ( $this->Item->Name == 'Terrain Extender' )
            $Terrain_Turns = 8;

          $Set_Terrain = new Terrain('Electric', !empty($Terrain_Turns) ?: null);
          if ( !empty($Set_Terrain) )
          {
            $this->Terrain[$Set_Terrain->Name] = $Set_Terrain;
            $Effect_Text .= $Set_Terrain->Dialogue;
          }
          break;

        case 'Forewarn':
          $Warning_Move = null;
          foreach ($Defender->Moves as $Move)
          {
            if ( empty($Warning_Move) )
            {
              $Warning_Move = $Move;
              continue;
            }

            $Base_Power = 0;
            if ( $Move->Power != 'None' )
              $Base_Power = $Move->Power;
            if ( $Move->Category == 'Ohko' )
              $Base_Power = 150;
            else if ( in_array($Move->Name, ['Counter', 'Metalburst', 'Mirror Coat']) )
              $Base_Power = 120;
            else if ( $Move->Category == 'Status' )
              $Base_Power = 80;
            else if ( in_array($Move->Name, ['Stored Power', 'Power Trip']) )
              $Base_Power = 20;
            else
              $Base_Power = 80;

            if ( $Base_Power > $Warning_Move->Power )
              $Warning_Move = $Move;
          }

          if ( !empty($Warning_Move) )
          {
            $Effect_Text .= "{$New_Active->Display_Name}'s Forewarn makes it wary of {$Defender->Display_Name}'s {$Warning_Move->Name}!";
            unset($Warning_Move);
          }
          break;

        case 'Frisk':
          if ( !empty($Defender->Item) )
          {
            $Effect_Text .= "{$New_Active->Display_Name} Frisked {$Defender->Display_Name}'s {$Defender->Item->Name}.";
          }
          break;

        case 'Gorrila Tactics':
          $New_Active->Stats['Attack']->Current_Value *= 1.5;
          $Effect_Text .= "{$New_Active->Display_Name}'s Attack was boosted by its Gorilla Tactics!";
          break;

        case 'Grassy Pelt':
          if ( !empty($this->Terrain) )
          {
            if ( $this->Terrain->Name == 'Grassy' )
            {
              $New_Active->Stats['Defense']->Current_Value *= 1.5;
            }
          }
          break;

        case 'Grassy Surge':
          if ( $this->Item->Name == 'Terrain Extender' )
            $Terrain_Turns = 8;

          $Set_Terrain = new Terrain('Grassy', !empty($Terrain_Turns) ?: null);
          if ( !empty($Set_Terrain) )
          {
            $this->Terrain[$Set_Terrain->Name] = $Set_Terrain;
            $Effect_Text .= $Set_Terrain->Dialogue;
          }
          break;

        case 'Heavy Metal':
          $this->Weight *= 2;
          break;

        case 'Huge Power':
          $New_Active->Stats['Attack']->Current_Value *= 2;
          $Effect_Text .= "{$New_Active->Display_Name}'s Attack was boosted by its Huge Power!";
          break;

        case 'Hustle':
          $New_Active->Stats['Attack']->Current_Value *= 1.5;
          $Effect_Text .= "{$New_Active->Display_Name}'s Attack was boosted by its Hustle!";
          break;

        case 'Illusion':
          $Attacker_Roster_Slots = count($Attacker_Owner->Roster);
          for ( $i = $Attacker_Roster_Slots - 1; $i >= $this->Slot; $i-- )
          {
            $Checking_Ally = $_SESSION['EvoChroniclesRPG']['Battle'][$this->Side]->Roster[$i];
            if ( !$Checking_Ally->Fainted )
            {
              $this->CopyPokemon($Checking_Ally);
              break;
            }
          }
          break;

        case 'Immunity':
          if ( $New_Active->HasStatusFromArray(['Badly Poisoned', 'Poison']) )
          {
            $New_Active->RemoveStatusFromArray(['Badly Poisoned', 'Poison']);
          }
          break;

        case 'Imposter':
          if
          (
            ($Defender->HasAbility(['Illusion']) && !$Defender->Ability->Procced) ||
            !$Defender->HasStatusFromArray(['Substitute', 'Transformed'])
          )
          {
            $Effect_Text .= "{$this->Display_Name}'s Imposter transformed it into {$Defender->Display_Name}!";

            $this->CopyPokemon($Defender, true, true, true, true, true);
          }
          break;

        case 'Intimidate':
          if ( !$Defender->HasAbility([ 'Clear Body', 'Focus', 'Full Metal Body', 'Hyper Cutter', 'Oblivious', 'Own Tempo', 'Scrappy', 'White Smoke' ]) && !$Defender->HasStatus('Substitute') )
          {
            if ( $Defender->Ability->Name == 'Rattled' && $Defender->Stats['Speed']->Current_Value < 6 )
            {
              $Defender->Stats['Speed']->SetValue(1);
              $Effect_Text .= "{$Defender->Display_Name} gained Speed from being Rattled due to {$New_Active->Display_Name}'s Intimidate!<br />";
            }

            if ( $Defender->Item->Name == 'Adrenaline Orb' && $Defender->Stats['Speed']->Current_Value < 6 )
            {
              $Defender->Stats['Speed']->SetValue(1);
              $Effect_Text .= "{$Defender->Display_Name} consumed it's Adrenaline Orb and gained Speed!<br />";
            }

            $Target = $Defender;
            if ( $Defender->Ability->Name == 'Mirror Armor')
              $Target = $New_Active;

            if ( $Target->Stats['Attack']->Current_Value > -6 )
              $Target->Stats['Attack']->SetValue(-1);

            $Effect_Text .= "{$New_Active->Display_Name}'s Intimidate cuts {$Target->Display_Name}'s Attack!";
          }
          break;

        case 'Intrepid Sword':
          $New_Active->Stats['Attack']->SetValue(1);
          $Effect_Text .= "{$New_Active->Display_Name}'s Intrepid Sword raised its Attack!<br />";
          break;

        case 'Light Metal':
          $this->Weight /= 2;
          break;

        case 'Limber':
          if ( $New_Active->HasStatus('Paralysis') )
            unset($New_Active->Statuses['Paralysis']);
          break;

        case 'Magma Armor':
          if ( $New_Active->HasStatus('Burn') )
            unset($New_Active->Statuses['Burn']);
          break;

        case 'Misty Surge':
          if ( $this->Item->Name == 'Terrain Extender' )
            $Terrain_Turns = 8;

          $Set_Terrain = new Terrain('Misty', !empty($Terrain_Turns) ?: null);
          if ( !empty($Set_Terrain) )
          {
            $this->Terrain[$Set_Terrain->Name] = $Set_Terrain;
            $Effect_Text .= $Set_Terrain->Dialogue;
          }
          break;

        case 'Multitype':
          if ( !empty($Attacker->Item) && $this->Name == 'Judgement' )
          {
            if ( $Attacker->Item->Name == 'Draco Plate' )
              $this->SetTyping('Primary', 'Dragon', true);
            else if ( $Attacker->Item->Name == 'Dread Plate' )
              $this->SetTyping('Primary', 'Earth', true);
            else if ( $Attacker->Item->Name == 'Earth Plate' )
              $this->SetTyping('Primary', 'Ground', true);
            else if ( $Attacker->Item->Name == 'Fist Plate' )
              $this->SetTyping('Primary', 'Fighting', true);
            else if ( $Attacker->Item->Name == 'Flame Plate' )
              $this->SetTyping('Primary', 'Fire', true);
            else if ( $Attacker->Item->Name == 'Icicle Plate' )
              $this->SetTyping('Primary', 'Ice', true);
            else if ( $Attacker->Item->Name == 'Insect Plate' )
              $this->SetTyping('Primary', 'Bug', true);
            else if ( $Attacker->Item->Name == 'Iron Plate' )
              $this->SetTyping('Primary', 'Steel', true);
            else if ( $Attacker->Item->Name == 'Meadow Plate' )
              $this->SetTyping('Primary', 'Grass', true);
            else if ( $Attacker->Item->Name == 'Mind Plate' )
              $this->SetTyping('Primary', 'Psychic', true);
            else if ( $Attacker->Item->Name == 'Pixie Plate' )
              $this->SetTyping('Primary', 'Fairy', true);
            else if ( $Attacker->Item->Name == 'Sky Plate' )
              $this->SetTyping('Primary', 'Flying', true);
            else if ( $Attacker->Item->Name == 'Splash Plate' )
              $this->SetTyping('Primary', 'Water', true);
            else if ( $Attacker->Item->Name == 'Spooky Plate' )
              $this->SetTyping('Primary', 'Ghost', true);
            else if ( $Attacker->Item->Name == 'Stone Plate' )
              $this->SetTyping('Primary', 'Rock', true);
            else if ( $Attacker->Item->Name == 'Toxic Plate' )
              $this->SetTyping('Primary', 'Poison', true);
            else if ( $Attacker->Item->Name == 'Zap Plate' )
              $this->SetTyping('Primary', 'Electric', true);
          }
          break;

        case 'Pastel Veil':
          if ( $New_Active->HasStatus('Poison') )
            unset($New_Active->Statuses['Poison']);

          if ( $New_Active->HasStatus('Badly Poisoned') )
            unset($New_Active->Statuses['Badly Poisoned']);
          break;

        case 'Primordial Sea':
          $Set_Weather = new Weather('Heavy Rain', -1);
          if ( $Set_Weather )
          {
            $this->Weather[$Set_Weather->Name] = $Set_Weather;
            $Effect_Text .= $Set_Weather->Dialogue;
          }
          break;

        case 'Psychic Surge':
          if ( $this->Item->Name == 'Terrain Extender' )
            $Terrain_Turns = 8;

          $Set_Terrain = new Terrain('Psychic', !empty($Terrain_Turns) ?: null);
          if ( !empty($Set_Terrain) )
          {
            $this->Terrain[$Set_Terrain->Name] = $Set_Terrain;
            $Effect_Text .= $Set_Terrain->Dialogue;
          }
          break;

        case 'Pure Power':
          $New_Active->Stats['Attack']->Current_Value *= 2;
          break;

        case 'Sand Stream':
          if ( $this->Item->Name == 'Smooth Rock' )
            $Turn_Count = 8;

          $Set_Weather = new Weather('Sandstorm', !empty($Turn_Count) ?: 5);
          if ( $Set_Weather )
          {
            $this->Weather[$Set_Weather->Name] = $Set_Weather;
            $Effect_Text .= $Set_Weather->Dialogue;
          }
          break;

        case 'Screen Cleaner':
          foreach (['Ally', 'Foe'] as $Field_Side)
          {
            foreach (['Aurora Veil', 'Light Screen', 'Reflect'] as $Field_Effect)
            {
              if ( $this->IsFieldEffectActive($Field_Side, $Field_Effect) )
              {
                $this->RemoveFieldEffect($Field_Side, $Field_Effect);
              }
            }
          }

          $Effect_Text .= 'All damage reducing field effects were removed!';
          break;

        case 'Snow Warning':
          if ( $this->Item->Name == 'Icy Rock' )
            $Turn_Count = 8;

          $Set_Weather = new Weather('Hail', !empty($Turn_Count) ?: 5);
          if ( $Set_Weather )
          {
            $this->Weather[$Set_Weather->Name] = $Set_Weather;
            $Effect_Text .= $Set_Weather->Dialogue;
          }
          break;

        case 'Trace':
          if ( !in_array($Defender->Ability->Name, ['Disguise', 'Flower Gift', 'Gulp Missle', 'Hunger Switch', 'Ice Face', 'Illusion', 'Imposter', 'Neutralizing Gas', 'Receiver', 'RKS System', 'Schooling', 'Stance Change', 'Trace', 'Zen Mode']) )
          {
            $this->SetAbility($Defender->Ability->Name);

            $Effect_Text .= "{$this->Display_Name} has traced {$Defender->Display_Name}'s {$this->Ability->Name}!";
            $Effect_Text .= $this->SwitchInto(true)['Text'];
          }
      }

      if ( $Effect_Text != '' )
        return $Effect_Text;
    }

    /**
     * Handle items that need to proc on entry, such as Assault Vest.
     */
    public function ItemProcsOnEntry()
    {
      $Item_Text_On_Entry = '<br />';

      switch ( $this->Item->Name )
      {
        case 'Assault Vest':
          $this->Stats['Sp_Defense']->Current_Value *= 1.5;
          break;

        case 'Berserk Gene':
          if ( $this->Stats['Attack']->Stage < 6 && !$this->HasStatus('Confusion') )
          {
            $this->Stats['Attack']->SetValue(2);
            $this->SetStatus('Confusion');

            $Item_Text_On_Entry .= "{$this->Display_Name} raised its Attack and became Confused by consuming its Berserk Gene!";
          }
          break;

        case 'Choice Band':
          $this->Stats['Attack']->Current_Value *= 1.5;
          break;

        case 'Choice Scarf':
          $this->Stats['Speed']->Current_Value *= 1.5;
          break;

        case 'Choice Specs':
          $this->Stats['Sp_Attack']->Current_Value *= 1.5;
          break;

        case 'Deep Sea Scale':
          if ( $this->Pokedex_ID == 366 )
            $this->Stats['Sp_Defense']->Current_Value *= 2;
          break;

        case 'Deep Sea Tooth':
          if ( $this->Pokedex_ID == 366 )
            $this->Stats['Sp_Attack']->Current_Value *= 2;
          break;

        case 'Eviolite':
          if ( $this->Can_Evolve )
          {
            $this->Stats['Defense']->Current_Value *= 1.5;
            $this->Stats['Sp_Defense']->Current_Value *= 1.5;
          }
          break;

        case 'Float Stone':
          $this->Weight *= 0.5;
          break;

        case 'Light Ball':
          if ( $this->Pokedex_ID === 25 )
          {
            $this->Stats['Attack']->Current_Value *= 2;
            $this->Stats['Sp_Attack']->Current_Value *= 2;
          }
          break;

        case 'Metal Powder':
          if ( $this->Pokedex_ID === 132 && $this->Pokedex_ID_Original === 132 )
            $this->Stats['Defense']->Current_Value *= 2;
          break;

        case 'Quick Powder':
          if ( $this->Pokedex_ID === 132 && $this->Pokedex_ID_Original === 132 )
            $this->Stats['Speed']->Current_Value *= 2;
          break;

        case 'Thuck Club':
          if ( in_array($this->Pokedex_ID, [104, 105]) )
            $this->Stats['Attack']->Current_Value *= 2;
          break;

        case 'Wide Lens':
          $this->Stats['Accuracy']->Current_Value *= 1.1;
          break;
      }

      return $Item_Text_On_Entry;
    }

    /**
     * Revive the Pokemon.
     *
     * @param {int} $HP_Amount
     */
    public function Revive
    (
      int $HP_Amount
    )
    {
      if ( !$this->Fainted )
        return false;

      $this->Fainted = false;
      $this->HP = $this->Max_HP;

      return $this;
    }

    /**
     * Increase the Pokemon's current Exp.
     */
    public function IncreaseExp()
    {
      global $PDO;

      $Exp_Divisor = 0;
      foreach ( $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Roster as $Pokemon )
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

      foreach ( $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Roster as $Pokemon )
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

          $Pokemon->Exp += $Exp;
          $Pokemon->Exp_Needed = FetchExpToNextLevel($Pokemon->Exp, 'Pokemon', true);

          $Dialogue['Text'] .= "{$Pokemon->Display_Name} has gained " . number_format($Exp) . " experience.<br />";

          $Check_Level = FetchLevel($Pokemon->Exp, 'Pokemon');
          if ( $Pokemon->Level != $Check_Level )
          {
            $Pokemon->Level = $Check_Level;
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
        $Ally_Active = $_SESSION['EvoChroniclesRPG']['Battle']['Ally']->Active;

      $Foe_Active = $_SESSION['EvoChroniclesRPG']['Battle']['Foe']->Active;

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
     * @param {int} $Heal
     * @param {bool} $Full_Heal
     */
    public function IncreaseHP
    (
      int $Heal,
      bool $Full_Heal = false
    )
    {
      if ( !empty($Full_Heal) )
        $this->HP += $Heal;
      else
        $this->HP = $this->Max_HP;

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
      if ( in_array($this->Ability->Name, $Abilities) )
        return true;

      return false;
    }

    /**
     * Set the Pokemon's Ability.
     * @param string $Ability
     */
    public function SetAbility
    (
      string $Ability_Name
    )
    {
      if ( $this->Ability->Name == $Ability_Name )
        return false;

      return $this->Ability = new Ability($Ability_Name);
    }

    /**
     * Set a status on the Pokemon.
     *
     * @param string $Status
     * @param int $Turns
     */
    public function SetStatus
    (
      string $Status,
      int $Turns = null
    )
    {
      $Status_Data = Status::AllStatuses()[$Status];
      if ( empty($Status_Data) )
        return false;

      if ( $this->HasStatus($Status) )
        return false;

      if ( $this->Ability->Name == 'Shields Down' )
        return false;

      if ( !$Status_Data['Volatile'] )
      {
        if ( $this->Ability->Name == 'Comatose' )
          return false;

        if ( $Status == 'Burn' && $this->HasTyping(['Fire']) && !$this->HasItem(['Flame Orb']) )
          return false;

        if ( $Status == 'Paralysis' && $this->HasTyping(['Electric']) )
          return false;

        if ( $Status == 'Poison' && $this->HasTyping(['Poison', 'Steel']) && $this->Ability->Name != 'Corrosion' && !$this->HasItem(['Toxic Orb']) )
          return false;

        if ( $Status == 'Sleep' && $this->HasAbility(['Insomnia', 'Sweet Veil']) )
          return false;
      }
      else
      {
        if ( in_array($this->Item->Name, ['Flame Orb', 'Toxic Orb']) )
          if ( in_array($this->Ability->Name, ['Flower Veil']) )
            return false;

        if ( in_array($this->Ability->Name, ['Leaf Guard', 'Comatose']) )
          return false;

        if ( $this->HasStatus('Safeguard') )
          return false;

        if ( in_array($Status, ['Encore', 'Heal Block', 'Infatuation', 'Taunt', 'Torment']) && $this->Ability->Name == 'Aroma Veil' )
          return false;

        if ( $this->Ability->Name == 'Oblivious' && $Status == 'Infatuation' )
          return false;

        if ( $this->Ability->Name == 'Own Tempo' && $Status == 'Confusion' )
          return false;
      }

      $Attempt_Status = new Status(
        $this,
        $Status,
        $Turns
      );

      $this->Statuses[$Attempt_Status->Name] = $Attempt_Status;

      return $this->Statuses[$Attempt_Status->Name];
    }

    /**
     * Update the desired status of the Pokemon.
     */
    public function UpdateStatus
    (
      string $Status_Name
    )
    {
      $Status_Data = $this->HasStatus($Status_Name);
      if ( empty($Status_Data) )
        return false;

      if ( $Status_Data->Turns_Left > 0 )
        $Status_Data->DecrementTurnCount();

      if ( !empty($Status_Data->Stacks) )
        $Status_Data->IncrementStacks();

      if ( $Status_Data->Turns_Left === 0 )
        unset($this->Statuses[$Status_Name]);

      if ( !empty($this->Statuses[$Status_Name]) )
        $this->Statuses[$Status_Name] = $Status_Data;
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

      if ( $this->Item->Name == 'Air Balloon' )
        return false;
    }

    /**
     * Reset all stat's back to their base.
     */
    public function ResetStats()
    {
      $Fetch_Stats = GetCurrentStats($this->Pokemon_ID, $this->Pokedex_ID, $this->Alt_ID);
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

    /**
     * Set the Pokemon's current Typing.
     * @param {string} $Primary_Or_Secondary
     * @param {string} $Typing
     * @param {bool} $Unset_Secondary_Typing
     * @return {bool}
     */
    public function SetTyping
    (
      string $Primary_Or_Secondary,
      string $Typing,
      bool $Null_Secondary_Typing = false
    )
    {
      $Valid_Types = [
        'Fighting', 'Flying', 'Poison', 'Ground', 'Rock',
        'Bug', 'Ghost', 'Steel', 'Fire', 'Water',
        'Grass', 'Electric', 'Psychic', 'Ice', 'Dragon', 'Dark'
      ];

      if ( !in_array($Typing, $Valid_Types) )
        return false;

      if ( !in_array($Primary_Or_Secondary, ['Primary', 'Secondary']) )
        return false;

      switch ( $Primary_Or_Secondary )
      {
        case 'Primary':
          if ( $this->Primary_Type == $Typing )
            return false;

          $this->Primary_Type = $Typing;
          break;

        case 'Secondary':
          if ( $this->Secondary_Type == $Typing )
            return false;

          $this->Secondary_Type = $Typing;
          break;
      }

      if ( $Null_Secondary_Typing && $this->Secondary_Type !== null )
        $this->Secondary_Type = null;

      return true;
    }

    /**
     * Determine if the Pokemon has a specified typing.
     * @param {array} $List_Of_Items
     */
    public function HasItem
    (
      array $List_Of_Items
    )
    {
      if ( empty($this->Item) )
        return false;

      if ( in_array($this->Item->Name, $List_Of_Items) )
        return true;

      return false;
    }

    /**
     * Check if the Pokemon has a specified nature.
     * @param {array} $Natures
     * @return {bool}
     */
    public function HasNature
    (
      array $Natures
    )
    {
      if ( in_array($this->Nature, $Natures) )
        return true;

      return false;
    }

    /**
     * Copy the specified Pokemon's Pokedex ID and Alt ID.
     * Used in cases such as Transform (Move), Imposter (Ability), and Illusion (Ability).
     * @param {PokemonHandler} $Pokemon
     */
    public function CopyPokemon
    (
      PokemonHandler $Pokemon,
      bool $Copy_Ability = false,
      bool $Copy_Moves = false,
      bool $Copy_Stats = false,
      bool $Copy_Weight = false,
      bool $Copy_Gender = false
    )
    {
      if ( $this->Pokedex_ID != $Pokemon->Pokedex_ID )
        $this->Pokedex_ID = $Pokemon->Pokedex_ID;

      if ( $this->Alt_ID != $Pokemon->Alt_ID )
        $this->Alt_ID = $Pokemon->Alt_ID;

      if ( $this->Sprite != $Pokemon->Sprite )
        $this->Sprite = $Pokemon->Sprite;

      if ( $this->Icon != $Pokemon->Icon )
        $this->Icon = $Pokemon->Icon;

      if ( $this->Display_Name != $Pokemon->Display_Name )
        $this->Display_Name = $Pokemon->Display_Name;

      if ( $Copy_Ability )
        if ( $this->Ability != $Pokemon->Ability )
          $this->Ability = $Pokemon->Ability;

      if ( $Copy_Moves )
        if ( $this->Moves != $Pokemon->Moves )
          $this->Moves = $Pokemon->Moves;

      if ( $Copy_Stats )
        if ( $this->Stats != $Pokemon->Stats )
          $this->Stats = $Pokemon->Stats;

      if ( $Copy_Weight )
        if ( $this->Weight != $Pokemon->Weight )
          $this->Weight = $Pokemon->Weight;

      if ( $Copy_Gender )
        if ( $this->Gender != $Pokemon->Gender )
          $this->Gender = $Pokemon->Gender;
    }

    /**
     * Revert a copied Pokemon back to its original form.
     */
    public function RevertCopy()
    {
      if ( $this->Pokedex_ID != $this->Pokedex_ID_Original )
        $this->Pokedex_ID = $this->Pokedex_ID_Original;

      if ( $this->Alt_ID != $this->Alt_ID_Original )
        $this->Alt_ID = $this->Alt_ID_Original;

      if ( $this->Sprite != $this->Sprite_Original )
        $this->Sprite = $this->Sprite_Original;

      if ( $this->Icon != $this->Icon_Original )
        $this->Icon = $this->Icon_Original;

      if ( $this->Display_Name != $this->Display_Name_Original )
        $this->Display_Name = $this->Display_Name_Original;

      if ( $this->Ability != $this->Ability_Original )
        $this->Ability = $this->Ability_Original;

      if ( $this->Moves != $this->Moves_Original )
        $this->Moves = $this->Moves_Original;

      if ( $this->Stats != $this->Stats_Original )
        $this->Stats = $this->Stats_Original;

      if ( $this->Weight != $this->Weight_Original )
        $this->Weight = $this->Weight_Original;

      if ( $this->Gender != $this->Gender_Original )
        $this->Gender = $this->Gender_Original;

      if ( $this->Critical_Hit_Boost > 0 )
        $this->Critical_Hit_Boost = 0;
    }

    /**
     * Reset the Pokemon's typings.
     */
    public function ResetTyping()
    {
      if ( $this->Primary_Type != $this->Primary_Type_Original )
        $this->Primary_Type = $this->Primary_Type_Original;

      if ( $this->Secondary_Type != $this->Secondary_Type_Original )
        $this->Secondary_Type = $this->Secondary_Type_Original;
    }

    /**
     * Determine how effective a given move type would be against the Pokemon.
     * @param {string} $Move_Type
     */
    public function CheckMoveWeakness
    (
      string $Move_Type
    )
    {
      $Types = [
        'Normal', 'Fire', 'Water', 'Electric',
        'Grass', 'Ice', 'Fighting', 'Poison',
        'Ground', 'Flying', 'Psychic', 'Bug',
        'Rock', 'Ghost', 'Dragon', 'Dark',
        'Steel', 'Fairy'
      ];

      $Type_Chart = [
        // N  FIR  WAT  ELE  GRA  ICE  FIG  POI  GRO  FLY  PSY  BUG  ROC  GHO  DRA  DAR  STE  FAI
        [1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 0.5, 0.0, 1.0, 1.0, 0.5, 1.0], // Normal
        [1.0, 0.5, 0.5, 1.0, 2.0, 2.0, 1.0, 1.0, 1.0, 1.0, 1.0, 2.0, 0.5, 1.0, 0.5, 1.0, 2.0, 1.0], // Fire
        [1.0, 2.0, 0.5, 1.0, 0.5, 1.0, 1.0, 1.0, 2.0, 1.0, 1.0, 1.0, 2.0, 1.0, 0.5, 1.0, 1.0, 1.0], // Water
        [1.0, 1.0, 2.0, 0.5, 0.5, 1.0, 1.0, 1.0, 0.0, 2.0, 1.0, 1.0, 1.0, 1.0, 0.5, 1.0, 1.0, 1.0], // Electric
        [1.0, 0.5, 2.0, 1.0, 0.5, 1.0, 1.0, 0.5, 2.0, 0.5, 1.0, 0.5, 2.0, 1.0, 0.5, 1.0, 0.5, 1.0], // Grass
        [1.0, 0.5, 0.5, 1.0, 2.0, 0.5, 1.0, 1.0, 2.0, 2.0, 1.0, 1.0, 1.0, 1.0, 2.0, 1.0, 0.5, 1.0], // Ice
        [2.0, 1.0, 1.0, 1.0, 1.0, 2.0, 1.0, 0.5, 1.0, 0.5, 0.5, 0.5, 2.0, 0.0, 1.0, 2.0, 2.0, 0.5], // Fighting
        [1.0, 1.0, 1.0, 1.0, 2.0, 1.0, 1.0, 0.5, 0.5, 1.0, 1.0, 1.0, 0.5, 0.5, 1.0, 1.0, 0.0, 2.0], // Poison
        [1.0, 2.0, 1.0, 2.0, 0.5, 1.0, 1.0, 2.0, 1.0, 0.0, 1.0, 0.5, 2.0, 1.0, 1.0, 1.0, 2.0, 1.0], // Ground
        [1.0, 1.0, 1.0, 0.5, 2.0, 1.0, 2.0, 1.0, 1.0, 1.0, 1.0, 2.0, 0.5, 1.0, 1.0, 1.0, 0.5, 1.0], // Flying
        [1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 2.0, 2.0, 1.0, 1.0, 0.5, 1.0, 1.0, 1.0, 1.0, 0.0, 0.5, 1.0], // Psychic
        [1.0, 0.5, 1.0, 1.0, 2.0, 1.0, 0.5, 0.5, 1.0, 0.5, 2.0, 1.0, 1.0, 0.5, 1.0, 2.0, 0.5, 0.5], // Bug
        [1.0, 2.0, 1.0, 1.0, 1.0, 2.0, 0.5, 1.0, 0.5, 2.0, 1.0, 2.0, 1.0, 1.0, 1.0, 1.0, 0.5, 1.0], // Rock
        [0.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 2.0, 1.0, 1.0, 2.0, 1.0, 0.5, 1.0, 1.0], // Ghost
        [1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 2.0, 1.0, 0.5, 0.0], // Dragon
        [1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 0.5, 1.0, 1.0, 1.0, 2.0, 1.0, 1.0, 2.0, 1.0, 0.5, 1.0, 0.5], // Dark
        [1.0, 0.5, 0.5, 0.5, 1.0, 2.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 2.0, 1.0, 1.0, 1.0, 0.5, 2.0], // Steel
        [1.0, 0.5, 1.0, 1.0, 1.0, 1.0, 2.0, 0.5, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 2.0, 2.0, 0.5, 1.0], // Fairy
      ];

      $Move_Type = array_search($Move_Type, $Types);

      $Type_1_Mult = array_search($this->Primary_Type, $Types);
      if ( !$Type_1_Mult )
        $Primary_Mult = 1;
      else
        $Primary_Mult = $Type_Chart[$Move_Type][$Type_1_Mult];

      $Type_2_Mult = array_search($this->Secondary_Type, $Types);
      if ( !$Type_2_Mult )
        $Secondary_Mult = 1;
      else
        $Secondary_Mult = $Type_Chart[$Move_Type][$Type_2_Mult];

      $Effective_Mult = $Primary_Mult * $Secondary_Mult;

      return $Effective_Mult;
    }

    /**
     * Get a random move given a Pokemon's typing.
     *
     * @param {string} $Primary_Type
     * @param {string} $Secondary_Type
     */
    public function GetRandomMoveFromDatabase
    (
      string $Primary_Type,
      string $Secondary_Type
    )
    {
      global $PDO;

      try
      {
        $Fetch_Random_Move = $PDO->prepare("
          SELECT `ID`
          FROM `moves`
          WHERE `Move_Type` = ? OR `Move_Type` = ?
          ORDER BY RAND()
          LIMIT 1
        ");
        $Fetch_Random_Move->execute([ $Primary_Type, $Secondary_Type ]);
        $Fetch_Random_Move->setFetchMode(PDO::FETCH_ASSOC);
        $Random_Move = $Fetch_Random_Move->fetch();
      }
      catch ( \PDOException $e )
      {
        HandleError($e);
      }

      if ( empty($Random_Move) )
        return false;

      return $Random_Move['ID'];
    }

    /**
     * Generate a random set of IVs.
     */
    public function GenerateIVs()
    {
      return [
        mt_rand(0, 31),
        mt_rand(0, 31),
        mt_rand(0, 31),
        mt_rand(0, 31),
        mt_rand(0, 31),
        mt_rand(0, 31)
      ];
    }

    /**
     * Generate a random set of EVs.
     */
    public function GenerateEVs()
    {
      $Total_EVs = 0;
      $EVs = [0, 0, 0, 0, 0, 0];
      $EV_Cap = mt_rand(255, 510);

      while ($Total_EVs < $EV_Cap)
      {
        $Stat_Index = mt_rand(0, 5);
        if ( $EVs[$Stat_Index] > 252 )
          continue;

        $EV_Amount = mt_rand(1, 3);
        $EVs[$Stat_Index] += $EV_Amount;
        $Total_EVs += $EV_Amount;
      }

      return $EVs;
    }
  }
