
const Render = new Phaser.Class({
  Extends: Phaser.Scene,

  initialize: function()
  {
    Phaser.Scene.call(this, { "key": "Render" });
  },

  init: function()
  {
    MapGame.Keys = this.input.keyboard.addKeys({
      up: 'W',
      left: 'A',
      down: 'S',
      right: 'D'
    });

    MapGame.Player = null;
    MapGame.Tile_Size = 16;
    MapGame.Network = new Network();

    let Width = this.cameras.main.width;
    let Height = this.cameras.main.height;

    var Progress_Bar = this.add.graphics();
    var Progress_Box = this.add.graphics();
    Progress_Box.fillStyle(0x222222, 0.8);
    Progress_Box.fillRect(20, 95, 200, 40);

    let Loading_Text = this.make.text({
      x: Width / 2,
      y: Height / 2 - 50,
      text: 'Loading...',
      style: {
        font: '20px monospace',
        fill: '#ffffff'
      }
    });
    Loading_Text.setOrigin(0.5, 0.5);

    let Percent_Text = this.make.text({
      x: Width / 2,
      y: Height / 2 - 5,
      text: '0%',
      style: {
        font: '18px monospace',
        fill: '#ffffff'
      }
    });
    Percent_Text.setOrigin(0.5, 0.5);

    let Asset_Text = this.make.text({
      x: Width / 2,
      y: Height / 2 + 50,
      text: '',
      style: {
        font: '18px monospace',
        fill: '#ffffff'
      }
    });
    Asset_Text.setOrigin(0.5, 0.5);

    this.load.on('progress', (value) =>
    {
      Percent_Text.setText(parseInt(value * 100) + '%');
      Progress_Bar.clear();
      Progress_Bar.fillStyle(0xffffff, 1);
      Progress_Bar.fillRect(30, 105, 180 * value, 20);
    });

    this.load.on('fileprogress', (file) =>
    {
      Asset_Text.setText('Loading Asset:\n - ' + file.key);
    });

    this.load.on('complete', () =>
    {
      Progress_Bar.destroy();
      Progress_Box.destroy();
      Loading_Text.destroy();
      Percent_Text.destroy();
      Asset_Text.destroy();
    });
  },

  preload: function()
  {
    /**
     * Load necessary assets.
     */
    console.log('[Network Instance]', MapGame.Network);
    MapGame.Network.SendRequest('Load').then((Assets) =>
    {
      Assets = JSON.parse(Assets);

      /**
       * Load the map file.
       */
      this.Map_Name = Assets.Map_Name;
      this.load.setPath('/maps/maps/');
      this.load.tilemapTiledJSON(Assets.Map_Name, `${Assets.Map_Name}.json`);

      /**
       * Load tileset images.
       */
      this.load.setPath('/maps/tilesets/images/');
      for ( const Tileset of Assets.Tilesets )
      {
        this.load.image(`${Tileset}_tiles`, `${Tileset}.png`);
      }

      /**
       * Load the player's NPC spritesheet.
       */
      this.load.setPath('/maps/assets/npcs/animations/');
      switch ( Assets.Character )
      {
        case 'Female':
          this.load.multiatlas('character', 'user_female/atlas.json', '/maps/assets/npcs/animations/user_female');
          MapGame.Atlas_Dir = '/maps/assets/npcs/animations/user_female';
          break;
        case 'Male':
          this.load.multiatlas('character', 'user_male/atlas.json', '/maps/assets/npcs/animations/user_male');
          MapGame.Atlas_Dir = '/maps/assets/npcs/animations/user_male';
          break;
        case 'Ungendered':
          this.load.multiatlas('character', 'user_ungendered/atlas.json', '/maps/assets/npcs/animations/user_ungendered');
          MapGame.Atlas_Dir = '/maps/assets/npcs/animations/user_ungendered';
          break;
      }

      /**
       * Store the player's position.
       */
      this.Player_Position = Assets.Position;
    });

    this.load.setPath('/maps/assets/npcs/');
    for ( let i = 1; i <= 84; i++ )
    {
      if ( i === 24 ) continue;

      this.load.spritesheet(`npc_${i}`, `${i}.png`, { frameWidth: 48, frameHeight: 48 });
    }

    this.load.setPath('/maps/assets/weather/');
    this.load.image('weather', 'rain.png');

    /**
     * Load the player's map stats.
     */
    MapGame.Network.SendRequest('Stats').then((Stats) =>
    {
      Stats = JSON.parse(Stats);
      console.log('[Player Stats]', Stats);

      document.getElementById('map_name').innerText = Stats.Map_Name.replace('_', ' ').toLowerCase().split(' ').map(word => word.charAt(0).toUpperCase() + word.substring(1)).join(' ');
      document.getElementById('map_level').innerText = Stats.Map_Level;
      document.getElementById('map_exp_to_level').innerText = Stats.Map_Experience_To_Level.Exp.toLocaleString(undefined, {maximumFractionDigits: 0});
      document.getElementById('map_shiny_odds').innerText = `${Stats.Shiny_Odds.Text} (${Stats.Shiny_Odds.Percent.toLocaleString(undefined, {maximumFractionDigits: 4})}%)`;
      document.getElementById('map_steps_until_encounter').innerText = Stats.Next_Encounter;
      document.getElementById('map_exp_bar').setAttribute('style', `width: ${Stats.Map_Experience_To_Level.Percent}%`);
    });

    return true;
  },

  create: function()
  {
    // Make the map.
    const Map = this.make.tilemap({ key: this.Map_Name, tileWidth: 16, tileHeight: 16 });
    MapGame.Map = Map;
    console.log('[Map]', Map);

    // Render the tiles.
    const Tiles = this.RenderTiles(Map);
    console.log('[Tiles]', Tiles);

    // Render the layers.
    const Layers = this.RenderLayers(Map, Tiles);
    MapGame.Layers = Layers;
    console.log('[Layers]', Layers);

    // Set player sprite
    const Player_Sprite = this.physics.add.sprite(0, 0, "character");
    Player_Sprite.setDepth(1);
    Player_Sprite.setOrigin(0.5, 0.5);
    Player_Sprite.body.setSize(16, 16, true);

    // Create player animations.
    MapGame.Player = new Player_Entity(Player_Sprite, this);
    MapGame.Player.CreateAnimations();
    MapGame.Player.PlayAnimation('idle-down');
    console.log('[Player Sprite/Entity]', Player_Sprite, MapGame.Player);

    // Set the main camera to follow the player, and keep it in bounds.
    this.cameras.main.startFollow(Player_Sprite, true);
    this.cameras.main.setFollowOffset(-Player_Sprite.width / 2, -Player_Sprite.height / 2);
    this.cameras.main.setBounds(0, 0, Map.width * Map.tileWidth, Map.height * Map.tileHeiht);

    // Grid Engine init
    const gridEngineConfig = {
      characters: [
        {
          id: "character",
          sprite: Player_Sprite,
          startPosition: { x: this.Player_Position.x, y: this.Player_Position.y },
        },
      ],
    };
    this.gridEngine.create(Map, gridEngineConfig);
  },

  update: function(Time, Delta)
  {
    MapGame.Player.Update(Time, Delta, this.gridEngine);
  },

  /**
   * Render all layers of the map.
   */
  RenderLayers: function(Map, Tiles)
  {
    let Layers = [];
    for ( const Layer in Map.layers )
    {
      const Layer_Name = Map.layers[Layer].name;

      let Create_Layer = Map.createLayer(Layer_Name, Tiles, 0, 0);
      Create_Layer.setDepth(Layer);
      Layers.push(Create_Layer);
    }
    return Layers;
  },

  /**
   * Render necessary tilesets.
   */
  RenderTiles: function(Map)
  {
    let Tiles = [];
    for ( let Tileset of Map.tilesets )
    {
      Tiles.push(Map.addTilesetImage(Tileset.name, `${Tileset.name}_tiles`));
    }
    return Tiles;
  },

  /**
   * Find map objects.
   */
  FindObjects: function(Type, Map, Layer)
  {
    console.log('[Finding Objects]', Type, Map, Layer);

    let Objects = [];

    Map.objects[Layer].forEach((Element) =>
    {
      console.log('[Object Found]', Element);
    });

    return Objects;
  },

  /**
   * Create objects.
   */
  CreateObjects: function()
  {
    console.log('[Creating Objects]');

    this.Objects = this.game.add.group();
    this.Objects.enableBody = true;

    let Find_Objects = this.FindObjects('null', this.map, 'objectsLayer');
    console.log(Find_Objects);
  },

  /**
   * Create a sprite from an object.
   */
  CreateFromTiledObject: function(Element, Group)
  {
    console.log('[Creating Object Sprite]', Element, Group);
  }
});
