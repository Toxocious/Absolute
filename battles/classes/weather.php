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
      if ( !isset($Weather_Data) )
        return false;

      if
      (
        $Weather_Name == 'Rain' &&
        !empty($this->Weather) &&
        in_array($this->Weather->Name, ['Strong Winds', 'Rain', 'Heavy Rain', 'Extremely Harsh Sunlight'])
      )
        return false;

      if
      (
        $Weather_Name == 'Harsh Sunlight' &&
        !empty($this->Weather) &&
        in_array($this->Weather->Name, ['Strong Winds', 'Heavy Rain', 'Harsh Sunlight', 'Extremely Harsh Sunlight'])
      )
        return false;

      $this->Name = $Weather_Name;
      $this->Turns_Left = $Turn_Count;
      $this->Dialogue = $Weather_Data['Text'];

      foreach (['Ally', 'Foe'] as $Side)
      {
        $Active_Pokemon = $_SESSION['Battle'][$Side]->Active;

        switch ($this->Name)
        {
          case 'Harsh Sunlight':
          case 'Extremely Harsh Sunlight':
            if ( $Active_Pokemon->Ability == 'Chlorophyll' )
              $Active_Pokemon->Stats['Speed'] *= 2;
            break;

          case 'Sandstorm':
            if ( $Active_Pokemon->HasTyping(['Rock']) )
              $Active_Pokemon->Stats['Sp_Defense'] *= 1.5;
            break;
        }
      }

      $_SESSION['Battle']['Weather'] = $this;
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
        $Active_Pokemon = $_SESSION['Battle'][$Side]->Active;

        switch ($this->Name)
        {
          case 'Harsh Sunlight':
          case 'Extremely Harsh Sunlight':
            if ( $Active_Pokemon->Ability == 'Chlorophyll' )
              $Active_Pokemon->Stats['Speed'] /= 2;
            break;

          case 'Sandstorm':
            if ( $Active_Pokemon->HasTyping(['Rock']) )
              $Active_Pokemon->Stats['Sp_Defense'] /= 1.5;
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
            'Text' => 'The land has become desolate!<br />',
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
    public function WeatherList()
    {
      return [
        'Clear Skies' => [
          'Text' => ''
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
