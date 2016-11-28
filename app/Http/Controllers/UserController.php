<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Auth;
use Cache;
use Validator;
use App\Room;
use Imagick;
use Storage;

class UserController extends Controller
{

    public function __construct()
    {

    }

    public function showUserList()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(10);
        return view('user.list', compact('users'));
    }

    public function showOverview(User $user, Request $request){
        $profile = $user->profile;
        $activeTab = 'overview';
        $ownProfile = $user->is($request->user());
        return view('user.overview', compact('user', 'profile', 'activeTab', 'ownProfile'));
    }

    public function showFriends(User $user, Request $request){
        $profile = $user->profile;
        $friends = $user->getFriends();
        $activeTab = 'friends';
        $ownProfile = $user->is($request->user());
        $pending = collect();
        if ($ownProfile){
            $pending = $user->getPendingFriendships()->map(function($friendship) use($user){
                if ($friendship->sender->is($user)){
                    return $friendship->recipient;
                }else{
                    Cache::get('asdf');
                    return $friendship->sender;
                }
            });
        }
        return view('user.friends', compact('user', 'profile', 'friends', 'pending', 'activeTab', 'ownProfile'));
    }

    public function showFavorites(User $user, Request $request){
        $profile = $user->profile;
        $favorites = $user->favoriteTracks()->orderBy('favorites.created_at', 'desc')->get();
        $mutualFavorites = null;
        $activeTab = 'favorites';
        $ownProfile = $user->is($request->user());
        if ($ownProfile){
            $mutualFavorites = $favorites;
        }elseif (Auth::check()){
            $mutualFavorites = $favorites->intersect($request->user()->favoriteTracks);
        }
        return view('user.favorites', compact('user', 'profile', 'favorites', 'mutualFavorites', 'activeTab', 'ownProfile'));
    }

    public function showRooms(User $user, Request $request){
        $profile = $user->profile;
        $rooms = $user->rooms()->whereVisibility('public')
            ->orderBy('user_count', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        $activeTab = 'rooms';
        $ownProfile = $user->is($request->user());
        return view('user.rooms', compact('user', 'profile', 'ownProfile', 'activeTab', 'rooms'));
    }

    public function showEditProfile(User $user, Request $request){
        if ($user->is($request->user())) {
            $profile = $user->profile;
            return view('user.edit', compact('user', 'profile'));
        }else{
            abort(403, "This profile isn't yours.");
        }
    }

    public function addFavorite($id, Request $request){
        $user = $request->user();
        if (is_null($user->favoriteTracks()->whereTrackId($id)->first())){
            $user->favoriteTracks()->attach($id);
        }else{
            return response("This track is already in your favorites.", 400);
        }
    }

    public function removeFavorite($id, Request $request){
        $user = $request->user();
        $user->favoriteTracks()->detach($id);
    }

    public function searchFavorites(User $user, Request $request){
        $queryString = '%' . $request->input('query', '') . '%';
        $perPage = $request->input('perPage', 10);
        return ($user->favoriteTracks()->where(function ($query) use ($queryString){
                $query->where('title', 'like', $queryString)
                    ->orWhere('artist', 'like', $queryString);
            })->orderBy('favorites.created_at', 'desc')
                ->paginate($perPage));
    }

    public function addSavedRoom(Room $room, Request $request){
        $user = $request->user();
        if (is_null($user->savedRooms()->whereRoomId($room->id)->first())){
            $user->savedRooms()->attach($room);
        }else{
            return response("This room is already in your saved rooms.", 400);
        }
    }

    public function removeSavedRoom(Room $room, Request $request){
        $user = $request->user();
        $user->savedRooms()->detach($room);
    }

    public function updateProfile(User $user, Request $request)
    {
        if ($user->is($request->user())) {
            $data = collect($request->all())->map(function($item, $key){
                if ($key == 'cosmetic-name' ||
                    $key == 'bio')
                {
                    return trim($item);
                }else{
                    return $item;
                }
            })->toArray();
            $validator = $this->validator($data);

            if ($validator->fails()) {
                $this->throwValidationException(
                    $request, $validator
                );
            }

            $profile = $user->profile;

            if ($request->hasFile('icon')){
                $icon = $request->file('icon');
                if (!$icon->isValid()){
                    return reponse("Error while uploading", 400);
                }

                $im = new Imagick($icon->getPathname());
                $im->setImageFormat('png');
                $im->thumbnailImage(200, 200, true, true);
                $largePath = 'uploads/img/' . $user->name . '_large.png';
                Storage::cloud()->put($largePath, $im->getImageBlob(), 'public');
                $im->thumbnailImage(48, 48, true, true);
                $smallPath = 'uploads/img/' . $user->name . '_small.png';
                Storage::cloud()->put($smallPath, $im->getImageBlob(), 'public');

                $profile->icon_large = $largePath;
                $profile->icon_small = $smallPath;
            }

            $profile->bio = $data['bio'];
            $profile->cosmetic_name = $data['cosmetic-name'];
            $profile->save();

            return redirect(route('user', ['user' => $user]));
        }else{
            abort(403, "This profile isn't yours.");
        }
    }

    protected function validator(array $data)
    {
        $validator = Validator::make($data, [
            'cosmetic-name' => 'max:24',
            'icon' => 'file|image|max:100000',
            'bio' => 'max:1000'
        ]);
        $validator->setAttributeNames([
            'cosmetic-name' => 'cosmetic name',
            'icon' => 'profile picture',
            'bio' => 'about me'
        ]);
        return $validator;
    }

    public function addFriend(User $user, Request $request){
        if(!$user->is($request->user())){
            $request->user()->befriend($user);
            return $user;
        }else{
            return response("You can't befriend yourself.", 403);
        }
    }

    public function removeFriend(User $user, Request $request){
        if(!$user->is($request->user())){
            $request->user()->unfriend($user);
            return $user;
        }else{
            return response("You can't unfriend yourself.", 403);
        }
    }

    public function acceptFriend(User $user, Request $request){
        if(!$user->is($request->user())){
            $request->user()->acceptFriendRequest($user);
            return $user;
        }else{
            return response("You can't accept yourself.", 403);
        }
    }

    public function declineFriend(User $user, Request $request){
        if(!$user->is($request->user())){
            $request->user()->denyFriendRequest($user);
            return $user;
        }else{
            return response("You can't deny yourself.", 403);
        }
    }
}
