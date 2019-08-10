game.PlayScreen = me.ScreenObject.extend({
  /**
   *  action to perform on state change
   */
  onResetEvent: function() {
    // load the level

    //! this will have to be moved from player since load is on load of everything
    //! change the name of load
    // Retrieves the starting data from the server and sets the player
    let load = me.loader.getJSON('load');

    me.levelDirector.loadLevel(load['map_name']);
    game.player = me.game.world.getChildByName("mainPlayer")[0];

    game.player.movement.warpTo(load['position']['x'], 
                                load['position']['y'], 
                                load['position']['z']);
    game.map.adjustPlayerLayer(load['position']['x'], load['position']['y']);
    game.data.next_encounter = load['next_encounter'];

    let pos = game.player.movement.getCurrentTile();
    let currentTile = game.map.getTileInfo(pos.x, pos.y, pos.z);
    if (currentTile.vehicle == "surf")
    {
      game.player.vehicle = "surf";
      game.player.movement.setIdleAnimation('down');
    }
    
    $.each(game.objects, function (key, object) {
        $.each(load['objects'], function (key2, map_object) {
            if (object.object_id == map_object.object_id && map_object.active === false)
            {
                game.objects[key] = null;
                object.remove();
            }
        });
    });

    // console.log(me.levelDirector.getCurrentLevel());

    // Add our HUD to the game world, add it last so that this is on top of the rest.
    // Can also be forced by specifying a "Infinity" z value to the addChild function.
    this.HUD = new game.HUD.Container();
    me.game.world.addChild(this.HUD);
    
    me.game.world.sort();

    /*
      shitty transformation for glitch city.. will probaly just not do this
      var scale = new me. Matrix2d ();
      scale.scale(2, 2);
      scale.translate(-60,-60);
      me.game.world.currentTransform = scale;
    */
  },

  /**
   *  action to perform when leaving this screen (state change)
   */
  onDestroyEvent: function() {
    // remove the HUD from the game world
    me.game.world.removeChild(this.HUD);

    // stop tracking the player
    me.game.viewport.unfollow();

    // move the camera to the top left
    me.game.viewport.moveTo(0, 0);
  }
});
