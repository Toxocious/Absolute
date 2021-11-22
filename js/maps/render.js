
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
      this.load.setPath('/maps/maps/');
      for ( const Tileset of Assets.Tilesets )
      {
        this.load.image('tiles', `${Tileset}.png`);
      }

      /**
       * Load the player's NPC spritesheet.
       */
      this.load.setPath('/maps/assets/npcs/');
      switch ( Assets.Character )
      {
        case 'Female':
          this.load.spritesheet('character', 'user_female.png', { frameWidth: 48, frameHeight: 48 });
          break;
        case 'Male':
          this.load.spritesheet('character', 'user_male.png', { frameWidth: 48, frameHeight: 48 });
          break;
        case 'Ungendered':
          this.load.spritesheet('character', 'user_ungendered.png', { frameWidth: 48, frameHeight: 48 });
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

    this.load.setPath('/images/Assets/');
    this.load.image('logo', 'pokeball.png');

    return true;
  },

  create: function()
  {
    // Make the map.
    const Map = this.make.tilemap({ key: this.Map_Name });
    console.log('[Map]', Map);

    // Render the tiles.
    const Tiles = this.RenderTiles(Map);
    console.log('[Tiles]', Tiles);

    // Render the layers.
    const Layers = this.RenderLayers(Map, Tiles);
    console.log('[Layers]', Layers);

    // Set player sprite
    const Player_Sprite = this.physics.add.sprite(0, 0, "character");
    Player_Sprite.setOrigin(0.5, 0.5);
    Player_Sprite.body.setSize(16, 16, true);
    this.cameras.main.startFollow(Player_Sprite, true);
    this.cameras.main.setFollowOffset(-Player_Sprite.width / 2, -Player_Sprite.height / 2);
    MapGame.Player = new Player_Entity(Player_Sprite);

    // Grid Engine init
    const gridEngineConfig = {
      characters: [
        {
          id: "character",
          sprite: Player_Sprite,
          walkingAnimationMapping: 6,
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
    let Layers;
    for ( const Layer in Map.layers )
    {
      const Layer_Name = Map.layers[Layer].name;

      Layers = Map.createLayer(Layer_Name, Tiles);
      Layers.setDepth(Layer);
    }
    return Layers;
  },

  /**
   * Render necessary tilesets.
   */
  RenderTiles: function(Map)
  {
    let Tiles;
    for ( let Tileset of Map.tilesets )
    {
      Tiles = Map.addTilesetImage(Tileset.name, 'tiles');
    }

    return Tiles;
  },
  }
});
