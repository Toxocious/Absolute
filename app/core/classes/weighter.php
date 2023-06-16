<?php
  class Weighter
  {
    protected $Objects = [];
    protected $Total_Weight = 0;

    public function AddObject
    (
      $Description,
      $Weight_Value
    )
    {
      $this->Total_Weight += $Weight_Value;

      $this->Objects[] = [
        'Description' => $Description,
        'Weight' => $Weight_Value,
        'Total_Weight' => $this->Total_Weight
      ];
    }

    public function GetObject()
    {
      if ( count($this->Objects) === 0 )
        return false;

      $Random_Weight = mt_rand(1, $this->Total_Weight);
      foreach ( $this->Objects as $Object )
        if ( $Random_Weight <= $Object['Total_Weight'] )
          return $Object['Description'];
    }
  }
