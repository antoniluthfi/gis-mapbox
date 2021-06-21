<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\Locations;
use App\Models\User;

class MapLocation extends Component
{
    use WithFileUploads;

    public $longitude, $latitude, $geoJson, $title, $image, $description, $locationId, $imageUrl;
    public $isEdit = false;
    public $defaultLocation = [];

    public function loadLocations()
    {
        $locations = Locations::orderBy('created_at', 'desc')->get();
        $customLocations = [];

        foreach ($locations as $location) {
            $customLocations[] = [
                'type' => 'Feature',
                'geometry' => [
                    'coordinates' => [$location->longitude, $location->latitude],
                    'type' => 'Point'
                ],
                'properties' => [
                    'message' => '',
                    'iconSize' => [50, 50],
                    'locationId' => $location->id,
                    'title' => $location->title,
                    'image' => $location->image,
                    'description' => $location->description
                ]
            ];
        }

        $geoLocation = [
            'type' => 'FeatureCollection',
            'features' => $customLocations
        ];

        $geoJson = collect($geoLocation)->toJson();
        $this->geoJson = $geoJson;
    }

    private function clearForm()
    {
        $this->longitude = '';
        $this->latitude = '';
        $this->title = '';
        $this->image = '';
        $this->description = '';
        $this->imageUrl = '';
        $this->locationId = '';
        $this->isEdit = false;
    }

    public function addNewLocation()
    {
        $this->validate([
            'longitude' => 'required',
            'latitude' => 'required',
            'title' => 'required',
            'image' => 'required|image|max:1024',
            'description' => 'required'
        ]);

        // store image
        $imageName = md5($this->image . microtime()) . '.' . $this->image->extension();
        Storage::putFileAs('public/img', $this->image, $imageName);
    
        // create data
        Locations::create([
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,
            'title' => $this->title,
            'image' => $imageName,
            'description' => $this->description
        ]);

        session()->flash('success', 'New location has been added');
        $this->loadLocations();
        $this->dispatchBrowserEvent('getLocation', $this->geoJson);
        $this->clearForm();
    }

    public function findLocation($id)
    {
        $location = Locations::findOrFail($id);

        $this->locationId = $location->id;
        $this->longitude = $location->longitude;
        $this->latitude = $location->latitude;
        $this->title = $location->title;
        $this->imageUrl = $location->image;
        $this->description = $location->description;
        $this->isEdit = true;
    }

    public function editLocation($id)
    {
        $this->validate([
            'longitude' => 'required',
            'latitude' => 'required',
            'title' => 'required',
            'description' => 'required'
        ]);

        $location = Locations::findOrFail($id);

        if($this->image) {
            if($location->image) {
                // remove old image
                unlink(public_path('storage/img/' . $location->image));
            }

            // store image
            $imageName = md5($this->image . microtime()) . '.' . $this->image->extension();
            Storage::putFileAs('public/img', $this->image, $imageName);

            $updateData = [
                'title' => $this->title,
                'image' => $imageName,
                'description' => $this->description
            ];
        } else {
            $updateData = [
                'title' => $this->title,
                'description' => $this->description
            ];
        }

        $location->update($updateData);

        session()->flash('success', 'Location updated succesfully');
        $this->clearForm();
        $this->loadLocations();
        $this->dispatchBrowserEvent('getLocation', $this->geoJson);
    }

    public function deleteLocation($id)
    {
        $location = Locations::findOrFail($id);
        $location->delete();
        
        // remove old image
        unlink(public_path('storage/img/' . $location->image));

        session()->flash('delete', 'Location deleted succesfully');
        $this->dispatchBrowserEvent('deleteLocation', $this->locationId);
        $this->loadLocations();
        $this->clearForm();
    }

    public function setAsDefaultLocation($id)
    {
        $location = Locations::findOrFail($id);
        $user = User::findOrFail(Auth::user()->id);

        $user->longitude = $location->longitude;
        $user->latitude = $location->latitude;
        $user->update();

        session()->flash('success', 'New default location has been set');
        $this->clearForm();
    }

    public function mount()
    {
        if(Auth::user()->longitude && Auth::user()->latitude) {
            $this->defaultLocation = [
                'longitude' => Auth::user()->longitude,
                'latitude' => Auth::user()->latitude
            ];
        } else {
            $this->defaultLocation = [
                'longitude' => 112.78094002581173,
                'latitude' => -7.339708862937101
            ];
        }

        $this->loadLocations();
    }

    public function render()
    {
        return view('livewire.map-location');
    }
}
