var nextId = -1;

module.exports = class Track {
    id = null;
    uri = null;
    title = null;
    artist = null;
    album = null;
    type = null;
    duration = null;
    owner = null;

    constructor(init){
        if (_.isObject(init)){
            _.assign(this, init);
        }

        this.id = this.id || nextId--;
        this.owner = this.owner || app.currentUser;
    }
};
