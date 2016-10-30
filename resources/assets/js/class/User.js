module.exports = class User {
    name = null,
    displayName = null,
    iconSmall = null,
    iconLarge = null,
    friendStatus = null

    constructor(init){
        if (_.isObject(init)){
            _.assign(this, init);
        }
    }
}
