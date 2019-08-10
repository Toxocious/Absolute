// handles grid based movement for an entity
//! need to remove "player z" and make it any z
class Movement {
  constructor(entity) {
    this.DEBUG_MOVEMENT = false;

    this.entity = entity;

    this.destination = null;
    this.lastPosition = new me.Vector2d(0, 0);
    this.speed = 100;

    this.sliding = false;
    this.sliding_direction = null;
  }

  // main move logic
  move(moveIntention) {
    // force a direction when sliding
    if (this.sliding) {
      if (
        this.isTileSlippery(
          this.destination.x,
          this.destination.y,
          this.destination.z
        )
      )
        moveIntention = this.sliding_direction;
    }

    // Stop the moving entity if at the destination.
    if (this.isMoving() && this.justReachedDestination() && !moveIntention) {
      if (this.DEBUG_MOVEMENT)
        console.log("Stop the moving entity if at the destination.");

      this.setIdleAnimation(this.entity.facing);
      this.stopMoving();
    }
    // Stop the moving entity when it hits a wall.
    else if (
      this.isMoving() &&
      this.justReachedDestination() &&
      moveIntention &&
      !this.canMoveDirectionFromTile(
        this.destination.x,
        this.destination.y,
        this.entity.pos_z,
        moveIntention
      )
    ) {
      if (this.DEBUG_MOVEMENT)
        console.log("Stop the moving entity when it hits a wall.");

      this.setIdleAnimation(this.entity.facing);
      this.stopMoving();
    }
    // Destination reached, but set new destination and keep going.
    else if (
      this.isMoving() &&
      this.justReachedDestination() &&
      moveIntention &&
      this.canMoveDirectionFromTile(
        this.destination.x,
        this.destination.y,
        this.entity.pos_z,
        moveIntention
      ) &&
      moveIntention === this.entity.facing
    ) {
      if (this.DEBUG_MOVEMENT)
        console.log(
          "Destination reached, but set new destination and keep going."
        );

      this.continueMovingFromDestination();
      this.setMovementAnimation(moveIntention);
    }
    // Destination reached, but changing direction and continuing.
    else if (
      this.isMoving() &&
      this.justReachedDestination() &&
      moveIntention &&
      this.canMoveDirectionFromTile(
        this.destination.x,
        this.destination.y,
        this.entity.pos_z,
        moveIntention
      ) &&
      moveIntention !== this.entity.facing
    ) {
      if (this.DEBUG_MOVEMENT)
        console.log(
          "Destination reached, but changing direction and continuing."
        );

      this.changeDirectionAndContinueMoving(moveIntention);
      this.setMovementAnimation(moveIntention);
    }
    // Destination not yet reached, so keep going.
    else if (this.isMoving() && !this.justReachedDestination()) {
      if (this.DEBUG_MOVEMENT)
        console.log("Destination not yet reached, so keep going.");

      this.continueMovingToDestination();
    }
    // Not moving, but wanting to, so start!
    else if (
      !this.isMoving() &&
      moveIntention &&
      this.canMoveDirectionFromCurrentTile(moveIntention)
    ) {
      if (this.DEBUG_MOVEMENT)
        console.log("Not moving, but wanting to, so start!");

      this.startMoving(moveIntention);
      this.setMovementAnimation(moveIntention);
    }
    // Not moving, but wanting to, but cannot.
    else if (
      !this.isMoving() &&
      moveIntention &&
      !this.canMoveDirectionFromCurrentTile(moveIntention)
    ) {
      if (this.DEBUG_MOVEMENT)
        console.log("Not moving, but wanting to, but cannot.");

      this.entity.facing = moveIntention;
      this.setIdleAnimation(moveIntention);
    }
    // else no movement action has been processed
    else {
      return false;
    }

    //
    return true;
  }

  setIdleAnimation(dir) {
    let movement = "idle";
    if (this.entity.vehicle == "surf") {
      movement = "surf";
    }

    if (dir == "left") {
      this.entity.renderable.flipX(false);
      if (!this.entity.renderable.isCurrentAnimation(movement + "_side")) {
        this.entity.renderable.setCurrentAnimation(movement + "_side");
      }
    } else if (dir == "right") {
      this.entity.renderable.flipX(true);
      if (!this.entity.renderable.isCurrentAnimation(movement + "_side")) {
        this.entity.renderable.setCurrentAnimation(movement + "_side");
      }
    } else if (dir == "up") {
      this.entity.renderable.flipX(false);
      if (!this.entity.renderable.isCurrentAnimation(movement + "_up")) {
        this.entity.renderable.setCurrentAnimation(movement + "_up");
      }
    } else if (dir == "down") {
      this.entity.renderable.flipX(false);
      if (!this.entity.renderable.isCurrentAnimation(movement + "_down")) {
        this.entity.renderable.setCurrentAnimation(movement + "_down");
      }
    }
  }

  setMovementAnimation(dir) {
    let movement;
    if (this.entity.vehicle == "surf") movement = "surf";
    
    //! FIX PLEASE
    else if (this.sliding) movement = "sliding";
    else if (this.entity.isRunning) movement = "run";
    else movement = "walk";

    if (dir == "left") {
      this.entity.renderable.flipX(false);
      if (!this.entity.renderable.isCurrentAnimation(movement + "_side")) {
        this.entity.renderable.setCurrentAnimation(movement + "_side");
      }
    } else if (dir == "right") {
      this.entity.renderable.flipX(true);
      if (!this.entity.renderable.isCurrentAnimation(movement + "_side")) {
        this.entity.renderable.setCurrentAnimation(movement + "_side");
      }
    } else if (dir == "up") {
      this.entity.renderable.flipX(false);
      if (!this.entity.renderable.isCurrentAnimation(movement + "_up")) {
        this.entity.renderable.setCurrentAnimation(movement + "_up");
      }
    } else if (dir == "down") {
      this.entity.renderable.flipX(false);
      if (!this.entity.renderable.isCurrentAnimation(movement + "_down")) {
        this.entity.renderable.setCurrentAnimation(movement + "_down");
      }
    }
  }

  warpTo(x, y, z) {
    this.destination = null;
    this.entity.body.vel.set(0, 0);
    this.lastPosition.set(0, 0);
    this.entity.pos_z = z;
    this.snapToTile(x, y);
    this.setIdleAnimation(this.entity.facing);
  }

  startMoving(direction) {
    // Change Velocity while running
    this.setMaxVelocity();
    this.lastPosition.set(this.entity.pos.x, this.entity.pos.y);
    // Get current tile position.
    let currentTile = this.getCurrentTile();
    // Get new destination.
    this.destination = this.getTileAdjacentToTile(
      currentTile.x,
      currentTile.y,
      currentTile.z,
      direction
    );
    // Check if the next tile is slippery
    this.startSliding(direction);
    // Move.
    this.setVelocityByTile(this.destination.x, this.destination.y, this.speed);
    // Remember this move for later.
    this.entity.facing = direction;
  }

  continueMovingToDestination() {
    // Move.
    this.setVelocityByTile(this.destination.x, this.destination.y, this.speed);
  }

  continueMovingFromDestination() {
    // Change Velocity while running
    this.setMaxVelocity();
    this.lastPosition.set(this.entity.pos.x, this.entity.pos.y);
    // Get new destination.
    this.destination = this.getTileAdjacentToTile(
      this.destination.x,
      this.destination.y,
      this.entity.pos_z,
      this.entity.facing
    );
    // Check if the next tile is slippery
    this.startSliding(this.entity.facing);
    // Move.
    this.setVelocityByTile(this.destination.x, this.destination.y, this.speed);
  }

  changeDirectionAndContinueMoving(newDirection) {
    // Change Velocity while running
    this.setMaxVelocity();
    this.lastPosition.set(this.entity.pos.x, this.entity.pos.y);
    // Method only called when at destination, so snap to it now.
    this.snapToTile(this.destination.x, this.destination.y);
    // Get new destination.
    this.destination = this.getTileAdjacentToTile(
      this.destination.x,
      this.destination.y,
      this.entity.pos_z,
      newDirection
    );
    // Check if the next tile is slippery
    this.startSliding(newDirection);
    // Move.
    this.setVelocityByTile(this.destination.x, this.destination.y, this.speed);
    // Remember this move for later.
    this.entity.facing = newDirection;
  }

  startSliding(direction) {
    // check if the destination is slippery
    if (
      this.isTileSlippery(
        this.destination.x,
        this.destination.y,
        this.destination.z
      )
    ) {
      if (this.DEBUG_MOVEMENT)
        console.log("Slippery tile detected, start sliding");
      this.sliding = true;
      this.sliding_direction = direction;
      this.setMovementAnimation(direction);

      // if you were already sliding; stop
    } else if (this.sliding == true) {
      if (this.DEBUG_MOVEMENT)
        console.log("Slippery segment has ceased. Stop sliding");
      this.sliding_direction = null;
      this.sliding = false;
      console.log("STOP SLIDING", direction);
      this.setMovementAnimation(direction);
    }
  }

  stopMoving() {
    // Method only called when at destination, so snap to it now.
    this.snapToTile(this.destination.x, this.destination.y);
    // We are already at the destination.
    this.destination = null;
    // no more sliding
    this.sliding = false;
    // Stop.
    this.entity.body.vel.set(0, 0);
  }

  // align position exactly to game grid
  snapToTile(x, y) {
    this.entity.pos.x = x * game.config.tilesize;
    this.entity.pos.y = y * game.config.tilesize;

    let currentTile = this.getCurrentTile();
    let tileinfo = game.map.getTileInfo(
      currentTile.x,
      currentTile.y,
      currentTile.z
    );

    if (tileinfo.stair) {
      if (this.DEBUG_MOVEMENT)
        console.log("On a stair tile, so next movement could be to any z.");
    //   this.entity.pos_z = -1;
    }
  }

  justReachedDestination() {
    var destinationX = this.destination.x * game.config.tilesize;
    var destinationY = this.destination.y * game.config.tilesize;

    var result =
      (this.entity.pos.x >= destinationX &&
        this.lastPosition.x < destinationX) ||
      (this.entity.pos.x <= destinationX &&
        this.lastPosition.x > destinationX) ||
      (this.entity.pos.y >= destinationY &&
        this.lastPosition.y < destinationY) ||
      (this.entity.pos.y <= destinationY && this.lastPosition.y > destinationY);
    return result;
  }

  canMoveDirectionFromTile(x, y, z, direction) {
    var newPos = this.getTileAdjacentToTile(x, y, z, direction);

    return game.map.canMove(this.entity, newPos.x, newPos.y, newPos.z);
  }

  canMoveDirectionFromCurrentTile(direction) {
    let currentTile = this.getCurrentTile();
    return this.canMoveDirectionFromTile(
      currentTile.x,
      currentTile.y,
      currentTile.z,
      direction
    );
  }

  // Sets the velocity of the entity so that it will move toward the tile.
  setVelocityByTile(tileX, tileY, velocity) {
    var tileCenterX = tileX * game.config.tilesize + game.config.tilesize / 2;
    var tileCenterY = tileY * game.config.tilesize + game.config.tilesize / 2;
    var entityCenterX = this.entity.pos.x + this.entity.size.x / 2;
    var entityCenterY = this.entity.pos.y + this.entity.size.y / 2;

    let velX = 0;
    let velY = 0;
    if (entityCenterX > tileCenterX) velX = -velocity;
    else if (entityCenterX < tileCenterX) velX = velocity;
    else if (entityCenterY > tileCenterY) velY = -velocity;
    else if (entityCenterY < tileCenterY) velY = velocity;

    this.entity.body.vel.set(velX, velY);
  }

  getCurrentTile() {
    let x = this.entity.pos.x / game.config.tilesize;
    let y = this.entity.pos.y / game.config.tilesize;
    return { x: x, y: y, z: this.entity.pos_z };
  }

  getTileAdjacentToTile(x, y, z, direction) {
    if (direction === "up") y += -1;
    else if (direction === "down") y += 1;
    else if (direction === "left") x += -1;
    else if (direction === "right") x += 1;
    return { x: x, y: y, z: z };
  }

  isMoving() {
    return this.destination !== null;
  }

  isTileSlippery(x, y, z) {
    let tileinfo = game.map.getTileInfo(x, y, z);
    return tileinfo.slippery;
  }

  setMaxVelocity() {
    if (this.entity.isRunning && this.entity.vehicle == "walk" && !this.sliding)
      this.entity.body.maxVel.set(
        this.entity.runVelocity.x,
        this.entity.runVelocity.y
      );
    else
      this.entity.body.maxVel.set(
        this.entity.walkVelocity.x,
        this.entity.walkVelocity.y
      );
  }
}
