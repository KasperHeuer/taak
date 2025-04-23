<x-layout>
    <x-header></x-header>

    <main class="max-w-md mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Upload een Nieuwe Afbeelding</h2>
                <p class="mt-1 text-sm text-gray-500">Selecteer een afbeeldingsbestand om te uploaden</p>
            </div>

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative my-4"
                    role="alert">
                    <strong class="font-bold">Succes!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative my-4" role="alert">
                    <strong class="font-bold">Fout!</strong>
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="px-6 py-5">
                <x-form action="/create" method="post" enctype="multipart/form-data" id="upload-form">
                    @csrf

                    <div id="image-container">
                        <x-input type="file" name="image[]" id="image-upload" accept="image/*" label='Kies je afbeelding'>
                        </x-input>
                    </div>

                    <div class="mt-4">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="add-more-toggle" class="sr-only peer">
                            <div
                                class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                            </div>
                            <span class="ml-3 text-sm font-medium text-gray-700">Meer afbeeldingen toevoegen</span>
                        </label>
                    </div>

                    <div id="additional-images-container" class="mt-4 hidden">
                        <button type="button" id="add-image-btn"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Afbeelding toevoegen
                        </button>

                        <div id="additional-images" class="mt-4 space-y-4">
                            <!-- Additional images will be added here -->
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <x-input type='submit' name='submit' value='Verzenden' label=''>
                        </x-input>
                    </div>
                </x-form>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const addMoreToggle = document.getElementById('add-more-toggle');
            const additionalImagesContainer = document.getElementById('additional-images-container');
            const addImageBtn = document.getElementById('add-image-btn');
            const additionalImages = document.getElementById('additional-images');
            let imageCounter = 1;

            // Toggle additional images section
            addMoreToggle.addEventListener('change', function () {
                if (this.checked) {
                    additionalImagesContainer.classList.remove('hidden');
                } else {
                    additionalImagesContainer.classList.add('hidden');
                    // Remove all additional images when toggle is turned off
                    additionalImages.innerHTML = '';
                    imageCounter = 1;
                }
            });

            // Add new image input field
            addImageBtn.addEventListener('click', function () {
                const newImageField = document.createElement('div');
                newImageField.className = 'flex items-center';
                newImageField.innerHTML = `
                    <div class="flex-grow">
                        <label class="block text-sm font-medium text-gray-700">Extra afbeelding ${imageCounter}</label>
                        <input type="file" name="image[]" accept="image/*" class="mt-1 block w-full text-sm text-gray-500
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-md file:border-0
                            file:text-sm file:font-semibold
                            file:bg-blue-50 file:text-blue-700
                            hover:file:bg-blue-100">
                    </div>
                    <button type="button" class="remove-image-btn ml-2 p-1 text-red-600 hover:text-red-900">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </button>
                `;
                additionalImages.appendChild(newImageField);
                imageCounter++;

                // Add event listener to remove button
                newImageField.querySelector('.remove-image-btn').addEventListener('click', function () {
                    additionalImages.removeChild(newImageField);
                });
            });
        });
    </script>
</x-layout>
