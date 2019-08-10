/**
 * an static Entity
 */
game.StaticEntity = me.Sprite.extend(
{
    init: function (x, y, settings)
    {
        // call the parent constructor
        this._super(me.Sprite, 'init', [x, y , settings]);

        // add a physic body
        this.body = new me.Body(this);
        this.body.addShape(new me.Rect(0, 0, this.width, this.height));
        this.body.vel.set(0, 0);
        this.body.setFriction(0, 0);

        this.body.gravity = 0;
        this.body.jumping = false;
        this.body.falling = false;

        this.movement = new Movement(this);
        
        this.object_id = settings.object_id;
        this.z = settings.z;

        // disable collision because we're going to handle it on a tile level
        this.body.setCollisionMask(me.collision.types.NO_OBJECT);

        game.objects.push(this);
        
    },

    remove: function() {
        me.game.world.removeChild(this);
    },

    // manage the enemy movement
    update : function (dt)
    {
        // check & update movement
        this.body.update(dt);

        // handle collisions against other shapes
        me.collision.check(this);

        // return true if we moved or if the renderable was updated
        return (this._super(me.Sprite, 'update', [dt]) || this.body.vel.x !== 0 || this.body.vel.y !== 0);
    },

    /**
     * colision handler
     * (called when colliding with other objects)
     */
    onCollision : function (response, other) {
        // Make all other objects solid
        return false;
    }
});