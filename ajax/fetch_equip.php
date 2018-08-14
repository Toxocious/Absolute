<?php
  require '../session.php';

  if ( isset($_POST['equip_id']) )
  {
    $fetch_stats = mysqli_query($con, "SELECT * FROM equip_rings WHERE ID = '" . $_POST['equip_id'] . "'"); 
    $fetch_columns = mysqli_num_fields($fetch_stats); 
    for ( $i = 1; $i < $fetch_columns; $i++ )
    { 
      //read field name
      $field_name = mysqli_field_name($fetch_stats, $i);
      while ( $row = mysqli_fetch_assoc($fetch_stats) )
      {
        foreach( $row as $fetch_columns=>$value )
        {
          if ( $value !== null && $fetch_columns !== 'Auto' )
          {
            echo "$fetch_columns = $value<br />";
          }
        }
      }
    }  
  }

  function mysqli_field_name($result, $field_offset)
  {
    $properties = mysqli_fetch_field_direct($result, $field_offset);
    return is_object($properties) ? $properties->name : null;
  }