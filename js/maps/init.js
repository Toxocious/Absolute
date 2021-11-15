let MapGame = {
  Config: {
    type: Phaser.AUTO,

    scale: {
      parent: 'map_canvas',
      mode: Phaser.Scale.FIT,
      height: 15 * 16,
      width: 15 * 16,
    },

    scene: [
      Render
    ],
  },

  Game: {},
};

MapGame.Game = new Phaser.Game(MapGame.Config);
console.log('[MapGame.Game]', MapGame.Game);
