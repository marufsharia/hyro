<footer class="bg-gray-900 text-white mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid md:grid-cols-4 gap-8">
            <!-- Brand -->
            <div class="col-span-2 md:col-span-1">
                <div class="flex items-center space-x-2 mb-4">
                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <span class="text-lg font-bold">{{ config('app.name') }}</span>
                </div>
                <p class="text-gray-400 text-sm">
                    Building amazing experiences with modern technology.
                </p>
            </div>

            <!-- Quick Links -->
            <div>
                <h3 class="font-semibold mb-4">Quick Links</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ url('/') }}" class="text-gray-400 hover:text-white transition-colors">Home</a></li>
                    <li><a href="{{ url('/about') }}" class="text-gray-400 hover:text-white transition-colors">About</a></li>
                    <li><a href="{{ url('/contact') }}" class="text-gray-400 hover:text-white transition-colors">Contact</a></li>
                </ul>
            </div>

            <!-- Resources -->
            <div>
                <h3 class="font-semibold mb-4">Resources</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Documentation</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Support</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Privacy Policy</a></li>
                </ul>
            </div>

            <!-- Social -->
            <div>
                <h3 class="font-semibold mb-4">Follow Us</h3>
                <div class="flex space-x-3">
                    <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-gray-700 rounded-lg flex items-center justify-center transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-gray-700 rounded-lg flex items-center justify-center transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                    </a>
                </div>
            </div>
        </div>

        <div class="border-t border-gray-800 mt-8 pt-8 text-center text-sm text-gray-400">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</footer>
