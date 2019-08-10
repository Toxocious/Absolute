
game.ProgressBar = me.Renderable.extend({
	init: function (x, y, w, h) {
		this._super(me.Renderable, "init", [x, y, w, h]);
		this.invalidate = false;
        this.barHeight = 15;
        this.barPadding = 15;
		this.progress = 0;
    },
    
	onProgressUpdate : function (progress) {
		this.progress = ~~(progress * (this.width - 2 * this.barPadding));
		this.invalidate = true;
    },
    
	update : function () {
		if (this.invalidate === true) {
			this.invalidate = false;
			return true;
        }
        
		return false;
	},

	draw : function (renderer) {
        black = new me.Color().parseCSS("#fff");
        light = new me.Color().parseCSS("#4A618F");

		renderer.setColor(black);
		renderer.fillRect(
            this.barPadding,
			(this.height / 2) - (this.barHeight / 2),
			this.width - (2 * this.barPadding),
			this.barHeight
        );
            
        renderer.setColor(light);
        renderer.fillRect(
            this.barPadding,
            (this.height / 2) - (this.barHeight / 2),
            this.progress,
            this.barHeight
        );
    }
});

game.LoadingScreen = me.ScreenObject.extend({
	onResetEvent : function () {
        black = new me.Color().parseCSS("#161d2a");

        me.game.world.addChild( new me.ColorLayer("background", black, 0), 0 );
        
        /*
        https://groups.google.com/forum/#!topic/melonjs/bF56tLCc_Aw

        I would recommend "manually" loading the image then, see here (using me.loader.load) :
        http://melonjs.github.io/docs/me.loader.html#load

        the load function takes a callback (see the example) and once the callback triggered then you could call the me.state.change to go to your loading screen and display the image.
        */

		var w = me.video.renderer.getWidth();
        var h = me.video.renderer.getHeight();
        
        var progressBar = new game.ProgressBar( 0, 0, w, h );
        progressBar.anchorPoint.set(0,0);
        me.game.world.addChild(progressBar, 1);

		this.loaderHdlr = me.event.subscribe(
			me.event.LOADER_PROGRESS,
			progressBar.onProgressUpdate.bind(progressBar)
		);

		this.resizeHdlr = me.event.subscribe(
			me.event.VIEWPORT_ONRESIZE,
			progressBar.resize.bind(progressBar)
		);
	},

	// destroy object at end of loading
	onDestroyEvent : function () {
		// cancel the callback
		me.event.unsubscribe(this.loaderHdlr);
        me.event.unsubscribe(this.resizeHdlr);
        
		this.loaderHdlr = this.resizeHdlr = null;
	}
});
