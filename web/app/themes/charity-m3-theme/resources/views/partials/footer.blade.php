<footer class="content-info bg-surface-container text-on-surface-container p-8 md:p-12 mt-auto">
  <div class="container mx-auto">
    <div class="footer-widgets grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
      @if (is_active_sidebar('footer-1'))
        <div class="footer-widget-col">
          @php(dynamic_sidebar('footer-1'))
        </div>
      @endif
      @if (is_active_sidebar('footer-2'))
        <div class="footer-widget-col">
          @php(dynamic_sidebar('footer-2'))
        </div>
      @endif
      @if (is_active_sidebar('footer-3'))
        <div class="footer-widget-col">
          @php(dynamic_sidebar('footer-3'))
        </div>
      @endif
    </div>

    <div class="footer-copyright border-t border-outline pt-8 text-center text-on-surface-variant">
      @if (is_active_sidebar('footer-copyright'))
          @php(dynamic_sidebar('footer-copyright'))
      @else
        <p>&copy; {{ date('Y') }} {{ $siteName ?? get_bloginfo('name') }}. All Rights Reserved.</p>
      @endif
    </div>
  </div>
</footer>
