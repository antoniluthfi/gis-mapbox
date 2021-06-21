<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mb-2">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    MapBox
                </div>
                <div class="card-body">
                    @if (session()->has('delete'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('delete') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <div wire:ignore id='map' style='width: 100%; height: 75vh;'></div>
                </div>
            </div>
        </div>
    
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    {{ $isEdit ? 'Edit Location' : 'Mark New Location' }}
                </div>
                <div class="card-body">
                    @if (session()->has('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <form wire:submit.prevent="{{ $isEdit ? 'editLocation(' . $locationId . ')' : 'addNewLocation' }}" id="form">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="title">Title</label>
                                    <input wire:model="title" type="text" class="form-control" id="title">
                                    @error('title')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
    
                            <div class="col-sm-6">
                                <label for="image">Image</label>
                                <div class="custom-file">
                                    <input type="file" wire:model="image" id="image" class="custom-file-input">
                                    <label class="custom-file-label" for="image">Choose file</label>
                                    @error('image')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        @if ($image)
                            <div class="row mt-3">
                                <div class="col-sm-12">
                                    <label>Image Preview</label>
                                    <div class="form-group">
                                        <img src="{{ $image->temporaryUrl() }}" alt="{{ $title }}" class="img-fluid">
                                    </div>
                                </div>
                            </div>   
                        @elseif ($imageUrl)
                            <div class="row mt-3">
                                <div class="col-sm-12">
                                    <label>Image Preview</label>
                                    <div class="form-group">
                                        <img src="{{ asset('storage/img/' . $imageUrl) }}" alt="{{ $title }}" class="img-fluid">
                                    </div>
                                </div>
                            </div>
                        @endif


                        <div class="row mt-3">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea wire:model="description" id="description" cols="10" rows="3" class="form-control"></textarea>    
                                    @error('description')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm">
                                    <button type="submit" class="btn btn-primary btn-block" @if (!$longitude || !$latitude || !$title || !$image || !$description) disabled @endif>{{ $isEdit ? 'Update' : 'Submit' }}</button>
                                </div>
                                @if ($isEdit)
                                    <div class="col-sm">
                                        <button wire:click="setAsDefaultLocation({{ $locationId }})" type="button" class="btn btn-success btn-block" @if (!$longitude || !$latitude || !$title || !$description) disabled @endif>Set as default location</button>
                                    </div>
                                @endif
                            </div>

                            @error('longitude' || 'latitude')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- modal --}}
    <div class="modal fade" id="delete" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title font-weight-bold" id="exampleModalLabel">Warning</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    This data will be deleted permanently, are you sure?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button wire:click="deleteLocation({{ $locationId }})" type="button" class="btn btn-danger" data-dismiss="modal">Delete</button>
                </div>
            </div>
        </div>
    </div>      
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:load', () => {
            // masjid nurul hikmah 112.78094002581173 -7.339708862937101
            const defaultLocation = [@this.defaultLocation.longitude, @this.defaultLocation.latitude];

            mapboxgl.accessToken = "{{ env('MAPBOX_ACCESS_TOKEN') }}";
            var map = new mapboxgl.Map({
                container: 'map',
                center: defaultLocation,
                zoom: 17.15,
                style: 'mapbox://styles/mapbox/streets-v11'
            });

            map.addControl(new mapboxgl.NavigationControl());
            map.on('click', (e) => {
                const longitude = e.lngLat.lng;
                const latitude = e.lngLat.lat;

                @this.longitude = longitude;
                @this.latitude = latitude;
            });

            // giving marker
            const loadLocations = (geoJson) => {
                geoJson.features.forEach((location) => {
                    const {geometry, properties} = location;
                    const {iconSize, locationId, title, image, description} = properties;

                    let markerElement = document.createElement('div');
                    markerElement.className = `marker${locationId}`;
                    markerElement.id = locationId;
                    markerElement.style.backgroundImage = 'url(https://docs.mapbox.com/help/demos/custom-markers-gl-js/mapbox-icon.png)';
                    markerElement.style.backgroundSize = 'cover';
                    markerElement.style.width = '40px';
                    markerElement.style.height = '40px';

                    const popupContent = `
                        <div style="overflow-y: auto; max-height: 400px; width: 100%;">
                            <table class="table table-sm mt-2">
                                <tbody>
                                    <tr>
                                        <td>Title</td>
                                        <td>${title}</td>
                                    </tr>
                                    <tr>
                                        <td>Picture</td>
                                        <td>
                                            <img src="{{ asset('storage/img/${image}') }}" alt="${title}" loading="lazy" class="img-fluid" width="50">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Description</td>
                                        <td>${description}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="row">
                                <a href="#form" class="btn btn-primary btn-sm btn-block">
                                    <i class="fas fa-pencil"></i>
                                </a>
                                <button wire:click="deleteLocation" type="button" class="btn btn-sm btn-danger btn-block"  data-toggle="modal" data-target="#delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;

                    markerElement.addEventListener('click', (e) => {
                        const locationId = e.target.id;
                        @this.findLocation(locationId);
                    });

                    const popUp = new mapboxgl.Popup({offset: 25}).setHTML(popupContent).setMaxWidth('400px');

                    new mapboxgl.Marker(markerElement).setLngLat(geometry.coordinates).setPopup(popUp).addTo(map);
                });
            }

            loadLocations({!! $geoJson !!});

            // reload data when there is an update
            window.addEventListener('getLocation', (e) => {
                loadLocations(JSON.parse(e.detail));
                $('.mapboxgl-popup').remove();
            });

            // reload when user delete location
            window.addEventListener('deleteLocation', (e) => {
                // loadLocations(JSON.parse(e.detail));
                $('.marker' + e.detail).remove();
                $('.mapboxgl-popup').remove();
            });
        });
    </script>
@endpush