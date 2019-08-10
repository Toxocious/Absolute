game.actions = {
    interact: function (data)  {
        game.player.interact(data);
    },

    settings: function () {
        if (game.hasInterference())
            return;
        game.addInterference('menu');
        game.network.request({
            action: "open_settings"
        });
    },

    set_option: function (option_name, value) {
        $('#map_options_text').html('Loading')
            .attr('class', 'error' )
            .css('width', '200px' )
            .css('margin-bottom', '10px' )

        game.network.request({
            action: "set_option",
            option_name: option_name,
            value: value
        }).then(function (data) {
            if (typeof data.map_options_text !== "undefined") {
                $('#map_options_text').html(data.map_options_text)
                    .attr('class', 'neutral' )

            }
        });
    },

    update_sound: function (name, value) {
        $('#' + name).html(value);
        game.actions.set_option(name, value)
    },

    bag: function () {
        if (game.hasInterference())
            return;
        game.addInterference('menu');
        game.network.request({
            action: "open_bag"
        });
    },

    quest_book: function() {

    },

    close_menu: function() {
        game.removeInterference('menu');
        $('#map_text').html('');
    },

    toggle_movement: function () {
        $('#action_running').toggle();
        $('#action_walking').toggle();

        game.player.alwaysRunning = !game.player.alwaysRunning;
    },

    bag_use_item: function (item_id, item_img) {
        this.close_menu();
        this.use_item(item_id, item_img);
    },

    use_item: function (item_id, item_img) {

        if (game.hasInterference()) 
            return;
        game.addInterference('interact');

        // set last used item as the key item in case people don't figure out right click
        if (item_id !== this.key_item_id) {
            this.set_key_item(item_id, item_img);
        }
        
        // Start fishing
        if (['270','271','272'].indexOf(''+item_id) !== -1) 
        {
            if (!game.fishing.start()) {
                game.removeInterference('interact');
                return;
            }
        }

        game.network.request({
            action: 'use_item',
            dir: game.player.facing,
            item_id: item_id
        }).then (function (data) {

            if (typeof data.fishing !== "undefined") {
                game.fishing.results(data.fishing);
            }

            console.log("use item: ", data);
        }).finally(function() {
            game.removeInterference('interact');
        });
    },


    key_item_id: null,
    key_item_img: null,
    use_key_item: function () {
        if (this.key_item_id == null)
            return;

        this.use_item(this.key_item_id);
    },

    unset_key_item: function () {
        this.key_item_id = null;
        this.key_item_img = null;

        if (window.localStorage && localStorage.removeItem) {
            localStorage.removeItem('key_item_id');
            localStorage.removeItem('key_item_img');
        }
    },

    set_key_item: function (item_id, item_img, store) {
        if (typeof store === 'undefined') store = true;

        $('#action_key_item').attr('src', item_img);
        this.key_item_id = item_id;
        this.key_item_img = item_img;

        if (store && window.localStorage && localStorage.setItem) {
            localStorage.setItem('key_item_id', item_id);
            localStorage.setItem('key_item_img', item_img);
        }
    },

    load_key_item: function () {
        if (window.localStorage && localStorage.getItem && localStorage.getItem('key_item_id')) {
            this.set_key_item(localStorage.getItem('key_item_id'), localStorage.getItem('key_item_img'), false);      
        }
    },

    load_captcha_form: function() {
        console.log("test'")
        $('#captcha_form').submit(function (e) {
            game.network.request({
                action: 'captcha',
                captcha: $('#captcha').val()
            });
            return false;
        });
    },

    submit_locke_nickname_form: function () {
        $('#kingdom_locke_nickname_response').html('Loading')
            .attr('class', 'error' )
            .css('width', '200px' )
            .css('margin-bottom', '10px' )

        game.network.request({
            action: "locke_nickname",
            nickname: $('[name=nickname]').val(),
        }).then(function (data) {
            if (typeof data.locke_text !== "undefined") {
                $('#kingdom_locke_nickname_response').html(data.locke_text)
                    .attr('class', 'neutral' )
            }

            if (typeof data.nickname_success !== "undefined") {
                $('#kingdom_locke_nickname').css('display', 'none');
                game.removeInterference('locke_nickname');
            }
        });

        return false;
    }

};