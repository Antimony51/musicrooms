<?php
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App{
/**
 * App\Profile
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $cosmetic_name
 * @property string $icon_large
 * @property string $icon_small
 * @property string $bio
 * @property integer $plays
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Query\Builder|\App\Profile whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Profile whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Profile whereCosmeticName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Profile whereIconLarge($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Profile whereIconSmall($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Profile whereBio($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Profile wherePlays($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Profile whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Profile whereUpdatedAt($value)
 */
	class Profile extends \Eloquent {}
}

namespace App{
/**
 * App\Room
 *
 * @property integer $id
 * @property integer $owner_id
 * @property string $name
 * @property string $visibility
 * @property string $title
 * @property string $description
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\User $owner
 * @method static \Illuminate\Database\Query\Builder|\App\Room whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Room whereOwnerId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Room whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Room whereVisibility($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Room whereTitle($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Room whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Room whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Room whereUpdatedAt($value)
 */
	class Room extends \Eloquent {}
}

namespace App{
/**
 * App\Track
 *
 * @property integer $id
 * @property integer $type
 * @property string $url
 * @property string $title
 * @property string $artist
 * @property string $album
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Track whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Track whereType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Track whereUrl($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Track whereTitle($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Track whereArtist($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Track whereAlbum($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Track whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Track whereUpdatedAt($value)
 */
	class Track extends \Eloquent {}
}

namespace App{
/**
 * App\User
 *
 * @property integer $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $remember_token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Room[] $rooms
 * @property-read \App\Profile $profile
 * @method static \Illuminate\Database\Query\Builder|\App\User whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereEmail($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User wherePassword($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereRememberToken($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

