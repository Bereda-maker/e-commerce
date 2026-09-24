<x-layout>
    <h1 class="text-2xl font-semibold mb-6">Profile</h1>
    <p class="text-gray-600">Signed in as {{ auth()->user()->name }} ({{ auth()->user()->email }}).</p>
</x-layout>
