<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mb-2">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    Locations List
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-sm table-bordered table-hover table-striped">
                        <thead>
                            <tr class="text-center">
                                <th>No</th>    
                                <th>Longitude</th>
                                <th>Latitude</th>
                                <th>Location Name</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>     
                        </thead>
                        <tbody>
                            @forelse ($locations as $index => $location)
                                <tr class="text-center">
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $location->longitude }}</td>
                                    <td>{{ $location->latitude }}</td>
                                    <td>{{ $location->title }}</td>
                                    <td class="text-left">{{ $location->description }}</td>
                                    <td>
                                        <a wire:click="findLocation({{ $location->id }})" href="#form" class="btn btn-sm btn-primary">
                                            <i class="fas fa-pencil"></i>
                                        </a>
                                        <button wire:click="findLocation({{ $location->id }})" type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr class="text-center">
                                    <td colspan="6">No Locations Found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-2">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    Location Detail
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

                    <form wire:submit.prevent="editLocation({{$locationId}})" id="form">
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
                                <div class="col-sm-6">
                                    <button type="submit" class="btn btn-primary btn-block mb-2" @if (!$longitude || !$latitude || !$title || !$description) disabled @endif>Update</button>
                                </div>
                                <div class="col-sm-6">
                                    <button wire:click="setAsDefaultLocation({{ $locationId }})" type="button" class="btn btn-success btn-block mb-2" @if (!$longitude || !$latitude || !$title || !$description) disabled @endif>Set as default location</button>
                                </div>
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