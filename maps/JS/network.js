class Network {
    constructor() {
        this.URL = "maps/ajax.php";
        
        this.networkPosition = {
            x: 0,
            y: 0,
            z: 0
        }
    }

    request (data) {
        let network = this;
        return new Promise(function (resolve, reject) {
            $.ajax({
                type: 'POST',
                url: game.network.URL,
                data: data,
                success: function (data) {
                    console.log("Data received =>");
                    console.log(game.data);

                    if (data == null) data = {};

                    if (typeof data !== "object") {
                        network.requestError(data);
                        reject(data);
                        return;
                    }

                    if (typeof data.error !== "undefined") {
                        game.error(data);
                        return;
                    }

                    if (typeof data.crash !== "undefined") {
                        game.crash(data);
                    }

                    if (typeof data.alert !== "undefined") {
                        alert(data.alert);
                    }

                    if (typeof data.delay !== "undefined") {
                        game.addInterference('delay');
                        setTimeout(function () {
                            game.removeInterference('delay');
                        }, data.delay);
                    }

                    if ( typeof game.data.interference.encounter !== "undefined" )
                    {
                        game.encounter.loaded(data);
                    }

                    if (typeof data.destroy_object !== "undefined") {
                        game.map.removeObject(data.destroy_object);
                    }

                    if (typeof data.addInterference !== "undefined") {
                        game.addInterference(data.addInterference);
                    }

                    if (typeof data.removeInterference !== "undefined") {
                        game.removeInterference(data.removeInterference);
                    }

                    if (typeof data.Text !== "undefined") {
                        $('#map_text').html(data.Text);
                    }

                    if (typeof data.warp_to_map !== "undefined") {
                        me.state.change(me.state.LOADING);
                        game.load_map();
                    }

                    resolve(data);
                },
                error: function (data)
                {
                    network.requestError(data.responseText);
                    reject(data);
                }
            });
        });
    }

    requestError (data) {
        console.log("Error Content =>");
        console.log(data);

        game.error({
            error: " Server Error",
            code: "101"
        });
    }

    interact (x, y, z, data) {
        game.addInterference('Interact');

        data.x = x;
        data.y = y;
        data.z = z;
        data.action = 'Interact';

        this.request(data).then (function (data)
        {
            if (typeof data.start_surfing !== "undefined")
            {
                game.map.start_surfing();
            }
        }).finally(function() {
            game.removeInterference('Interact');
        });
    }

    // sync position sends the position of the player off to the server
    //  if your game gets in an invalid state then throw an error
    syncPosition(x, y, z)
    {
        x = Math.round(x);
        y = Math.round(y);
        z = Math.round(z);

        if (!(this.networkPosition.x == x
         && this.networkPosition.y == y
         && this.networkPosition.z == z))
        {
            this.networkPosition = {
                x: x,
                y: y,
                z: z,
            };

            this.request({
                x: x,
                y: y,
                z: z,
                action: 'Move'
            }).then(function (e) {
                //console.log(e);
            });
        }
    }
}