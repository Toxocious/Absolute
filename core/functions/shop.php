<?php
  /**
   * Parse JSON into a workable array.
   * @param JSON
   */
  function FetchPriceList($JSON)
  {
    // Turn the JSON into an array.
    $Price_List = json_decode($JSON, true);

    // Return the maps.
    return $Price_List;
  }