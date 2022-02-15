<?php
  /**
   * Show an HTML dropdown of all possible item entries.
   */
  function ShowMovesDropdown()
  {
    global $PDO;

    try
    {
      $Get_Move_Entries = $PDO->prepare("
        SELECT *
        FROM `moves`
        ORDER BY `Name` ASC, `ID` ASC
      ");
      $Get_Move_Entries->execute([ ]);
      $Get_Move_Entries->setFetchMode(PDO::FETCH_ASSOC);
      $Move_Entries = $Get_Move_Entries->fetchAll();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    if ( empty($Move_Entries) )
    {
      return "
        <select name='move_entries'>
          <option>There are no move entries</option>
        </select>
      ";
    }

    $Dropdown_Entries = '';
    foreach ( $Move_Entries as $Move )
    {
      $Dropdown_Entries .= "
        <option value='{$Move['ID']}'>
          {$Move['Name']}
        </option>
      ";
    }

    return "
      <select name='move_entries' onchange='ShowMoveEntry();'>
        <option>Select A Move</option>
        {$Dropdown_Entries}
      </select>
    ";
  }

  /**
   * Show an HTML table allowing for editing of the specified move's properties.
   *
   * @param $Move_ID
   */
  function ShowMoveEditTable
  (
    $Move_ID
  )
  {
    global $PDO;

    try
    {
      $Get_Move_Data = $PDO->prepare("
        SELECT *
        FROM `moves`
        INNER JOIN `moves_flags`
        ON `moves`.`ID` = `moves_flags`.`ID`
        WHERE `moves`.`ID` = ?
        LIMIT 1
      ");
      $Get_Move_Data->execute([ $Move_ID ]);
      $Get_Move_Data->setFetchMode(PDO::FETCH_ASSOC);
      $Move_Data = $Get_Move_Data->fetch();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    $Move_Config_Options = [
      'Naming' => [
        'Name' => [ 'Field_Name' => 'Name', 'Field_Type' => 'Text' ],
        'Class Name' => [ 'Field_Name' => 'Class_Name', 'Field_Type' => 'Text' ],
      ],
      'Base Stats' => [
        'Accuracy' => [ 'Field_Name' => 'Accuracy', 'Field_Type' => 'Number' ],
        'Power' => [ 'Field_Name' => 'Power', 'Field_Type' => 'Number' ],
        'Priority' => [ 'Field_Name' => 'Priority', 'Field_Type' => 'Number' ],
        'Base PP' => [ 'Field_Name' => 'PP', 'Field_Type' => 'Number' ],
      ],
      'Typing & Category' => [
        'Damage Type' => [ 'Field_Name' => 'Damage_Type', 'Field_Type' => 'Dropdown' ],
        'Move Type' => [ 'Field_Name' => 'Move_Type', 'Field_Type' => 'Dropdown' ],
        'Category' => [ 'Field_Name' => 'Category', 'Field_Type' => 'Dropdown' ],
        'Ailment' => [ 'Field_Name' => 'Ailment', 'Field_Type' => 'Dropdown' ],
      ],
      'Probability' => [
        'Flinch Chance' => [ 'Field_Name' => 'Flinch_Chance', 'Field_Type' => 'Number' ],
        'Crit Modifier' => [ 'Field_Name' => 'Crit_Chance', 'Field_Type' => 'Number' ],
        'Effect Chance' => [ 'Field_Name' => 'Effect_Chance', 'Field_Type' => 'Number' ],
        'Ailment Chance' => [ 'Field_Name' => 'Ailment_Chance', 'Field_Type' => 'Number' ],
      ],
      'Stat Boosts' => [
        'HP' => [ 'Field_Name' => 'HP_Boost', 'Field_Type' => 'Number' ],
        'Attack' => [ 'Field_Name' => 'Attack_Boost', 'Field_Type' => 'Number' ],
        'Defense' => [ 'Field_Name' => 'Defense_Boost', 'Field_Type' => 'Number' ],
        'Sp. Attack' => [ 'Field_Name' => 'Sp_Attack_Boost', 'Field_Type' => 'Number' ],
        'Sp. Defense' => [ 'Field_Name' => 'Sp_Defense_Boost', 'Field_Type' => 'Number' ],
        'Speed' => [ 'Field_Name' => 'Speed_Boost', 'Field_Type' => 'Number' ],
        'Accuracy' => [ 'Field_Name' => 'Accuracy_Boost', 'Field_Type' => 'Number' ],
        'Evasion' => [ 'Field_Name' => 'Evasion_Boost', 'Field_Type' => 'Number' ],
      ],
      'Misc. Info' => [
        'Min. Hits' => [ 'Field_Name' => 'Min_Hits', 'Field_Type' => 'Number' ],
        'Max. Hits' => [ 'Field_Name' => 'Max_Hits', 'Field_Type' => 'Number' ],
        'Min. Turns' => [ 'Field_Name' => 'Min_Turns', 'Field_Type' => 'Number' ],
        'Max. Turns' => [ 'Field_Name' => 'Max_Turns', 'Field_Type' => 'Number' ],
        'Recoil Taken' => [ 'Field_Name' => 'Recoil', 'Field_Type' => 'Number' ],
        'Drain Amount' => [ 'Field_Name' => 'Drain', 'Field_Type' => 'Number' ],
        'Heal Amount' => [ 'Field_Name' => 'Healing', 'Field_Type' => 'Number' ],
        'Stat Chance' => [ 'Field_Name' => 'Stat_Chance', 'Field_Type' => 'Number' ],
      ],
      'Move Flags' => [
        'Authentic' => [ 'Field_Name' => 'authentic', 'Field_Type' => 'Checkbox' ],
        'Bite' => [ 'Field_Name' => 'bite', 'Field_Type' => 'Checkbox' ],
        'Bullet' => [ 'Field_Name' => 'bullet', 'Field_Type' => 'Checkbox' ],
        'Charge' => [ 'Field_Name' => 'charge', 'Field_Type' => 'Checkbox' ],
        'Contact' => [ 'Field_Name' => 'contact', 'Field_Type' => 'Checkbox' ],
        'Dance' => [ 'Field_Name' => 'dance', 'Field_Type' => 'Checkbox' ],
        'Defrost' => [ 'Field_Name' => 'defrost', 'Field_Type' => 'Checkbox' ],
        'Distance' => [ 'Field_Name' => 'distance', 'Field_Type' => 'Checkbox' ],
        'Gravity' => [ 'Field_Name' => 'gravity', 'Field_Type' => 'Checkbox' ],
        'Heal' => [ 'Field_Name' => 'heal', 'Field_Type' => 'Checkbox' ],
        'Mirror' => [ 'Field_Name' => 'mirror', 'Field_Type' => 'Checkbox' ],
        'Mystery' => [ 'Field_Name' => 'mystery', 'Field_Type' => 'Checkbox' ],
        'Non-Sky' => [ 'Field_Name' => 'nonsky', 'Field_Type' => 'Checkbox' ],
        'Powder' => [ 'Field_Name' => 'powder', 'Field_Type' => 'Checkbox' ],
        'Protect' => [ 'Field_Name' => 'protect', 'Field_Type' => 'Checkbox' ],
        'Pulse' => [ 'Field_Name' => 'pulse', 'Field_Type' => 'Checkbox' ],
        'Punch' => [ 'Field_Name' => 'punch', 'Field_Type' => 'Checkbox' ],
        'Recharge' => [ 'Field_Name' => 'recharge', 'Field_Type' => 'Checkbox' ],
        'Reflectable' => [ 'Field_Name' => 'reflectable', 'Field_Type' => 'Checkbox' ],
        'Snatch' => [ 'Field_Name' => 'snatch', 'Field_Type' => 'Checkbox' ],
        'Sound' => [ 'Field_Name' => 'sound', 'Field_Type' => 'Checkbox' ],
        'Empty' => [ 'Field_Name' => '', 'Field_Type' => 'Blank' ],
        'Empty ' => [ 'Field_Name' => '', 'Field_Type' => 'Blank' ],
        'Empty  ' => [ 'Field_Name' => '', 'Field_Type' => 'Blank' ],
      ],
    ];

    $Move_Config_Table_Rows = '';

    foreach ( $Move_Config_Options as $Option_Category => $Sub_Children )
    {
      $Config_Row_Text = "
        <thead>
          <tr>
            <th colspan='40' style='width: 100%;'>
              <b>{$Option_Category}</b>
            </th>
          </tr>
        </thead>
      ";

      $Child_Options_Text = '';

      $Column_Span = 40 / count($Sub_Children);
      if ( $Option_Category == 'Move Flags' )
        $Column_Span = 40 / 8;

      $Width = 100 / count($Sub_Children);

      $Current_Child = 0;
      foreach ( $Sub_Children as $Child_Option => $Child_Fields )
      {
        switch ( $Child_Fields['Field_Type'] )
        {
          case 'Blank':
            $Input_HTML = '';
            break;

          case 'Checkbox':
            $Input_HTML = "
              <input
                temp_val='{$Move_Data[$Child_Fields['Field_Name']]}'
                type='checkbox'
                name='{$Child_Fields['Field_Name']}'
                value='{$Move_Data[$Child_Fields['Field_Name']]}'
                " . ($Move_Data[$Child_Fields['Field_Name']] ? 'checked' : '') . "
              />
            ";
            break;

          case 'Dropdown':
            switch ( $Child_Fields['Field_Name'] )
            {
              case 'Damage_Type':
                $Options = [ 'Physical', 'Special', 'Status' ];
                break;

              case 'Move_Type':
                $Options = [ 'None', 'Normal', 'Water', 'Fire', 'Grass', 'Electric', 'Ground', 'Flying', 'Fighting', 'Psychic', 'Dark', 'Ghost', 'Bug', 'Rock', 'Ice', 'Steel', 'Dragon', 'Poison', 'Fairy' ];
                break;

              case 'Category':
                $Options = [ 'Damage+heal', 'Damage+lower', 'Unique', 'Damage+ailment', 'Ohko', 'Whole-field-effect', 'Field-effect', 'Damage', 'Heal', 'Ailment', 'Damage+raise', 'Swagger', 'Stat Change', 'Force-switch' ];
                break;

              case 'Ailment':
                $Options = [ 'Badly Poisoned', 'Burn', 'Confusion', 'Disable', 'Embargo', 'Flinch', 'Freeze', 'Heal-block', 'Infatuation', 'Ingrain', 'Leech-seed', 'Nightmare', 'No-type-immunity', 'None', 'Paralysis', 'Perish-song', 'Poison', 'Silence', 'Sleep', 'Torment', 'Trap', 'Unknown', 'Yawn' ];
                break;
            }


            $Option_Text = '';
            foreach ( $Options as $Option )
            {
              $Option_Text .= "
                <option
                  temp_val='{$Move_Data[$Child_Fields['Field_Name']]}'
                  style='width: 180px;'
                  value='{$Option}'
                  " . ($Move_Data[$Child_Fields['Field_Name']] == $Option ? 'selected' : '') . "
                >
                  {$Option}
                </option>
              ";
            }

            $Input_HTML = "
              <select name='{$Child_Fields['Field_Name']}'>
                {$Option_Text}
              </select>
            ";
            break;

          case 'Number':
            $Field_Value = 0;
            if ( !empty($Move_Data[$Child_Fields['Field_Name']]) && $Move_Data[$Child_Fields['Field_Name']] != 'None' )
              $Field_Value = $Move_Data[$Child_Fields['Field_Name']];

            $Input_HTML = "
              <input
                temp_val='{$Move_Data[$Child_Fields['Field_Name']]}'
                type='number'
                name='{$Child_Fields['Field_Name']}'
                value='{$Field_Value}'
                style='width: 70px;'
              />
            ";
            break;

          case 'Text':
            $Input_HTML = "
              <input
                temp_val='{$Move_Data[$Child_Fields['Field_Name']]}'
                type='text'
                name='{$Child_Fields['Field_Name']}'
                value='{$Move_Data[$Child_Fields['Field_Name']]}'
                style='width: 180px;'
              />
            ";
            break;

          case 'Textarea':
            $Input_HTML = "
              <textarea cols='80' rows='5' name='{$Child_Fields['Field_Name']}'>{$Move_Data[$Child_Fields['Field_Name']]}</textarea>
            ";
            break;
        }

        $Child_Options_Text .= "
          <td colspan='{$Column_Span}' style='width: {$Width}%;'>
            <b>{$Child_Option}</b>
            <br />
            {$Input_HTML}
          </td>
        ";

        if
        (
          $Option_Category == 'Move Flags' &&
          $Current_Child < 21 &&
          $Current_Child % 8 == 7
        )
          $Child_Options_Text .= '</tr><tr>';

        $Current_Child++;
      }

      $Move_Config_Table_Rows .= "
        {$Config_Row_Text}

        <tbody>
          {$Child_Options_Text}
        </tbody>
      ";
    }

    return "
      <table class='border-gradient' style='width: 600px;'>
        <thead>
          <tr>
            <th colspan='40'>
              Editing Move Entry #{$Move_ID}
            </th>
          </tr>
        </thead>

        <tbody>
          <tr>
            <td colspan='40'>
              <h3>{$Move_Data['Name']}</h3>
            </td>
          </tr>
        </tbody>

        {$Move_Config_Table_Rows}

        <tbody>
          <tr>
            <td colspan='40' style='padding: 5px;'>
              <button onclick='UpdateMoveData({$Move_ID});'>
                Update Move
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    ";
  }

  /**
   * Update the given move and its battle flags.
   *
   * @param $Name
   * @param $Class_Name
   * @param $Accuracy
   * @param $Power
   * @param $Priority
   * @param $PP
   * @param $Damage_Type
   * @param $Move_Type
   * @param $Category
   * @param $Ailment
   * @param $Flinch_Chance
   * @param $Crit_Chance
   * @param $Effect_Chance
   * @param $Ailment_Chance
   * @param $HP_Boost
   * @param $Attack_Boost
   * @param $Defense_Boost
   * @param $Sp_Attack_Boost
   * @param $Sp_Defense_Boost
   * @param $Speed_Boost
   * @param $Accuracy_Boost
   * @param $Evasion_Boost
   * @param $Min_Hits
   * @param $Max_Hits
   * @param $Min_Turns
   * @param $Max_Turns
   * @param $Recoil
   * @param $Drain
   * @param $Healing
   * @param $Stat_Chance
   * @param $authentic
   * @param $bite
   * @param $bullet
   * @param $charge
   * @param $contact
   * @param $dance
   * @param $defrost
   * @param $distance
   * @param $gravity
   * @param $heal
   * @param $mirror
   * @param $mystery
   * @param $nonsky
   * @param $powder
   * @param $protect
   * @param $pulse
   * @param $punch
   * @param $recharge
   * @param $reflectable
   * @param $snatch
   * @param $sound
   *
   */
  function UpdateMoveData
  (
    $Move_ID, $Name, $Class_Name, $Accuracy, $Power, $Priority, $PP, $Damage_Type, $Move_Type, $Category, $Ailment, $Flinch_Chance, $Crit_Chance, $Effect_Chance, $Ailment_Chance, $HP_Boost, $Attack_Boost, $Defense_Boost, $Sp_Attack_Boost, $Sp_Defense_Boost, $Speed_Boost, $Accuracy_Boost, $Evasion_Boost, $Min_Hits, $Max_Hits, $Min_Turns, $Max_Turns, $Recoil, $Drain, $Healing, $Stat_Chance, $authentic, $bite, $bullet, $charge, $contact, $dance, $defrost, $distance, $gravity, $heal, $mirror, $mystery, $nonsky, $powder, $protect, $pulse, $punch, $recharge, $reflectable, $snatch, $sound
  )
  {
    global $PDO;

    try
    {
      $PDO->beginTransaction();

      $Update_Move_Data = $PDO->prepare("
        UPDATE `moves`
        SET `Name` = ?, `Class_Name` = ?, `Accuracy` = ?, `Power` = ?, `Priority` = ?, `PP` = ?, `Damage_Type` = ?, `Move_Type` = ?, `Category` = ?, `Ailment` = ?, `Flinch_Chance` = ?, `Crit_Chance` = ?, `Effect_Chance` = ?, `Ailment_Chance` = ?, `HP_Boost` = ?, `Attack_Boost` = ?, `Defense_Boost` = ?, `Sp_Attack_Boost` = ?, `Sp_Defense_Boost` = ?, `Speed_Boost` = ?, `Accuracy_Boost` = ?, `Evasion_Boost` = ?, `Min_Hits` = ?, `Max_Hits` = ?, `Min_Turns` = ?, `Max_Turns` = ?, `Recoil` = ?, `Drain` = ?, `Healing` = ?, `Stat_Chance` = ?
        WHERE `ID` = ?
        LIMIT 1
      ");
      $Update_Move_Data->execute([
        $Name, $Class_Name, $Accuracy, $Power, $Priority, $PP, $Damage_Type, $Move_Type, $Category, $Ailment, $Flinch_Chance, $Crit_Chance, $Effect_Chance, $Ailment_Chance, $HP_Boost, $Attack_Boost, $Defense_Boost, $Sp_Attack_Boost, $Sp_Defense_Boost, $Speed_Boost, $Accuracy_Boost, $Evasion_Boost, $Min_Hits, $Max_Hits, $Min_Turns, $Max_Turns, $Recoil, $Drain, $Healing, $Stat_Chance,
        $Move_ID
      ]);

      $Update_Move_Flags = $PDO->prepare("
        UPDATE `moves_flags`
        SET `authentic` = ?, `bite` = ?, `bullet` = ?, `charge` = ?, `contact` = ?, `dance` = ?, `defrost` = ?, `distance` = ?, `gravity` = ?, `heal` = ?, `mirror` = ?, `mystery` = ?, `nonsky` = ?, `powder` = ?, `protect` = ?, `pulse` = ?, `punch` = ?, `recharge` = ?, `reflectable` = ?, `snatch` = ?, `sound` = ?
        WHERE `ID` = ?
        LIMIT 1
      ");
      $Update_Move_Flags->execute([
        $authentic, $bite, $bullet, $charge, $contact, $dance, $defrost, $distance, $gravity, $heal, $mirror, $mystery, $nonsky, $powder, $protect, $pulse, $punch, $recharge, $reflectable, $snatch, $sound,
        $Move_ID
      ]);

      $PDO->commit();
    }
    catch ( PDOException $e )
    {
      $PDO->rollBack();

      HandleError($e);
    }

    return [
      'Success' => true,
      'Message' => 'You have successfully updated the database entries for this move.'
    ];
  }
