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
    Scene_Events.on('NPC_Dialogue_Remove', this.RemoveDialogue, this);
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

      this.add.text(10, 200, Dialogue.value, {
        color: '#000',
        fontFamily: 'Times New Roman',
        fontSize: 12,
        wordWrap: { width: 200 }
      });

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
