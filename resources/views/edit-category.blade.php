@extends('layouts.app')

@section('content')
<div class="p-6 max-w-md mx-auto">
    <h2 class="text-xl font-bold mb-4">Edit Category</h2>

    @if($errors->any())
        <div class="bg-red-100 text-red-800 p-2 rounded mb-4">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('categories.update', $category) }}" method="POST" class="space-y-4">
        @csrf @method('PUT')
        <input type="text" name="name" value="{{ $category->name }}" class="w-full border border-blue-300 p-2 rounded" required>

        <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Update</button>
    </form>
</div>
@endsection
