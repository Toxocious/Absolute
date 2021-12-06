class HUD extends Phaser.Scene
{
  constructor(Render_Instance)
  {
    super({ key: 'HUD' });

    this.Render_Instance = Render_Instance;

    this.Dialogue_State = null;
    this.Dialogue_Text = null;
  }

  create()
  {
    this.Dialogue_Box = this.add.group({
      classType: Phaser.GameObjects.Image
    });

    /**
     * Listen for custom scene events.
     */
    Scene_Events.on('NPC_Dialogue', this.DisplayDialogue, this);
    Scene_Events.on('NPC_Dialogue_Remove', this.RemoveDialogue, this);
  }

  /**
   * Display active dialogue.
   */
  DisplayDialogue(Dialogue_Array)
  {
    if ( !this.In_Dialogue )
    {
      MapGame.Player.In_Dialogue = true;
      this.Dialogue_State = 0;

      this.Dialogue_Box.createMultiple({
        key: 'dialogue_frame',
        quantity: 1,
        setXY: {
          x: 120,
          y: 220
        }
      });

      const Dialogue = Dialogue_Array[this.Dialogue_State];

      this.Dialogue_Text = this.add.text(10, 200, Dialogue.value, {
        color: '#000',
        fontFamily: 'Times New Roman',
        fontSize: 12,
        wordWrap: { width: 200 }
      });

      this.Dialogue_Box.add(this.Dialogue_Text);
    }
  }

  /**
   * Remove active dialogue,
   */
  RemoveDialogue()
  {
    if ( typeof this.Dialogue_State === 'number' )
    {
      this.Dialogue_Box.clear(true);
      this.Dialogue_State = null;
    }
  }
}
