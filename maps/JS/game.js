/* Game namespace */
var game = {
  map: {},
  touching: false,
  objects: [],

  config: {
    tilesize: 16
  },

  // an object where to store game information
  data: {
    // score
    interference: {},
  },

  // Run on page load.
  onload: function() {
    // Initialize the video.
    if (
      !me.video.init(16 * 15, 16 * 15, {
        wrapper: "screen",
        // renderer : me.video.CANVAS,
        // scale : 1.0,
        // scaleMethod : "fill-max",
        // useParentDOMSize: true,
        // doubleBuffering : true
        antiAlias: true
      })
    ) {
      alert("Your browser does not support HTML5 canvas.");
      return;
    }

    // Initialize the audio.
    me.audio.init("mp3,ogg");

    game.font = new me.Font("courier", "16px", "white");
    game.network = new Network();
    game.actions.load_key_item();

    me.state.ERROR = 9900;
    me.state.CRASH = 9901;

    // set the loading screen
    me.state.set(me.state.LOADING, new game.LoadingScreen());
    me.state.change(me.state.LOADING);

    // set and load all resources.
    // (this will also automatically switch to the loading screen)
    me.loader.preload(game.resources, this.loaded.bind(this));

  },

  // Run on game resources loaded.
  loaded: function() {

    // me.state.set(me.state.MENU, new game.TitleScreen());
    me.state.set(me.state.PLAY, new game.PlayScreen());

    // add our player entity in the entity pool
    me.pool.register("mainPlayer", game.PlayerEntity);
    me.pool.register("StaticObject", game.StaticEntity);
    me.pool.register("transport", game.TransportEntity);

    // final two arguments of bindKey:
    // lock             Boolean   cancels the keypress event once read
    // preventDefault   Boolean   prevent default browser action
    me.input.bindKey(me.input.KEY.LEFT, "left");
    me.input.bindKey(me.input.KEY.RIGHT, "right");
    me.input.bindKey(me.input.KEY.UP, "up");
    me.input.bindKey(me.input.KEY.DOWN, "down");

    me.input.bindKey(me.input.KEY.A, "left", false, false);
    me.input.bindKey(me.input.KEY.D, "right", false, false);
    me.input.bindKey(me.input.KEY.W, "up", false, false);
    me.input.bindKey(me.input.KEY.S, "down", false, false);

    me.input.bindKey(me.input.KEY.C, "run", false, false);
    me.input.bindKey(me.input.KEY.SHIFT, "run", false, false);

    // true means don't continue triggering after press
    me.input.bindKey(me.input.KEY.SPACE, "interact", true); 
    
    // touch movement
    me.input.registerPointerEvent("pointerdown", me.game.viewport, function (event) {
        game.touching = game.getMovementDirection(event.gameScreenX, event.gameScreenY);
    });
    me.input.registerPointerEvent("pointerup", me.game.viewport, function (event) {
        game.touching = false;
    });
    me.input.registerPointerEvent("pointermove", me.game.viewport, function (event) {
        if (game.touching !== false) {
            game.touching = game.getMovementDirection(event.gameScreenX, event.gameScreenY);
        }
    });

    // load map
    this.load_map();

  },

  load_map: function () {
    game.addInterference('loading_map');

    // first figure out what to load
    me.loader.preload([{
      "name": "load",
      "type": "json",
      "src": game.network.URL + "?load"
    }], function () {

      // then load map tileset and tmx file
      let load = me.loader.getJSON('load');
      let map_resources = [
        {
          "name": load['map_name'],
          "type": "tmx",
          "src": game.network.URL + "?tmx"
        }
      ];

      $.each(load['tilesets'], function (index, value) {
        map_resources.push({
          "name": value,
          "type": "image",
          "src": game.network.URL + "?tileset=" + value
        })
      });

      // load and redirect to play screen
      me.loader.preload(map_resources, this.map_loaded.bind(this));
    }.bind(this))
  },

  map_loaded: function () {
    // Start the game.
    game.removeInterference('loading_map');
    me.state.change(me.state.PLAY);
  },

  getMovementDirection: function (x, y) {
    if (x >= y) {
      if (240 - x >= y)
        return 'up';
      else 
        return 'right';
    } else { 
      if (240 - x >= y)
        return 'left';
      else 
        return 'down';
    }
  },

  error: function (content) {
    if (!me.state.isCurrent(me.state.ERROR)) {
      //! passing in arguments doesn't work apparantly
      game._error = content;
      me.state.set(me.state.ERROR, new game.ErrorScreen(content.error, content.code));
      me.state.change(me.state.ERROR);
    }
  },

  crash: function () {
    if (!me.state.isCurrent(me.state.CRASH)) {
      me.state.set(me.state.CRASH, new game.CrashScreen());
      me.state.change(me.state.CRASH);
    }
  },

  addInterference: function (name) {
    game.data.interference[name] = true;
  },

  removeInterference: function (name) {
    game.data.interference[name] = false;      
  },

  hasInterference: function (list) {
    // if (typeof list === "undefined") 
      // list = [];

    let interference = false;
    $.each(game.data.interference, function (key, value) {
      if (value && (typeof list === "undefined" || list.indexOf(key) !== -1))
        interference = true;
    });

    return interference;
  }
};







function randInt(min,max){
  let range = max - min
  let rand = Math.floor(Math.random() * (range + 1));
  return min + rand;
}
  
function Format(x) {
  x=parseInt(x);
  let parts = x.toString().split(".");
  parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  return parts.join(".");
}