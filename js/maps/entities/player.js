class Player_Entity extends Phaser.Physics.Arcade.Sprite
{
  constructor(scene, x, y)
  {
    super(scene, x * 16, y * 16, 'texture');

    this.scene.add.existing(this);
    this.setTexture('character');
  }
}
