@extends('layouts.app')

@section('title', 'Admin Control - MehfilCards')

@section('content')
<section class="admin-dashboard">
    <div class="admin-hero">
        <div>
            <span class="eyebrow">Admin Control</span>
            <h1>Manage categories and custom card designs</h1>
            <p>Logged in as {{ auth()->user()->name }}. Add any festival, upload your own artwork, choose colors, and publish templates for the card creator.</p>
        </div>
        <div class="admin-stats">
            <span><strong>{{ $categories->count() }}</strong> Categories</span>
            <span><strong>{{ $templates->count() }}</strong> Templates</span>
            <span><strong>Live</strong> Creator</span>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success compact-alert">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger compact-alert">{{ $errors->first() }}</div>
    @endif

    <div class="admin-grid">
        <div class="admin-panel control-panel">
            <div class="section-title">
                <span>Categories</span>
                <i class="fa-solid fa-tags"></i>
            </div>
            <form method="POST" action="{{ route('admin.categories.store') }}" class="inline-form">
                @csrf
                <input class="form-control" name="name" placeholder="Sangeet, Naming Ceremony, Office Party" required>
                <button class="btn btn-primary" type="submit"><i class="fa-solid fa-plus"></i><span>Add</span></button>
            </form>
            <div class="chips category-chips">
                @foreach ($categories as $category)
                    <span>{{ $category->name }}</span>
                @endforeach
            </div>
        </div>

        <div class="admin-panel control-panel">
            <div class="section-title">
                <span>Custom Design Upload</span>
                <i class="fa-solid fa-palette"></i>
            </div>
            <form method="POST" action="{{ route('admin.templates.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="field-grid two">
                    <label>
                        <span>Category</span>
                        <select class="form-select" name="category_id">
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span>Template Name</span>
                        <input class="form-control" name="name" placeholder="Royal Walima Gold" required>
                    </label>
                </div>
                <div class="field-grid two">
                    <label>
                        <span>Upload Artwork</span>
                        <input class="form-control" type="file" name="template_image" accept="image/*">
                    </label>
                    <label>
                        <span>Design Motif</span>
                        <select class="form-select" name="motif">
                            <option value="arch">Arch</option>
                            <option value="crescent">Crescent</option>
                            <option value="lantern">Lantern</option>
                            <option value="floral">Floral</option>
                            <option value="confetti">Confetti</option>
                            <option value="diya">Diya</option>
                        </select>
                    </label>
                </div>
                <div class="field-grid three swatch-grid">
                    <label><span>Background 1</span><input class="form-control form-control-color" type="color" name="color_one" value="#0f3d3e"></label>
                    <label><span>Background 2</span><input class="form-control form-control-color" type="color" name="color_two" value="#8b2f55"></label>
                    <label><span>Accent</span><input class="form-control form-control-color" type="color" name="accent" value="#d9a441"></label>
                </div>
                <button class="btn btn-primary" type="submit"><i class="fa-solid fa-upload"></i><span>Save Custom Template</span></button>
            </form>
        </div>
    </div>

    <div class="admin-panel">
        <div class="section-title">
            <span>Live Template Library</span>
            <a class="btn btn-outline-dark btn-sm" href="{{ route('home') }}#maker"><i class="fa-solid fa-eye"></i><span>View Creator</span></a>
        </div>
        <div class="template-table template-library">
            @foreach ($templates as $template)
                <article>
                    <img src="{{ $template->image_url ?: route('card.art', $template->slug) }}" alt="{{ $template->name }}">
                    <div>
                        <strong>{{ $template->name }}</strong>
                        <span>{{ $template->category->name }} / {{ $template->motif }}</span>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endsection
