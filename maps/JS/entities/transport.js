/**
 * Transport Entity
 */

// https://github.com/melonjs/melonJS/blob/master/src/entity/level_entity.js
game.TransportEntity = me.Entity.extend({
  /**
   * constructor
   */
  init: function(x, y, settings) {
    // call the constructor
    this._super(me.Entity, "init", [x, y, settings]);

    this.body.collisionType = me.collision.types.ACTION_OBJECT;

    this.map_x = settings.map_x;
    this.map_y = settings.map_y;
    this.map_z = settings.map_z;

    this.animation = settings.animation ? settings.animation : "fade";
    this.fade_duration = 250;
    this.fade_color = "#000000";

    this.warping = false;
  },

  /**
   * colision handler
   * (called when colliding with other objects)
   */
  onCollision: function(response, other) {

    if (this.warping)
        return false;
        
    this.warping = true;

    let transport = this;
    game.addInterference('warp');
    game.network.networkPosition = {
        x: this.pos.x / game.config.tilesize,
        y: this.pos.y / game.config.tilesize,
        z: game.player.pos_z
    };

    game.network.request({
        action: 'warp',
        x: this.pos.x / game.config.tilesize,
        y: this.pos.y / game.config.tilesize,
        //! z: this.pos_z,
    }).then(function (data) {

        console.log(data);

        game.removeInterference('warp');
        if (data.warp_to_map) 
        {
            return;
        }
        
        transport.warp(transport, other);

    });

    return false;
  },

  warp: function(transport, other) {
      
    if (transport.animation == "none") {
        other.movement.warpTo(transport.map_x, transport.map_y, transport.map_z);
        this.warping = false;

        return false;
    }
  
    if (transport.animation == "fade") {
        game.addInterference('warp_fade');
        me.game.viewport.fadeIn(
            transport.fade_color,
            transport.fade_duration,
            transport.onFadeComplete.bind(transport, other)
        );
        return false;
    }

  },

  onFadeComplete: function(other) {
    other.movement.warpTo(this.map_x, this.map_y, this.map_z);
    me.game.viewport.fadeOut(this.fade_color, this.fade_duration, function () {
        game.removeInterference('warp_fade');

    });
    this.warping = false;

  }
});
