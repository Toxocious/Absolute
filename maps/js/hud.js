class HUD extends Phaser.Scene
{
  constructor(Render_Instance)
  {
    super({ key: 'HUD' });

    this.Render_Instance = Render_Instance;

    this.In_Dialogue = false;
    this.Dialogue_State = null;
  }

  create()
  {
    console.log('[HUD] Has been created');

    this.Dialogue_Box = this.add.group({
      classType: Phaser.GameObjects.Image
    });

    /**
     * Listen for custom scene events.
     */
    Scene_Events.on('NPC_Dialogue', this.DisplayDialogue, this);
  }

  /**
   * Display active dialogue.
   */
  DisplayDialogue(Dialogue_Array)
  {
    if ( !this.In_Dialogue )
    {
      console.log('[HUD | Dialogue] Displaying object dialogue', Dialogue_Array);

      this.In_Dialogue = true;
      this.Dialogue_State = 1;

      this.Dialogue_Box.createMultiple({
        key: 'dialogue_frame',
        quantity: 1,
        setXY: {
          x: 120,
          y: 220
        }
      });
    }
  }
}
