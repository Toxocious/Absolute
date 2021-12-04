class HUD extends Phaser.Scene
{
  constructor(Render_Instance)
  {
    super({ key: 'HUD' });

    this.Render_Instance = Render_Instance;

    this.In_Dialogue = false;
  }

  create()
  {
    console.log('[HUD] Has been created');

    const Dialogue_Box = this.add.group({
      classType: Phaser.GameObjects.Image
    });

    Dialogue_Box.createMultiple({
      key: 'dialogue_frame',
      quantity: 1,
      setXY: {
        x: 120,
        y: 220
      }
    });
  }
}
