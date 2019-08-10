
game.CrashScreen = me.ScreenObject.extend({
  /**
   *  action to perform on state change
   */
  onResetEvent: function(error, code) {
    this.background = new me.ColorLayer("background", "#FFF", 0);
    me.game.world.addChild(this.background, 0 );
    
    this.crash = new me.Sprite(-16, -16, {
        image: me.loader.getImage('crash'),
        framewidth: 320,
        frameheight:320,
        anchorPoint : new me.Vector2d(0, 0)
    });

    me.game.world.addChild(this.crash, 1);
  },    

  /**
   *  action to perform when leaving this screen (state change)
   */
  onDestroyEvent: function() {
    me.game.world.removeChild(this.background);
    me.game.world.removeChild(this.crash);
  }
});
