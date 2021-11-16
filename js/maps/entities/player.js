class Player_Entity extends Phaser.Physics.Arcade.Sprite
{
  constructor(scene, x, y)
  {
    console.log('[Player_Entity] Initializing:', scene, x, y);
    super(scene, x, y, 'texture');

    this.scene.add.existing(this);
  }
}
