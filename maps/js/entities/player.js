class Player_Entity
{
  constructor(Sprite, Render_Instance, GE_Instance)
  {
    this.Sprite = Sprite;
    this.Render_Instance = Render_Instance;
    this.GE_Instance = GE_Instance;

    this.Update_Timer = this.Render_Instance.time.addEvent({
      callback: this.UpdateLoop,
      callbackScope: this,
      delay: 3000,
      loop: true
    });
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

  /**
   * Handle interacting with whatever is in front of the player.
   */
  Interact()
  {
    let x = Math.round(this.Sprite.body.position.x) / 16;
    let y = Math.round(this.Sprite.body.position.y) / 16 + 1;
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

    const Tile_Info = new Tile(x, y, z);

    Tile_Info.GetTileInfo();
  }

  /**
   * Sync the player position to the database.
   */
  UpdateLoop()
  {
    MapGame.Network.SendRequest({
      Action: 'Position',
      x: Math.round(this.Sprite.body.position.x) / 16,
      y: Math.round(this.Sprite.body.position.y) / 16,
      z: this.GetCurrentLayer(),
    }, 'POST');
  }

  /**
   * Get the direction the player is facing.
   */
  GetFacingDirection()
  {
    return this.GE_Instance.gridCharacters.entries().next().value[1].facingDirection;
  }

  /**
   * Fetch the player's current layer.
   */
  GetCurrentLayer()
  {
    return this.GE_Instance.gridCharacters.entries().next().value[1]._tilePos.layer.replace('Layer_', '');
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
