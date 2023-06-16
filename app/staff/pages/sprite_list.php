<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/auth.php';

  if ( !AuthorizeUser() )
  {
    echo "
      <div style='padding: 5px;'>
        You aren't authorized to be here.
      </div>
    ";

    exit;
  }

  $Pokemon_Categories = [
    '1' => 'Gen I',
    '2' => 'Gen II',
    '3' => 'Gen III',
    '4' => 'Gen IV',
    '5' => 'Gen V',
    '6' => 'Gen VI',
    '7' => 'Gen VII',
  ];

  $Item_Categories = [
    'Battle Item' => 'Battle Items',
    'Berries' => 'Berries',
    'General Item' => 'General Items',
    'Held Item' => 'Held Items',
    'Medicine' => 'Medicine',
  ];
?>

<div style='display: flex; flex-wrap: wrap;'>
  <div style='flex-basis: 100%; width: 100%;'>
    <h3>Sprite List</h3>
  </div>

  <div style='display: flex; flex-wrap: wrap; gap: 10px; align-content: center;'>
    <table class='border-gradient' style='width: 700px;'>
      <thead>
        <tr>
          <th colspan='7'>
            <b>Pok&eacute;mon</b>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <?php
            foreach ( $Pokemon_Categories as $Category_Key => $Category_Name )
            {
              echo "
                <td colspan='1'>
                  <a href='javascript:void(0);' style='font-size: 14px;' onclick='ShowSprites(\"Pokemon\", \"{$Category_Key}\");'>
                    {$Category_Name}
                  </a>
                </td>
              ";
            }
          ?>
        </tr>
      </tbody>
    </table>

    <table class='border-gradient' style='width: 700px;'>
      <thead>
        <tr>
          <th colspan='5'>
            <b>Items</b>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <?php
            foreach ( $Item_Categories as $Category_Key => $Category_Name )
            {
              echo "
                <td colspan='1' style='width: 20%;'>
                  <a href='javascript:void(0);' style='font-size: 14px;' onclick='ShowSprites(\"Items\", \"{$Category_Key}\");'>
                    {$Category_Name}
                  </a>
                </td>
              ";
            }
          ?>
        </tr>
      </tbody>
    </table>
  </div>

  <div id='Sprite_AJAX' style='width: 100%;'></div>
</div>
