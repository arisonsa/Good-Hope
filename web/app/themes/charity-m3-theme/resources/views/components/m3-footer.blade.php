<footer class="site-footer bg-gray-800 text-white py-12">
    <div class="container mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="footer-col-1">
                @if (is_active_sidebar('footer-col-1'))
                    @php dynamic_sidebar('footer-col-1') @endphp
                @endif
            </div>
            <div class="footer-col-2">
                @if (is_active_sidebar('footer-col-2'))
                    @php dynamic_sidebar('footer-col-2') @endphp
                @endif
            </div>
            <div class="footer-col-3">
                @if (is_active_sidebar('footer-col-3'))
                    @php dynamic_sidebar('footer-col-3') @endphp
                @endif
            </div>
            <div class="footer-col-4">
                @if (is_active_sidebar('footer-col-4'))
                    @php dynamic_sidebar('footer-col-4') @endphp
                @endif
            </div>
        </div>
        <div class="mt-8 border-t border-gray-700 pt-8 flex flex-col md:flex-row justify-between items-center">
            <p class="text-sm">&copy; {{ date('Y') }} {{ get_bloginfo('name') }}. All Rights Reserved.</p>
            @if (has_nav_menu('footer_navigation'))
                <nav class="footer-navigation mt-4 md:mt-0">
                    {!! wp_nav_menu(['theme_location' => 'footer_navigation', 'menu_class' => 'flex space-x-4', 'echo' => false]) !!}
                </nav>
            @endif
        </div>
    </div>
</footer>
