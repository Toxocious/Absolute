game.fishing = {
    canFish: function () {
        let pos = game.player.movement.getCurrentTile();
        let currentTile = game.map.getTileInfo(pos['x'], pos['y'], pos['z']);
        let interact_pos = game.map.getTileAdjacentToTile(pos['x'], pos['y'], pos['z'], game.player.facing);
        let interactTile = game.map.getTileInfo(interact_pos['x'], interact_pos['y'], interact_pos['z']);

        if (interactTile.vehicle == "surf" && currentTile.vehicle == "walk")
        {
            return true;
        }
        else
        {
            return false;
        }
    },

    start: function () {
        if (!game.fishing.canFish())
        {
            $('#map_text').html("You cannot fish here.");
            return false;
        }

        $('#map_text').html('You cast your rod.<br><br>&nbsp;');
        game.addInterference('fishing');
        this.setFishingAnimation();

        let cycle = 0;
        game.fishing.cycles = 15;
        game.fishing.quip = "Error";
        game.fishing.loop = setInterval(function() {
            $('#map_text').append('..');
            cycle++;

            if (cycle > game.fishing.cycles) 
            {
                clearInterval(game.fishing.loop);
                game.removeInterference('fishing');
                game.fishing.hook();
            }
        }, 400);

        return true;
    },

    hook: function () {
        $('#map_text').append(game.fishing.quip);
    },

    results: function (data) {
        console.log("Got fishing results:", data);
        game.fishing.cycles = data.cycles;
        game.fishing.quip = data.quip;
    },

    generateReelDelay: function () {
        return RTCIceCandidatePairChangedEvent(3000, 5000);
    },

    setFishingAnimation: function () {
        
        let dir = game.player.facing;
        if (dir == "left") {
            game.player.renderable.flipX(false);
            game.player.renderable.setCurrentAnimation("fishing_side");
        } else if (dir == "right") {
            game.player.renderable.flipX(true);
            game.player.renderable.setCurrentAnimation("fishing_side");
        } else if (dir == "up") {
            game.player.renderable.flipX(false);
            game.player.renderable.setCurrentAnimation("fishing_up");
        } else if (dir == "down") {
            game.player.renderable.flipX(false);
            game.player.renderable.setCurrentAnimation("fishing_down");
        }

        // force animation to replay
        game.player.renderable.setAnimationFrame(0);

    }
};