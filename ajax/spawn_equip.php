<?php
  require '../session.php';

  if ( isset($_POST['equip_id']) )
  {
    $sqlQuery = '';

    $fetch_stats = mysqli_query($con, "SELECT * FROM `equip_rings` WHERE `ID` = '" . $_POST['equip_id'] . "'");
    $fetch_stats2 = mysqli_query($con, "SELECT * FROM `equip_rings` WHERE `ID` = '" . $_POST['equip_id'] . "'");
    $fetch_columns = mysqli_num_fields($fetch_stats);
    $fetch_columns2 = mysqli_num_fields($fetch_stats);

    $len = count($fetch_stats);
    $len2 = count($fetch_stats2);

    $sqlQuery .= "INSERT INTO `equips` (`Owner_ID`,`Item_Table`,";

    for ( $i = 0; $i < $fetch_columns; $i++ )
    {
      $field_name = mysqli_field_name($fetch_stats, $i);
      while ( $row = mysqli_fetch_assoc($fetch_stats) )
      {
        foreach( $row as $fetch_columns=>$value )
        {
          if ( $value !== null && $fetch_columns !== 'Auto' )
          {
            $sqlQuery .= "`{$fetch_columns}`,";
          }
        }
      }
    }

    $sqlQuery .= ") VALUES ('{$User_Data['id']}', 'equip_rings', ";

    for ( $i = 0; $i < $fetch_columns2; $i++ )
    {
      $field_name2 = mysqli_field_name($fetch_stats2, $i);
      while ( $row2 = mysqli_fetch_assoc($fetch_stats2) )
      {
        foreach( $row2 as $fetch_columns2=>$value2 )
        {
          if ( $value2 !== null && $fetch_columns2 !== 'Auto' )
          {
            $sqlQuery .= "'{$value2}',";
          }
        }
      }
    }

    $sqlQuery .= ")";

    $replace = str_replace("',)", "')", $sqlQuery);
    $replace = str_replace("`,)", "`)", $replace);

    echo "mysqli_query(con, \"$replace\");";
    mysqli_query($con, "$replace");
  }

  function mysqli_field_name($result, $field_offset)
  {
    $properties = mysqli_fetch_field_direct($result, $field_offset);
    return is_object($properties) ? $properties->name : null;
  }