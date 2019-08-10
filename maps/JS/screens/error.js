
/**
 * a basic HUD item to display score
 */
game.ErrorWindow = me.Renderable.extend( {
  /**
   * constructor
   */
  init: function(error, code, x, y, w, h)
  {
    // call the parent constructor
    // (size does not matter here)
    this._super(me.Renderable, 'init', [x, y, w, h]);
    
    this.header_font = new me.Font("Consolas", "30px", "#4A618F");
    this.text_font = new me.Font("Consolas", "16px", "#fff");
    this.alwaysUpdate = true;
    this.invalidate = true;

    // local copy of the global score
    this.error = error;
    this.code = code;
  },

  /**
   * update function
   */
  update: function(dt)
  {
  if (this.invalidate === true) {
    this.invalidate = false;
    return true;
      }
      
  return false;
  },

  draw: function(renderer)
  {
    this.header_font.draw (renderer, "Error", 70, 16);
    this.text_font.draw   (renderer, this.error, 50, 16 + 40);
    this.header_font.draw (renderer, "Code", 75, 16 + 80);
    this.text_font.draw   (renderer, "#" + this.code, 92, 16 + 120);
    this.text_font.draw   (renderer, "Please reload the page", 22, 16 + 186);
  }
});
  
game.ErrorScreen = me.ScreenObject.extend({
  /**
   *  action to perform on state change
   */
  onResetEvent: function(error, code) {
    me.game.world.addChild( new me.ColorLayer("background", "#161d2a", 0), 0 );

    var w = me.video.renderer.getWidth();
    var h = me.video.renderer.getHeight();

    //! passing in arguments doesn't work apparantly
    error = game._error.error;
    code = game._error.code;

    if (error == null) error = "Error failed";
    if (code == null) code = "000";

    var ErrorWindow = new game.ErrorWindow( error, code, 0, 0, w, h );
    ErrorWindow.anchorPoint.set(0,0);
    me.game.world.addChild(ErrorWindow, 1);
  },    

  /**
   *  action to perform when leaving this screen (state change)
   */
  onDestroyEvent: function() {
    // remove the HUD from the game world
    // me.game.world.removeChild(this.ErrorWindow);
  }
});