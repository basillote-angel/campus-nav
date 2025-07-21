@extends('layouts.app')

@section('content')
<div class="p-6 max-w-md mx-auto">
    <h2 class="text-xl font-bold mb-4">Add New Category</h2>

    @if($errors->any())
        <div class="bg-red-100 text-red-800 p-2 rounded mb-4">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('categories.store') }}" method="POST" class="space-y-4">
        @csrf
        <input type="text" name="name" class="w-full border border-blue-300 p-2 rounded" placeholder="Category name" required>

        <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Save</button>
    </form>
</div>
@endsection
