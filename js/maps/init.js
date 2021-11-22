let MapGame = {
  Config: {
    type: Phaser.AUTO,

    scale: {
      parent: 'map_canvas',
      mode: Phaser.Scale.FIT,
      height: 15 * 16,
      width: 15 * 16,
    },

    audio: {
      disableWebAudio: false,
    },

    physics: {
      default: 'arcade',
      arcade: {
        debug: true,
      },
    },

    render: {
      antialiasGL: false,
      pixelArt: true,
    },

    plugins: {
      scene: [
        {
          key: "gridEngine",
          plugin: GridEngine,
          mapping: "gridEngine",
        },
      ],
    },

    scene: [
      Render
    ],
  },

  Game: {},
};

MapGame.Game = new Phaser.Game(MapGame.Config);
console.log('[MapGame.Game]', MapGame.Game);
