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
        $settingsButton = true;
        return view('user.list', compact('users', 'settingsButton'));
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
        if ($user->is($request->user()) || $request->user()->admin) {
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
        if ($user->is($request->user()) || $request->user()->admin) {
            $data = collect($request->all())->map(function($item, $key){
                if ($key == 'cosmetic-name' ||
                    $key == 'bio')
                {
                    return trim($item);
                }else{
                    return $item;
                }
            })->toArray();

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

                $uri = hash_file('sha1', $icon->path());
                $im = new Imagick($icon->path());
                $im->setImageFormat('png');
                $geo = $im->getImageGeometry();
                if(($geo['width']/200) < ($geo['height']/200))
                {
                    $im->cropImage($geo['width'], floor(200*$geo['width']/200),
                        0, (($geo['height']-(200*$geo['width']/200))/2));
                }
                else
                {
                    $im->cropImage(ceil(200*$geo['height']/200), $geo['height'],
                        (($geo['width']-(200*$geo['height']/200))/2), 0);
                }
                $im->thumbnailImage(200, 200, true);
                $largePath = 'uploads/img/avatar/' . $uri . '_200.png';
                Storage::cloud()->delete($profile->getOriginal('icon_large'));
                Storage::cloud()->put($largePath, $im->getImageBlob(), 'public');
                $im->thumbnailImage(48, 48, true);
                $smallPath = 'uploads/img/avatar/' . $uri . '_48.png';
                Storage::cloud()->delete($profile->getOriginal('icon_small'));
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

    public function showUserSettings(User $user, Request $request){
        if ($user->is($request->user()) || $request->user()->admin){
            return view('user.settings', compact('user'));
        }else{
            abort(403, "You don't have permission to edit this user's settings.");
        }
    }

    public function updateUser(User $user, Request $request){
        if ($user->is($request->user()) || $request->user()->admin){
            $data = $request->all();

            $rules = [
                'name' => 'username_chars|max:24|unique:users,name',
                'email' => 'email|max:255|unique:users',
                'password' => 'min:6|confirmed',
            ];

            if (config('auth.passwords.users.use_security_questions')){
                $numSecurityQuestions = config('auth.passwords.users.num_security_questions');
                $rules = array_merge($rules, [
                    'questions' => "required|size:$numSecurityQuestions",
                    'questions.*' => 'required|max:255',
                    'answers.*' => 'max:255'
                ]);
            }

            if (isset($data['name']) && $data['name'] == $user->name){
                unset($rules['name']);
                unset($data['name']);
            }

            if (isset($data['email']) && $data['email'] == $user->email){
                unset($rules['email']);
                unset($data['email']);
            }

            if (isset($data['password']) && $data['password'] == $user->password){
                unset($rules['password']);
                unset($data['password']);
            }

            $validator = Validator::make($data, $rules);

            $rules = [
                'name' => 'username_chars|max:24|unique:users,name',
                'email' => 'email|max:255|unique:users',
                'password' => 'min:6|max:255|confirmed',
            ];

            if (config('auth.passwords.users.use_security_questions')){
                $numSecurityQuestions = config('auth.passwords.users.num_security_questions');
                $rules = array_merge($rules, [
                    'questions' => "required|size:$numSecurityQuestions",
                    'questions.*' => 'required|max:255',
                    'answers.*' => 'max:255'
                ]);
            }

            $validator->setAttributeNames([
                'name' => 'username',
                'email' => 'email',
                'password' => 'password',
                'questions' => 'security questions',
                'questions.*' => 'security question',
                'answers.*' => 'security answer',
            ]);

            if ($validator->fails()) {
                $this->throwValidationException(
                    $request, $validator
                );
            }

            if (!empty($data['name'])) $user->name = $data['name'];
            if (!empty($data['email'])) $user->email = $data['email'];
            if (!empty($data['password']))  $user->password = bcrypt($data['password']);
            if (config('auth.passwords.users.use_security_questions')){
                $user->questions = $data['questions'];
                $user->answers = array_map(function($answer, $oldAnswer){
                    $normalized = strtolower(preg_replace('/\s+/', ' ', trim($answer)));
                    if (!empty($normalized)){
                        return bcrypt($normalized);
                    }else{
                        return $oldAnswer;
                    }
                }, $data['answers'], $user->answers ?: []);
            }

            if ($request->user()->admin){
                $user->admin = isset($data['admin']) ? true : false;
            }

            $user->save();

            return redirect(route('user', ['user' => $user]));
        }else{
            abort(403, "You don't have permission to edit this user's settings.");
        }
    }

    public function delete(User $user, Request $request){
        if ($user->is($request->user()) || $request->user()->admin){
            if ($user->is($request->user())){
                Auth::logout();
            }
            $user->delete();
            return redirect(url('/'));
        }else{
            abort(403, "You don't have permission to delete this user.");
        }
    }
}
