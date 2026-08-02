<x-user-dashboard-layout title="Profile" pretitle="Account">
    <div class="row row-cards">
        <div class="col-12 col-xl-8">
            <div class="card card-body">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-8">
            <div class="card card-body">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-8">
            <div class="card card-body">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-user-dashboard-layout>
