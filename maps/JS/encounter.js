game.encounter = {
  start: function () {
    console.log("started encounter");
    // interference from client side when new encounter is detected
    game.addInterference('encounter');
  },

  loaded: function (data) {
    console.log("encounter loaded");
  },

  action: function (action, post_code) {
    switch (action) {
      case 'run':
        game.removeInterference('encounter');
        $('#map_text').html("<div style='padding: 80px 5px;'>You have safely fled from the wild Pokemon.</div>");
        break;
      case 'fight': 
        //window.location = '/battle_create.php?Battle=Wild&Trainer=' + post_code;
        //$('#map_text').html('');
        $('#map_text').html('attemping to fight the wild pokemon');
        break;
      case 'catch':
      case 'release':
        // check to see if you've already encountered
        if (game.hasInterference(['encounter_delay'])) 
          return;
        
        // $('#map_text').html('Loading...');

        // delay when catching or releasing pokemon
        game.addInterference('encounter_delay');
        setTimeout(function () {
          game.removeInterference('encounter_delay');
        }, 750);

        game.network.request({
          action: action,
          post_code: post_code
        }).then(function (e) {
          game.removeInterference('encounter');
        });
        break;
      default:
        alert("Unknown Action!");
        break;
    }
  }
};