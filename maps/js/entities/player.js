class Player_Entity
{
  constructor(Sprite, Render_Instance, GE_Instance)
  {
    this.Sprite = Sprite;
    this.Render_Instance = Render_Instance;
    this.GE_Instance = GE_Instance;

    this.Steps_Till_Encounter = 21;
  }

  Update(Time, Delta, GridEngine)
  {
    this.Facing_Direction = this.GetFacingDirection();

    if (MapGame.Keys.left.isDown)
    {
      if ( !MapGame.Player.In_Encounter && this.Steps_Till_Encounter !== 0 )
      {
        GridEngine.move('character', 'left');
        this.PlayAnimation('walk-left');
        this.Sprite.flipX = false;
      }
    }
    else if (MapGame.Keys.right.isDown)
    {
      if ( !MapGame.Player.In_Encounter && this.Steps_Till_Encounter !== 0 )
      {
        GridEngine.move('character', 'right');
        this.PlayAnimation('walk-right');
        this.Sprite.flipX = true;
      }
    }
    else if (MapGame.Keys.up.isDown)
    {
      if ( !MapGame.Player.In_Encounter && this.Steps_Till_Encounter !== 0 )
      {
        GridEngine.move('character', 'up');
        this.PlayAnimation('walk-up');
      }
    }
    else if (MapGame.Keys.down.isDown)
    {
      if ( !MapGame.Player.In_Encounter && this.Steps_Till_Encounter !== 0 )
      {
        GridEngine.move('character', 'down');
        this.PlayAnimation('walk-down');
      }
    }
    else if ( MapGame.Keys.space.isDown )
    {
      this.Interact();
    }
  }

  /**
   * Handle interacting with whatever is in front of the player.
   */
  Interact()
  {
    let x = Math.round(this.Sprite.body.position.x / 16);
    let y = Math.round(this.Sprite.body.position.y / 16) + 1;
    let z = this.GetCurrentLayer();

    switch ( this.Facing_Direction )
    {
      case 'up':
        y--;
        break;

      case 'down':
        y++;
        break;

      case 'left':
        x--;
        break;

      case 'right':
        x++;
        break;
    }

    MapGame.Network.SendRequest({
      Action: 'Interact',
      x: x,
      y: y,
      z: z,
    }, 'POST').then((data) => {
      if ( data === 'true' )
      {
        const Get_Tile = new TileInfo(x, y, z);
        const Tile_Info = Get_Tile.GetTileInfo();

        if ( typeof Tile_Info.Objects !== 'undefined' )
        {
          const Tile_Object = Tile_Info.Objects;
          Tile_Object.Interact();
        }
      }
    });
  }

  /**
   * Handle events that may need to happen when the player moves to a new tile.
   *  - Position Update
   *  - Encounters
   */
  ProcessMovement()
  {
    if ( MapGame.Player.In_Encounter || this.Steps_Till_Encounter === 0 )
      return;

    if ( MapGame.Player.In_Dialogue )
    {
      MapGame.Player.In_Dialogue = false;
      document.getElementById('map_dialogue').innerHTML = `You wander around aimlessly.`;
    }

    const x = Math.round(this.Sprite.body.position.x / 16);
    const y = Math.round(this.Sprite.body.position.y / 16);
    const z = this.GetCurrentLayer();

    const Get_Tile = new TileInfo(x, y, z);
    const Tile_Info = Get_Tile.GetTileInfo();

    let Encounter_Tile = false;
    if ( typeof Tile_Info.Objects !== 'undefined' )
      if ( Tile_Info.Objects.type === 'encounter' )
        Encounter_Tile = true;

    MapGame.Network.SendRequest({
      Action: 'Movement',
      Encounter_Tile: Encounter_Tile,
      x: x,
      y: y,
      z: z,
    }, 'POST').then((data) => {
      data = JSON.parse(data);
      document.getElementById('map_steps_until_encounter').innerText = `${data.Next_Encounter} Steps`;

      if ( data.Next_Encounter < 0 )
        data.Next_Encounter = 0;

      this.Steps_Till_Encounter = data.Next_Encounter;

      if ( this.Steps_Till_Encounter === 0 )
        this.CheckForEncounter(Tile_Info);
    });
  }

  /**
   * Check for a wild encounter.
   */
  CheckForEncounter(Tile_Info)
  {
    if ( this.Steps_Till_Encounter !== 0 || MapGame.Player.In_Encounter )
      return;

    MapGame.Player.In_Encounter = true;

    MapGame.Encounter = new Encounter(Tile_Info.Objects.Name, Tile_Info.Objects.Grid_Engine_ID, Tile_Info.Objects.properties, Tile_Info.Objects.type, Tile_Info.Objects.coords, Tile_Info.Objects.Render_Instance);
    MapGame.Encounter.DisplayEncounter(this.Steps_Till_Encounter, MapGame.Player.In_Encounter);
  }

  /**
   * Run away from the active encounter.
   */
  RunFromEncounter()
  {
    if ( !MapGame.Player.In_Encounter )
      return;

    MapGame.Network.SendRequest({
      Action: 'Run',
    }, 'POST').then((Run_Data) => {
      Run_Data = JSON.parse(Run_Data);

      MapGame.Player.In_Encounter = false;
      this.Steps_Till_Encounter = Run_Data.Steps_Until_Next_Encounter;

      document.getElementById('map_dialogue').innerHTML = `You ran away from the wild Pok&eacute;mon.`;
    });
  }

  /**
   * Get the direction the player is facing.
   */
  GetFacingDirection()
  {
    return this.GE_Instance.getFacingDirection('character');
  }

  /**
   * Fetch the player's current layer.
   */
  GetCurrentLayer()
  {
    return parseInt(this.GE_Instance.gridCharacters.entries().next().value[1]._tilePos.layer.replace('Layer_', ''));
  }

  /**
   * Play the specified animation.
   */
  PlayAnimation(Animation_Name)
  {
    this.Sprite.anims.play(Animation_Name, true);
  }

  /**
   * Create animations.
   */
  CreateAnimations()
  {
    const Anims = this.Sprite.anims;
    Anims.create({
      key: 'walk-left',
      frames: Anims.generateFrameNames('character', {
        start: 0,
        end: 3,
        prefix: 'atlas-',
        suffix: '.png',
      }),
      frameRate: 10,
      repeat: false,
    });
    Anims.create({
      key: 'walk-right',
      frames: Anims.generateFrameNames('character', {
        start: 0,
        end: 3,
        prefix: 'atlas-',
        suffix: '.png',
      }),
      flipped: true,
      frameRate: 10,
      repeat: false,
    });
    Anims.create({
      key: 'walk-down',
      frames: Anims.generateFrameNames('character', {
        start: 8,
        end: 11,
        prefix: 'atlas-',
        suffix: '.png',
      }),
      frameRate: 10,
      repeat: false,
    });
    Anims.create({
      key: 'walk-up',
      frames: Anims.generateFrameNames('character', {
        start: 4,
        end: 7,
        prefix: 'atlas-',
        suffix: '.png',
      }),
      frameRate: 10,
      repeat: false,
    });
    Anims.create({
      key: 'idle-down',
      frames: Anims.generateFrameNames('character', {
        start: 8,
        end: 8,
        prefix: 'atlas-',
        suffix: '.png',
      }),
      frameRate: 10,
      repeat: false,
    });
  }
}
