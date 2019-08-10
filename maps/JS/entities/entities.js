/**
 * Player Entity
 */

// grid based movement based off of:
// https://github.com/Joncom/impact-grid-movement/blob/master/lib/game/entities/player.js

game.PlayerEntity = me.Entity.extend({
  /**
   * constructor
   */
  init: function(x, y, settings) {
    // call the constructor
    this._super(me.Entity, "init", [x, y, settings]);

    // set animations
    this.renderable.addAnimation("walk_side", [1, 2, 3]);
    this.renderable.addAnimation("walk_down", [9, 10, 11]);
    this.renderable.addAnimation("walk_up", [5, 6, 7]);

    this.renderable.addAnimation("run_side", [13, 14, 15]);
    this.renderable.addAnimation("run_down", [21, 22, 23]);
    this.renderable.addAnimation("run_up", [17, 18, 19]);

    this.renderable.addAnimation("surf_side", [24, 25]);
    this.renderable.addAnimation("surf_up", [26, 27]);
    this.renderable.addAnimation("surf_down", [28, 29]);

    this.renderable.addAnimation("sliding_side", [0, 4, 12, 8]);
    this.renderable.addAnimation("sliding_up", [0, 4, 12, 8]);
    this.renderable.addAnimation("sliding_down", [0, 4, 12, 8]);

    this.renderable.addAnimation("idle_side", [0]);
    this.renderable.addAnimation("idle_down", [8]);
    this.renderable.addAnimation("idle_up", [4]);

    this.renderable.addAnimation("got_item", [19, 20, 21, 22, 23 ]);

    this.renderable.addAnimation("fishing_down", [
        { name: 36, delay: 300 }, 
        { name: 37, delay: 300 }, 
        { name: 38, delay: 300 }, 
        { name: 39, delay: Infinity }, 
    ]);

    this.renderable.addAnimation("fishing_up", [
        { name: 40, delay: 300 }, 
        { name: 41, delay: 300 }, 
        { name: 42, delay: 300 }, 
        { name: 43, delay: Infinity }, 
    ]);

    this.renderable.addAnimation("fishing_side", [
        { name: 44, delay: 300 }, 
        { name: 45, delay: 300 }, 
        { name: 46, delay: 300 }, 
        { name: 47, delay: Infinity }, 
    ]);

    // set the standing animation as default
    this.renderable.setCurrentAnimation("idle_down");

    // max walking & jumping speed
    this.walkVelocity = new me.Vector2d(1, 1);
    this.runVelocity = new me.Vector2d(2, 2);
    this.body.vel.set(0, 0);
    this.body.maxVel.set(this.walkVelocity.x, this.walkVelocity.y);
    this.body.accel.set(0, 0);
    this.body.friction.set(0.9, 0.9);

    this.body.gravity = 0;
    this.body.jumping = false;
    this.body.falling = false;

    // movement properties
    this.alwaysRunning = false;
    this.facing = null;
    this.vehicle = "walk";
    this.movement = new Movement(this);
    this.pos_z = settings.start_z;

    this.size = { x: 16, y: 16 };

    // Set the sprite anchor point
    // for a 32x32 spritesheet:
    // this.anchorPoint.set(-0.5, -0.5);
    // for a 48x48 spritesheet:
    this.anchorPoint.set(-1, -1);

    // ensure the player is updated even when outside of the viewport
    this.alwaysUpdate = true;

    this.anchorViewportOffset = 8;
    this.anchorPos = new me.Vector2d(0, 0);
    this.anchorPos.x = this.pos.x + this.anchorViewportOffset;
    this.anchorPos.y = this.pos.y + this.anchorViewportOffset;

    this.syncedPosition = {
      x: -1,
      y: -1,
      z: -1
    };

    // set the display to foloow our position on both axis
    me.game.viewport.follow(this.anchorPos, me.game.viewport.AXIS.BOTH);
    me.game.viewport.setDeadzone(0, 0);

  },

  syncPosition: function () {
    let pos = this.movement.getCurrentTile();

    let x = Math.round(pos.x);
    let y = Math.round(pos.y);
    let z = Math.round(this.pos_z);

    if (!(this.syncedPosition.x == x
      && this.syncedPosition.y == y))
    {
      this.syncedPosition = {
        x: x,
        y: y,
        z: z
      };

      return true;
    }
    else
    {
      return false;
    }
  },

  interact: function (data) {
    if (this.movement.isMoving())
      return false;
    let pos = this.movement.getCurrentTile();
    game.map.interact(this, pos.x, pos.y, this.pos_z, this.facing, data);
  },

  /**
   * update the entity
   */
  update: function(dt) {
    this.moveIntention = null;
    
    if (game.hasInterference()) this.moveIntention = null;
    else if (me.input.isKeyPressed("left") || game.touching == 'left') this.moveIntention = "left";
    else if (me.input.isKeyPressed("right") || game.touching == 'right') this.moveIntention = "right";
    else if (me.input.isKeyPressed("up") || game.touching == 'up') this.moveIntention = "up";
    else if (me.input.isKeyPressed("down")  || game.touching == 'down') this.moveIntention = "down";

    if (this.alwaysRunning) 
      this.isRunning = !me.input.isKeyPressed("run");
    else 
      this.isRunning = me.input.isKeyPressed("run");
    
    let moved = this.movement.move(this.moveIntention);

    // if the player hasn't moved, then you can perform interactions
    if (!moved) {
      if (me.input.isKeyPressed("interact")) {
        this.interact({});
      }
    }
    // don't sync position when sliding
    else if (!this.movement.sliding)
    {
      if (this.syncPosition()) {
        game.network.syncPosition(this.syncedPosition.x, this.syncedPosition.y, this.syncedPosition.z);
        game.map.doMove(this.syncedPosition.x, this.syncedPosition.y, this.syncedPosition.z);
      }
    }

    // apply physics to the body (this moves the entity)
    this.body.update(dt);

    this.anchorPos.x = this.pos.x + this.anchorViewportOffset;
    this.anchorPos.y = this.pos.y + this.anchorViewportOffset;

    // handle collisions against other shapes
    me.collision.check(this);

    // return true if we moved or if the renderable was updated
    return (
      this._super(me.Entity, "update", [dt]) ||
      this.body.vel.x !== 0 ||
      this.body.vel.y !== 0
    );
  },

  /**
   * colision handler
   * (called when colliding with other objects)
   */
  onCollision: function(response, other) {
    switch (response.b.body.collisionType) {
      case me.collision.types.ACTION_OBJECT:
        return false;
      // Make all other objects solid
      default:
        return true;
    }
  }
});
