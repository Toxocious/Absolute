<?php
  class Weather
  {
    public $Name = null;
    public $Turns_Left = null;
    public $Dialogue = null;

    public function __construct
    (
      string $Weather_Name,
      int $Turn_Count = 5
    )
    {
      $Weather_Data = $this->WeatherList()[$Weather_Name];

      $this->Name = $Weather_Name;
      $this->Turns_Left = $Turn_Count;
      $this->Dialogue = $Weather_Data['Text'];

      foreach (['Ally', 'Foe'] as $Side)
      {
        $Active_Pokemon = $_SESSION['EvoChroniclesRPG']['Battle'][$Side]->Active;

        switch ($this->Name)
        {
          case 'Hail':
            if ( $Active_Pokemon->Ability->Name == 'Slush Rush' )
              $Active_Pokemon->Stats['Speed']->Current_Value *= 2;
            break;

          case 'Harsh Sunlight':
          case 'Extremely Harsh Sunlight':
            if ( $Active_Pokemon->Ability->Name == 'Chlorophyll' )
              $Active_Pokemon->Stats['Speed']->Current_Value *= 2;

            if ( $Active_Pokemon->Ability->Name == 'Flower Gift' )
            {
              $Active_Pokemon->Stats['Attack']->Current_Value *= 1.5;
              $Active_Pokemon->Stats['Sp_Defense']->Current_Value *= 1.5;
            }

            if ( $Active_Pokemon->Ability->Name == 'Solar Power' )
              $Active_Pokemon->Stats['Sp_Attack']->Current_Value *= 1.5;
            break;

          case 'Rain':
          case 'Heavy Rain':
            if ( $Active_Pokemon->Ability->Name == 'Swift Swim' )
              $Active_Pokemon->Stats['Speed']->Current_Value *= 2;
            break;

          case 'Sandstorm':
            if ( $Active_Pokemon->HasTyping(['Rock']) )
              $Active_Pokemon->Stats['Sp_Defense']->Current_Value *= 1.5;

            if ( $Active_Pokemon->Ability->Name == 'Sand Rush' )
              $Active_Pokemon->Stats['Speed']->Current_Value *= 2;
            break;
        }
      }

      return $this;
    }

    /**
     * Decrement how many turns remain.
     */
    public function TickWeather()
    {
      if ( $this->Turns_Left > 0 )
        $this->Turns_Left--;

      return $this;
    }

    /**
     * End the current weather.
     */
    public function EndWeather()
    {
      foreach (['Ally', 'Foe'] as $Side)
      {
        $Active_Pokemon = $_SESSION['EvoChroniclesRPG']['Battle'][$Side]->Active;

        switch ($this->Name)
        {
          case 'Hail':
            if ( $Active_Pokemon->Ability->Name == 'Slush Rush' )
              $Active_Pokemon->Stats['Speed']->Current_Value /= 2;
            break;

          case 'Harsh Sunlight':
          case 'Extremely Harsh Sunlight':
            if ( $Active_Pokemon->Ability->Name == 'Chlorophyll' )
              $Active_Pokemon->Stats['Speed']->Current_Value /= 2;

            if ( $Active_Pokemon->Ability->Name == 'Flower Gift' )
            {
              $Active_Pokemon->Stats['Attack']->Current_Value /= 1.5;
              $Active_Pokemon->Stats['Sp_Defense']->Current_Value /= 1.5;
            }

            if ( $Active_Pokemon->Ability->Name == 'Solar Power' )
              $Active_Pokemon->Stats['Sp_Attack']->Current_Value /= 1.5;
            break;

          case 'Rain':
          case 'Heavy Rain':
            if ( $Active_Pokemon->Ability->Name == 'Swift Swim' )
              $Active_Pokemon->Stats['Speed']->Current_Value /= 2;
            break;

          case 'Sandstorm':
            if ( $Active_Pokemon->HasTyping(['Rock']) )
              $Active_Pokemon->Stats['Sp_Defense']->Current_Value /= 1.5;

            if ( $Active_Pokemon->Ability->Name == 'Sand Rush' )
              $Active_Pokemon->Stats['Speed']->Current_Value /= 2;
            break;
        }
      }

      switch ($this->Name)
      {
        case 'Clear Skies':
          return [
            'Text' => ''
          ];

        case 'Desolate Land':
          return [
            'Text' => 'The land is no longer desolate!<br />',
          ];

        case 'Fog':
          return [
            'Text' => 'The fog has been blown away!<br />',
          ];

        case 'Hail':
          return [
            'Text' => 'The hail stopped.<br />'
          ];

        case 'Rain':
          return [
            'Text' => 'The rain stopped.<br />'
          ];

        case 'Heavy Rain':
          return [
            'Text' => 'The heavy rain has lifted!<br />'
          ];

        case 'Sandstorm':
          return [
            'Text' => 'The sandstorm subsided.<br />'
          ];

        case 'Harsh Sunlight':
          return [
            'Text' => 'The harsh sunlight faded.<br />'
          ];

        case 'Extremely Harsh Sunlight':
          return [
            'Text' => 'The harsh sunlight faded.<br />'
          ];

        case 'Shadowy Aura':
          return [
            'Text' => 'The shadowy aura faded away!<br />'
          ];

        case 'Strong Wings':
          return [
            'Text' => 'The mysterious strong winds have dissipated!<br />'
          ];
      }
    }

    /**
     * All possible field effects.
     */
    public static function WeatherList()
    {
      return [
        'Clear Skies' => [
          'Text' => ''
        ],
        'Desolate Land' => [
          'Text' => 'The land has become desolate!',
        ],
        'Fog' => [
          'Text' => 'The fog is deep...',
        ],
        'Hail' => [
          'Text' => 'It started to hail!'
        ],
        'Rain' => [
          'Text' => 'It started to rain!'
        ],
        'Heavy Rain' => [
          'Text' => 'A heavy rain begain to fall!'
        ],
        'Sandstorm' => [
          'Text' => 'A sandstorm kicked up!'
        ],
        'Harsh Sunlight' => [
          'Text' => 'The sunlight turned harsh!'
        ],
        'Extremely Harsh Sunlight' => [
          'Text' => 'The sunlight turned extremely harsh!'
        ],
        'Shadowy Aura' => [
          'Text' => 'A shadowy aura filled the sky!'
        ],
        'Strong Winds' => [
          'Text' => 'Mysterious strong winds are protecting Flying-type Pok&eacute;mon!'
        ],
      ];
    }
  }
