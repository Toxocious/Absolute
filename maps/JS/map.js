game.map = {
  doMove: function (x, y, z) {
    let currentTile = game.map.getTileInfo(x, y, z);

    // Check for encounters
    if (currentTile.encounter) {
      console.log("=====");
      console.log(game.data);
      console.log("=====");

      if (game.data.interference.encounter == true) {
        game.encounter.start();
      }
    }
  },

  canMove: function(player, x, y, z) {
    let tileinfo = game.map.getTileInfo(x, y, z);
    let pos = player.movement.getCurrentTile();
    let currentTile = game.map.getTileInfo(pos.x, pos.y, pos.z);

    // check for object collision
    let object_collision = false;
    $.each(game.objects, function (index, o) {
        if (o) {
            var object_pos = o.movement.getCurrentTile();
            //! hacky way to do this
            if (x == object_pos.x && y == object_pos.y)
                object_collision = true;
        }
    });

    if (object_collision) return false;
 
    // normal movement
    if (!currentTile.stair) 
    {
        if  (tileinfo.vehicle == "walk" && player.vehicle == "surf") {
            //! bad place to change the vehicle
            game.player.vehicle = "walk";
            return tileinfo.walkable;
        }

        if (tileinfo.vehicle == player.vehicle) {
            return tileinfo.walkable;
        }
    }

    // when on a stair tile, allow every z axis
    else 
    {
      return this.adjustPlayerLayer(x, y);
    }

    return false;
  },

  adjustPlayerLayer: function (x, y) {
    let level = me.levelDirector.getCurrentLevel();
    let layers = level.getLayers();

    var updated = false;
    layers.forEach(function(layer) {

      // get the z index of the tileinfo
      let layer_z = layer.name.replace("tileinfo_", "");
      if (layer_z != layer.name) 
      {
        let tileinfo = game.map.getTileInfo(x, y, layer_z);

        if (tileinfo.walkable && tileinfo.vehicle == game.player.vehicle) {

          updated = true;
          game.player.pos_z = layer_z;
          game.player.pos.z = layer.pos.z;
          me.game.world.sort();
        }
      }
    });

    return updated;

  },

  getTileInfo: function(x, y, z) {
    let level = me.levelDirector.getCurrentLevel();
    let layers = level.getLayers();

    let tileinfo = null;
    layers.forEach(function(layer) {
      if (layer.name == "tileinfo_" + z) {
        tileinfo = layer;
      }
    });

    if (tileinfo == null) {
      return game.config.tileinfo["default"];
    }

    // layer.layerData[x][y]
    // normalize tileinfo by subtracting where the first gid in the font tileset is
    let tile =
      tileinfo.getTileId(x * 16, y * 16) - tileinfo.tileset.firstgid + 1;

    if (game.config.tileinfo[tile]) {
      var surface = game.config.tileinfo[tile];
    } else {
      var surface = game.config.tileinfo["default"];
    }

    var info = game.config.surfaceinfo[surface];
    info.surface = surface;
    info.encounter = game.config.encounterSlot(tile);

    return info;
  },

  interact: function(player, x, y, z, direction, data) {
    let interact_tile = this.getTileAdjacentToTile(x, y, z, direction);
    // console.log(
    //     interact_tile.x,
    //     interact_tile.y,
    //     interact_tile.z
    // );

    game.network.interact(
        interact_tile.x,
        interact_tile.y,
        interact_tile.z,
        data
    );

    return;
  },

  start_surfing: function () {
    // if (player.vehicle == "walk" && tileinfo.vehicle == "surf") {
        game.player.vehicle = "surf";
        game.player.movement.move(game.player.facing);
    //   }
  
    //   if (player.vehicle == "surf" && tileinfo.vehicle == "walk") {
        // player.vehicle = "walk";
        // player.movement.move(direction);
    //   }
  },

  removeObject: function (object_id) {
    $.each(game.objects, function (key, object) {
        if (object.object_id == object_id)
        {
            game.objects[key] = null;
            object.remove();
        }
    });
  },

  getTileAdjacentToTile: function(x, y, z, direction) {
    if (direction === "up") y += -1;
    else if (direction === "down") y += 1;
    else if (direction === "left") x += -1;
    else if (direction === "right") x += 1;
    return { x: x, y: y, z: z };
  }
};
