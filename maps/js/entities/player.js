class Player_Entity
{
  constructor(Sprite, Render_Instance, GE_Instance)
  {
    this.Sprite = Sprite;
    this.Render_Instance = Render_Instance;
    this.GE_Instance = GE_Instance;
  }

  Update(Time, Delta, GridEngine)
  {
    this.Facing_Direction = this.GetFacingDirection();

    if (MapGame.Keys.left.isDown)
    {
      GridEngine.move('character', 'left');
      this.PlayAnimation('walk-left');
      this.Sprite.flipX = false;
    }
    else if (MapGame.Keys.right.isDown)
    {
      GridEngine.move('character', 'right');
      this.PlayAnimation('walk-right');
      this.Sprite.flipX = true;
    }
    else if (MapGame.Keys.up.isDown)
    {
      GridEngine.move('character', 'up');
      this.PlayAnimation('walk-up');
    }
    else if (MapGame.Keys.down.isDown)
    {
      GridEngine.move('character', 'down');
      this.PlayAnimation('walk-down');
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
