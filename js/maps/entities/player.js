class Player_Entity extends Phaser.Physics.Arcade.Sprite
{
  constructor(scene, x, y)
  {
    super(scene, x * 16, y * 16, 'texture');

    this.scene.add.existing(this);
    this.setTexture('character');

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
