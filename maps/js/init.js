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
        gravity: { y: 0 },
      },
    },

    render: {
      antialiasGL: true,
      pixelArt: true,
    },

    pack: {
        files: [{
            type: 'plugin',
            key: 'rexawaitloaderplugin',
            url: '/js/dependencies/phaser-rexawaitloaderplugin.min.js',
            start: true
        }]
    },

    plugins: {
      scene: [
        {
          key: 'gridEngine',
          plugin: GridEngine,
          mapping: 'gridEngine',
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
