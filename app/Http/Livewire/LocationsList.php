<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\Locations;
use App\Models\User;

class LocationsList extends Component
{
    use WithFileUploads;
    use WithPagination;

    public $longitude, $latitude, $title, $image, $description, $locationId, $imageUrl, $search;
    protected $paginationTheme = 'bootstrap';

    private function clearForm()
    {
        $this->longitude = '';
        $this->latitude = '';
        $this->title = '';
        $this->image = '';
        $this->description = '';
        $this->imageUrl = '';
        $this->locationId = '';
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
    }

    public function deleteLocation($id)
    {
        $location = Locations::findOrFail($id);
        $location->delete();
        
        // remove old image
        unlink(public_path('storage/img/' . $location->image));

        session()->flash('success', 'Location deleted succesfully');
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

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        if($this->search) {
            $locations = Locations::where('title', 'like', '%' . $this->search . '%')->paginate(15);
        } else {
            $locations = Locations::paginate(15);
        }

        return view('livewire.locations-list', [
            'locations' => $locations
        ]);
    }
}
