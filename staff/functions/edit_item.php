<?php
  /**
   * Show an HTML dropdown of all possible item entries.
   */
  function ShowItemDropdown()
  {
    global $PDO;

    try
    {
      $Get_Item_Entries = $PDO->prepare("
        SELECT *
        FROM `item_dex`
        ORDER BY `Item_ID` ASC
      ");
      $Get_Item_Entries->execute([ ]);
      $Get_Item_Entries->setFetchMode(PDO::FETCH_ASSOC);
      $Item_Entries = $Get_Item_Entries->fetchAll();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    if ( empty($Item_Entries) )
    {
      return "
        <select name='Item_entries'>
          <option>There are no item entries</option>
        </select>
      ";
    }

    $Dropdown_Entries = '';
    foreach ( $Item_Entries as $Item )
    {
      $Dropdown_Entries .= "
        <option value='{$Item['Item_ID']}'>
          {$Item['Item_Name']}
        </option>
      ";
    }

    return "
      <select name='item_entries' onchange='ShowItemEntry();'>
        <option>Select An Item</option>
        {$Dropdown_Entries}
      </select>
    ";
  }

  /**
   * Show an HTML table allowing for editing of the specified item's database fields.
   *
   * @param $Item_ID
   */
  function ShowItemEditTable
  (
    $Item_ID
  )
  {
    global $PDO;

    try
    {
      $Get_Item_Entry_Data = $PDO->prepare("
        SELECT *
        FROM `item_dex`
        WHERE `Item_ID` = ?
        LIMIT 1
      ");
      $Get_Item_Entry_Data->execute([ $Item_ID ]);
      $Get_Item_Entry_Data->setFetchMode(PDO::FETCH_ASSOC);
      $Item_Entry_Data = $Get_Item_Entry_Data->fetch();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    $Config_Options = [
      'Item Data' => [
        'Description' => [
          'Field_Name' => 'Item_Description',
          'Field_Type' => 'Textarea'
        ],
      ],
      'Battle Data' => [
        'Can Be Stolen' => [
          'Field_Name' => 'Can_Take_Item',
          'Field_Type' => 'Checkbox'
        ],
        'Natural Gift Power' => [
            'Field_Name' => 'Natural_Gift_Power',
            'Field_Type' => 'Number'
        ],
        'Natural Gift Type' => [
          'Field_Name' => 'Natural_Gift_Type',
          'Field_Type' => 'Dropdown'
        ],
        'Fling Power' => [
          'Field_Name' => 'Fling_Power',
          'Field_Type' => 'Number',
        ],
      ],
      'Stat Boosts' => [
        'Defense' => [
          'Field_Name' => 'Defense_Boost',
          'Field_Type' => 'Number'
        ],
        'Sp. Attack' => [
          'Field_Name' => 'Sp_Attack_Boost',
          'Field_Type' => 'Number'
        ],
        'Sp. Defense' => [
          'Field_Name' => 'Sp_Defense_Boost',
          'Field_Type' => 'Number'
        ],
        'Speed' => [
          'Field_Name' => 'Speed_Boost',
          'Field_Type' => 'Number'
        ],
        'Accuracy' => [
          'Field_Name' => 'Accuracy_Boost',
          'Field_Type' => 'Number'
        ],
        'Evasion' => [
          'Field_Name' => 'Evasion_Boost',
          'Field_Type' => 'Number'
        ],
      ],
    ];

    $Config_Table_Rows = '';

    foreach ( $Config_Options as $Option_Category => $Sub_Children )
    {
      $Config_Row_Text = "
        <thead>
          <tr>
            <th colspan='30' style='width: 100%;'>
              <b>{$Option_Category}</b>
            </th>
          </tr>
        </thead>
      ";

      $Child_Options_Text = '';

      $Column_Span = 28 / count($Sub_Children);
      $Width = 100 / count($Sub_Children);

      foreach ( $Sub_Children as $Child_Option => $Child_Fields )
      {
        switch ( $Child_Fields['Field_Type'] )
        {
          case 'Checkbox':
            $Input_HTML = "
              <input
                type='checkbox'
                name='{$Child_Fields['Field_Name']}'
                value='{$Item_Entry_Data[$Child_Fields['Field_Name']]}'
                " . ($Item_Entry_Data[$Child_Fields['Field_Name']] ? 'checked' : '') . "
              />
            ";
            break;

          case 'Dropdown':
            $Options = [ 'None', 'Normal', 'Water', 'Fire', 'Grass', 'Electric', 'Ground', 'Flying', 'Fighting', 'Psychic', 'Dark', 'Ghost', 'Bug', 'Rock', 'Ice', 'Steel', 'Dragon', 'Poison', 'Fairy' ];

            $Option_Text = '';
            foreach ( $Options as $Option )
            {
              $Option_Text .= "
                <option value='{$Option}' " . ($Item_Entry_Data[$Child_Fields['Field_Name']] == $Option ? 'selected' : '') . ">
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
            $Input_HTML = "
              <input
                type='number'
                name='{$Child_Fields['Field_Name']}'
                value='{$Item_Entry_Data[$Child_Fields['Field_Name']]}' style='width: 70px;'
              />
            ";
            break;

          case 'Textarea':
            $Input_HTML = "<textarea cols='80' rows='5'>{$Item_Entry_Data[$Child_Fields['Field_Name']]}</textarea>";
            break;
        }

        $Child_Options_Text .= "
          <td colspan='{$Column_Span}' style='width: {$Width}%;'>
            <b>{$Child_Option}</b>
            <br />
            {$Input_HTML}
          </td>
        ";
      }

      $Config_Table_Rows .= "
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
            <th colspan='28'>
              Editing Item Entry #{$Item_ID}
            </th>
          </tr>
        </thead>

        <tbody>
          <tr>
            <td colspan='28'>
              <img src='" . DOMAIN_SPRITES . "/Items/{$Item_Entry_Data['Item_Name']}.png' />
              <br />
              <b>{$Item_Entry_Data['Item_Name']}</b>
            </td>
          </tr>
        </tbody>

        {$Config_Table_Rows}

        <tbody>
          <tr>
            <td colspan='28' style='padding: 10px;'>
              <button onclick='UpdateItemEntry({$Item_ID});'>
                Update Item Entry
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    ";
  }
