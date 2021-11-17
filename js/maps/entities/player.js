class Player_Entity extends Phaser.Physics.Arcade.Sprite
{
  constructor(scene, x, y)
  {
    super(scene, x * MapGame.Tile_Size, y * MapGame.Tile_Size, 'texture');

    const offsetX = MapGame.Tile_Size / 2;
    const offsetY = MapGame.Tile_Size;

    this.scene.add.existing(this);

    this.setTexture('character');
    this.setDepth(3);
    this.setOrigin(0.5, 0.5);
    this.setPosition(
      x * MapGame.Tile_Size + offsetX,
      y * MapGame.Tile_Size + offsetY
    );
  }

  Update(Time, Delta)
  {
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
}
