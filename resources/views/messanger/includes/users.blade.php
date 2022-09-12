@forelse ($users as $user)
    <!-- Card -->
    <div class="card border-0 mt-5">
        <div class="card-body">

            <div class="row align-items-center gx-5">
                <div class="col-auto">
                    <div class="avatar ">
                        <img class="avatar-img" src="{{ $user->image }}" alt="">
                    </div>
                </div>
                <div class="col">
                    <h5>{{ $user->name }}</h5>
                </div>
                <div class="col-auto">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="user_id" value="{{ $user->id }}" id="id-member-{{ $user->id }}">
                        <label class="form-check-label" for="id-member-{{ $user->id }}"></label>
                    </div>
                </div>
            </div>
            <label class="stretched-label" for="id-member-{{ $user->id }}"></label>
        </div>
    </div>
    <!-- Card -->
@empty
<h3 class="text-center">No Users</h3>
@endforelse
