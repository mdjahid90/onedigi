<x-admin-layout>
    <x-slot name="header">
        <div>
            <div class="page-pretitle">Marketing</div>
            <h2 class="page-title">Sitemap</h2>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <div>
                            <div class="subheader">Automatic sitemap</div>
                            <h3 class="card-title mb-2">Live XML sitemap is ready</h3>
                            <p class="text-secondary mb-0">
                                It is generated from active products, active categories, published pages, and important public routes whenever Google or a visitor opens the sitemap URL.
                            </p>
                        </div>
                        <span class="badge bg-green-lt">{{ $entryCount }} URLs</span>
                    </div>

                    <div class="mt-4">
                        <label class="form-label">Sitemap URL</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ $sitemapUrl }}" readonly>
                            <a href="{{ $sitemapUrl }}" target="_blank" rel="noreferrer" class="btn btn-primary">Open</a>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Robots URL</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ $robotsUrl }}" readonly>
                            <a href="{{ $robotsUrl }}" target="_blank" rel="noreferrer" class="btn btn-outline-primary">Open</a>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.marketing.sitemap.generate') }}" class="mt-4">
                        @csrf
                        <button class="btn btn-outline-secondary" type="submit">Check automatic sitemap</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Shared hosting setup</h3>
                </div>
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <div class="fw-semibold">No public file write required</div>
                        <div class="text-secondary small">Hostinger/shared hosting permission issues will not block sitemap updates.</div>
                    </div>
                    <div class="list-group-item">
                        <div class="fw-semibold">No cron or VPS required</div>
                        <div class="text-secondary small">The sitemap is generated automatically when <span class="font-monospace">/sitemap.xml</span> is requested.</div>
                    </div>
                    <div class="list-group-item">
                        <div class="fw-semibold">Correct live domain</div>
                        <div class="text-secondary small">URLs are built from the current request domain, so localhost URLs will not be used on live hosting.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Preview URLs</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>URL</th>
                                <th class="w-1">Change</th>
                                <th class="w-1">Priority</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($previewEntries as $entry)
                                <tr>
                                    <td class="text-truncate" style="max-width: 560px;">
                                        <a href="{{ $entry['loc'] }}" target="_blank" rel="noreferrer">{{ $entry['loc'] }}</a>
                                    </td>
                                    <td><span class="badge bg-blue-lt">{{ $entry['changefreq'] }}</span></td>
                                    <td>{{ $entry['priority'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
