<?php
  require_once '../../required/session.php';
  require_once '../../classes/shop.php';

  if ( isset($_GET['Object_ID']) )
    $Object_ID = Purify($_GET['Object_ID']);
  else
    $Object_ID = null;

  if ( isset($_GET['Object_Type']) )
    $Object_Type = Purify($_GET['Object_Type']);
  else
    $Object_Type = null;

  if ( !$Object_ID || !$Object_Type )
  {
    echo "
      <div class='error'>
        An error occurred while fetching object data.
      </div>
    ";
  }
  else if ( !in_array($Object_Type, ['Item', 'Pokemon']) )
  {
    echo "
      <div class='error'>
        An error occurred while fetching object data.
      </div>
    ";
  }
  else
  {
    $Object_Data = $Shop_Class->FetchObjectData($Object_ID, $Object_Type);

    if ( !$Object_Data )
    {
      echo "
        <div class='error'>
          An error occurred while fetching object data.
        </div>
      ";
    }
    else
    {
      if ( $Object_Data[0]['Remaining'] < 1 )
        echo "Out of stock!";
      else
        echo "In stock: " . number_format($Object_Data[0]['Remaining']);
    }
  }
