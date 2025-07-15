class Weather
{
  construct()
  {
    this.Weather = null;
  }

  ChangeWeather(Weather_Type)
  {
    switch ( Weather_Type )
    {
      case 'Rain':
        this.Weather = this.add.particles('weather');
        this.Weather.createEmitter({
          frame: 'blue',
          x: { min: 0, max: 800 },
          y: 0 ,
          lifespan: { min: 100, max: 400 },
          speedY: 1500,
          scaleY: { min: 1, max:4 },
          scaleX: .01,
          quantity: { min: 5, max: 15 },
          blendMode: 'LIGHTEN',
        });
      break;

    case 'Snow':
      this.Weather = this.add.particles('weather');
      this.Weather.createEmitter({
        speedY: { min: 15, max: 40 },
        frequency: 2000,
        gravityY: 0,
        scale: 0.1,
        quantity: 1,
        lifespan: { min: 15000, max: 20000 },
        emitZone: { source: new Phaser.Geom.Line(-20, -100, 820, -100 )}
      });
      break;

    default:
        this.Weather = null;
        break;
    }
  }

  UnsetWeather()
  {
    this.Weather = null;
  }
}
