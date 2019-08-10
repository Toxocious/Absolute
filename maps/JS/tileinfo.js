game.config.surfaceinfo = {
    "land": {
        surface: "land",
        walkable: true,
        vehicle: "walk"
    },
    "water": {
        surface: "solid",
        walkable: true,
        vehicle: "surf"
    },
    "solid": {
        surface: "solid",
        walkable: false,
        vehicle: "wall",
    },
    "ice": {
        surface: "ice",
        walkable: true,
        slippery: true,
        vehicle: "walk"
    },
    "stair": {
        surface: "stair",
        walkable: true,
        vehicle: "walk",
        stair: true
    }
}

game.config.encounterSlot = function (tileID) {
    if (tileID >= 1 && tileID <= 26) {
        return String.fromCharCode(96 + tileID);
    } else if (tileID >= 121 && tileID <= 128) {
        return tileID - 119;
    } else {
        return false;
    }
}

// tiles in RBY font
game.config.tileinfo = {
  default: "land",

  // triangle down
  111: "stair",

  // pokedollar
  113: "ice",

  // 0
  119: "land",
  // 1
  120: "solid",
  // 2
  121: "water",
  // 3
  122: "water",
  // 4
  123: "water",
  // 5
  124: "water",
  // 6
  125: "water",
  // 7
  126: "water",
  // 8
  127: "water",
  // 9
  128: "water",

  // A
  1: "land",
  // B
  2: "land",
  // C
  3: "land",
  // D
  4: "land",
  // E
  5: "land",
  // F
  6: "land",
  // G
  7: "land",
  // H
  8: "land",
  // I
  9: "land",
  // J
  10: "land",
  // K
  11: "land",
  // L
  12: "land",
  // M
  13: "land",
  // N
  14: "land",
  // O
  15: "land",
  // P
  16: "land",
  // Q
  17: "land",
  // R
  18: "land",
  // S
  19: "land",
  // T
  20: "land",
  // U
  21: "land",
  // V
  22: "land",
  // W
  23: "land",
  // X
  24: "land",
  // Y
  25: "land",
  // Z
  26: "land",
};
