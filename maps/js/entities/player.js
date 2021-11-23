class Player_Entity
{
  constructor(Sprite)
  {

  }

  Update(Time, Delta, GridEngine)
  {
    if (MapGame.Keys.left.isDown) {
      GridEngine.move("character", "left");
    } else if (MapGame.Keys.right.isDown) {
      GridEngine.move("character", "right");
    } else if (MapGame.Keys.up.isDown) {
      GridEngine.move("character", "up");
    } else if (MapGame.Keys.down.isDown) {
      GridEngine.move("character", "down");
    }

    //if (MapGame.Keys.left.isDown)
    //{
    //  this.CheckMove("left");
    //}
    //else if (MapGame.Keys.right.isDown)
    //{
    //  this.CheckMove("right");
    //}
    //else if (MapGame.Keys.up.isDown)
    //{
    //  this.CheckMove("up");
    //}
    //else if (MapGame.Keys.down.isDown)
    //{
    //  this.CheckMove("down");
    //}
  }

  CheckMove(Direction)
  {
    console.log('[Checking Movement]', Direction);
    console.log('[Checking Movement Layer]', MapGame.Layers);

    let Next_Tile;
    if ( Direction == 'left' ) Next_Tile = MapGame.Layers.getTileAtWorldXY(this.x - 16, this.y, true);
    else if ( Direction == 'right' ) Next_Tile = MapGame.Layers.getTileAtWorldXY(this.x + 16, this.y, true);
    else if ( Direction == 'up' ) Next_Tile = MapGame.Layers.getTileAtWorldXY(this.x, this.y - 16, true);
    else Next_Tile = MapGame.Layers.getTileAtWorldXY(this.x, this.y + 16, true);

    console.log('[Checking Movement Collision]', Next_Tile);
  }

  /**
   * Handle player input.
   */
  InputListener(Input, Layers)
  {
    Input.keyboard.on('keydown-A', (e) =>
    {
      var Tile_Info = Layers.getTileAtWorldXY(this.x - 16, this.y, true);
      if ( !Tile_Info || Tile_Info.properties.collision )
        return;

      this.play('walk_side');

      this.x -= 16;
      this.scaleX = 1;
    });

    Input.keyboard.on('keydown-D', (e) =>
    {
      var Tile_Info = Layers.getTileAtWorldXY(this.x + 16, this.y, true);
      if ( !Tile_Info || Tile_Info.properties.collision )
        return;

      this.play('walk_side');

      this.x += 16;
      this.scaleX = -1;
    });

    Input.keyboard.on('keydown-W', (e) =>
    {
      var Tile_Info = Layers.getTileAtWorldXY(this.x, this.y - 16, true);
      if ( !Tile_Info || Tile_Info.properties.collision )
        return;

      this.play('walk_up');

      this.y -= 16;
    });

    Input.keyboard.on('keydown-S', (e) =>
    {
      var Tile_Info = Layers.getTileAtWorldXY(this.x, this.y + 16, true);
      if ( !Tile_Info || Tile_Info.properties.collision )
        return;

      this.play('walk_down');

      this.y += 16;
    });
  }

  /**
   * Create animations.
   */
  CreateAnimations()
  {
    /*
    this.renderable.addAnimation("run_side", [13, 14, 15]);
    this.renderable.addAnimation("run_down", [21, 22, 23]);
    this.renderable.addAnimation("run_up", [17, 18, 19]);

    this.renderable.addAnimation("surf_side", [24, 25]);
    this.renderable.addAnimation("surf_up", [26, 27]);
    this.renderable.addAnimation("surf_down", [28, 29]);

    this.renderable.addAnimation("sliding_side", [0, 4, 12, 8]);
    this.renderable.addAnimation("sliding_up", [0, 4, 12, 8]);
    this.renderable.addAnimation("sliding_down", [0, 4, 12, 8]);
    */

    this.anims.create({
      key: "walk_side",
      frameRate: 7,
      frames: this.anims.generateFrameNumbers("character", { start: 1, end: 3 }),
      repeat: -1
    });

    this.anims.create({
      key: "walk_down",
      frameRate: 7,
      frames: this.anims.generateFrameNumbers("character", { start: 9, end: 10 }),
      repeat: -1
    });

    this.anims.create({
      key: "walk_up",
      frameRate: 7,
      frames: this.anims.generateFrameNumbers("character", { start: 5, end: 7 }),
      repeat: -1
    });

    this.anims.create({
      key: "idle_side",
      frameRate: 7,
      frames: this.anims.generateFrameNumbers("character", { start: 0, end: 0 }),
      repeat: -1
    });

    this.anims.create({
      key: "idle_down",
      frameRate: 7,
      frames: this.anims.generateFrameNumbers("character", { start: 8, end: 8 }),
      repeat: -1
    });

    this.anims.create({
      key: "idle_up",
      frameRate: 7,
      frames: this.anims.generateFrameNumbers("character", { start: 4, end: 4 }),
      repeat: -1
    });

    this.play('idle_down');
  }
}
